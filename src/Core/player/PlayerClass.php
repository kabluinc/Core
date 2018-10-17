<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 4/27/2017
 * Time: 4:39 PM
 */

namespace Core\player;

use Core\Main;
use Core\managers\GameManager;
use Core\tasks\IronSoupTask;
use Core\tasks\KohiTask;
use Core\utils\Prefix;
use Core\utils\StatsManager;
use Core\utils\TextToHead;
use Core\utils\Utils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\Player;
use pocketmine\Server;

class PlayerClass extends Player
{


    public $loginTrys = [];

    public $kohiMatch = [];

    private $queued = false;

    public $inGame;

    public $gameType;

    private $statsManager;

    private $match;

    private $plugin;

    private $session;

    public $matchType;

    private $loaderID;

    private $ids = [];


    /**
     * PlayerClass constructor.
     * @param Server $server
     * @param NetworkSession $session
     */
    public function __construct(Server $server, NetworkSession $session){
        parent::__construct($server, $session);
        $this->statsManager = new StatsManager($this);
        $this->session = $session;
        $plugin = Main::getInstance();
        $this->plugin = $plugin;

        /*$id = mt_rand(1, PHP_INT_MAX);
        if(isset($this->ids[$this->getName()])){
            $this->loaderID = -1;
        }else{
            $this->loaderID = $id;
        }

        $this->ids[$this->getName()] = $id;
        */
    }

    /**
     * @return Main
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return NetworkSession
     */
    public function getNetworkSession(): NetworkSession{
        return $this->session;
    }

    /* TODO: implement permissions handling.
    public function hasPermission($permission): bool{
        $database = $this->getPlugin()->database->getAll();
        if($this->getPlugin()->database->exists($this->getName())){
            if(isset($database[$this->getName()]["permissions"][$permission])){
                $perm = $database[$this->getName()]["permissions"][$permission];
                if($perm === true){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return true;
        }
    }

    public function addPermission($permission){
        $database = $this->getPlugin()->database->getAll();
        $database[strtolower($this->getName())]["permissions"][$permission] = true;
        $this->getPlugin()->database->setAll($database);
        $this->getPlugin()->database->save();
        $this->getPlugin()->database->reload();
    }

    public function removePermission($permission){
        $database = $this->getPlugin()->database->getAll();
        $database[strtolower($this->getName())]["permissions"][$permission] = false;
        $this->getPlugin()->database->setAll($database);
        $this->getPlugin()->database->save();
        $this->getPlugin()->database->reload();
    }
*/

    /**
     * @return \pocketmine\Server
     */
    public function getServer(){
        return parent::getServer();
    }


    /**
     * @return string
     */
    public function getName() : string{
        return strtolower(parent::getName());
    }

    /** Returns player name in regular form
     * @return string
     */
    public function getRealName(){
        return parent::getName();
    }


    /**
     * @return mixed
     */
    public function getPassword(){
        $database = $this->getPlugin()->database->getAll();
        if($this->isRegistered()){
            return $database[strtolower($this->getName())]["password"];
        }else{
            return -1;
        }
    }

    /**
     * @return bool
     */
    public function isRegistered(){
        if($this->getPlugin()->database->exists(strtolower($this->getName()))){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isLoggedIn(){
        if(isset($this->getPlugin()->loggedIn[$this->getName()])){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @return mixed|void
     */
    public function login(){
        $this->getPlugin()->loggedIn[$this->getName()] = true;
        TextToHead::sendText($this);
        $this->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::LOGGED_IN));
    }


    /**
     * @return mixed|void
     */
    public function logout(){
        unset($this->getPlugin()->loggedIn[$this->getName()]);
    }

    /**
     * @return GameManager|null
     * returns what match player is in
     */
    public function getMatch(){
        if($this->match instanceof GameManager){
            return $this->match;
        }else{
            return null;
        }
    }

    /**
     * @return mixed|void
     */
    public function removeFromMatch(){
        if($this->isQueued()){
            $this->getMatch()->removePlayer($this);
        }
        $this->match = null;
        $this->getPlugin()->playersInMatches--;
    }

    /**
     * @return bool
     */
    public function isQueued(){ // will be used for checking is player is in a match(any gamemode)
        return $this->queued;
    }


    /**
     * @param bool $bool
     * @param bool $inGame
     * @param $gameType
     */
    public function setQueued(bool $bool, bool $inGame, $gameType){
        $this->queued = $bool;
        if($bool === false){
            $this->inGame = false;
            $this->gameType = null;
        }else{
            $this->inGame = $inGame;
            $this->gameType = $gameType;
        }
    }

    /**
     * @return mixed
     */
    public function getLang(){
        $database = $this->getPlugin()->database->getAll();
        return $database[$this->getName()]["lang"];
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang){
        $database = $this->getPlugin()->database->getAll();
        $database[$this->getName()]["lang"] = $lang;
        $this->getPlugin()->database->setAll($database);
        $this->getPlugin()->database->save();
        $this->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Set language to ".$lang);
    }

    /**
     * @param GameManager $match
     */
    public function setInMatch(GameManager $match){
        $this->match = $match;
    }

    /**
     * @return StatsManager
     */
    public function getStatsManager() : StatsManager{
        return $this->statsManager;
    }

    /**
     * @return string
     */
    public function getStats(){
        return $this->getStatsManager()->returnStats();
    }


    /*public function getKohiMatch(){
        return isset($this->kohiMatch["match"]) ? $this->kohiMatch["match"] : null;
    }*/


    /**
     * @param GameManager $match
     * @return bool
     */
    public function joinMatch(GameManager $match){
        if($match->getStatus() === GameManager::PVP){
            return false;
        }

        if(count($match->getPlayers()) === 2){
            return false;
        }

        if(!$match->isJoinable()){
            $this->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Match #".$match->getName()." for ".$match->getGameType()." isn't joinable please try again later!");
            return false;
        }

        if(count($match->getPlayers()) === 0){
            if($match->getGameType() === "Kohi1v1"){
                $task = new KohiTask($this->getPlugin(), $match);
                $h = $this->getPlugin()->getScheduler()->scheduleRepeatingTask($task, 20);
                $task->setHandler($h);
                Main::$tasks[$match->getName()] = $task->getTaskId();
            }

            if($match->getGameType() === "IronSoup1v1"){
                $task = new IronSoupTask($this->getPlugin(), $match);
                $h = $this->getPlugin()->getScheduler()->scheduleRepeatingTask($task, 20);
                $task->setHandler($h);
                Main::$tasks[$match->getName()] = $task->getTaskId();
            }
            //TODO: BUHC task!
        }

        $match->addPlayer($this);

        $cfg = $this->getPlugin()->matchesConfig->getAll();
        $pos1 = $cfg[$match->getGameType()."-matches"][$match->getName()]["Positions"]["pos1"];
        $pos2 = $cfg[$match->getGameType()."-matches"][$match->getName()]["Positions"]["pos2"];
        $level = $cfg[$match->getGameType()."-matches"][$match->getName()]["Positions"]["pos2"]["level"];

        if(!$this->getServer()->isLevelLoaded($level)){
            $this->getServer()->loadLevel($level);
        }

        if(count($match->getPlayers()) === 1){
            $this->teleport(new Position($pos1["x"], $pos1["y"]+1.5, $pos1["z"], $this->getServer()->getLevelByName($pos1["level"])));
        }else{
            $this->teleport(new Position($pos2["x"], $pos2["y"]+1.5, $pos2["z"], $this->getServer()->getLevelByName($pos2["level"])));
        }
        $this->getInventory()->clearAll();
        $this->getPlugin()->playersInMatches++;

        return true;
    }
}

