<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 4/26/2017
 * Time: 7:18 PM
 */
namespace Core;

use Core\commands\CoreCommand;
use Core\events\EventsListener;
use Core\managers\BUHCManager;
use Core\managers\GameManager;
use Core\managers\IronSoupManager;
use Core\managers\KohiManager;
use Core\npc\HumanNPC;
use Core\player\PlayerClass;
use Core\tasks\FloatingTextTask;
use Core\tasks\ParticlesTask;
use Core\tasks\QueueTask;
use Core\tasks\ServerRestartTask;
use Core\tasks\MySQLProviderTask;
use Core\utils\Permissions;
use Core\utils\Prefix;
use Core\utils\Utils;
use Core\utils\MySQLProvider;
use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use PocketMine\plugin\Plugin;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{

    //TODO: check for map backup is in backup folder and implement joinable function


    private static $obj = null;

    public static $langs = [];
    public static $tasks = [];
    public static $badwords = ["cunt", "whore", "bitch", "nigger", "fuck", 'shit', 'ass', 'slut', 'faggot', 'fag', 'motherfucker', 'dick', 'pussy', 'vagina', 'penis'];

    public $settings, $database, $matchesConfig, $privateMessages, $utils;

    public $playersInMatches = 0;

    public $ironSoupMatches = [];
    public $kohiMatches = [];
    public $gappleMatches = [];
    public $buhcMatches = [];

    public $loggedIn = [];
    public $tempPass = [];
    public $isSetting = [];
    public $npcDelete = [];
    public $test_version = "";

    public $sql;

    public function onLoad(){
        self::$obj = $this;
        date_default_timezone_set('EST');
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."World_Backups");
        $this->getLogger()->info(Prefix::DEFAULT."Loading...");
    }

    public function onEnable(){
        $this->settings = new Config($this->getDataFolder()."settings.yml", Config::YAML, ["Prefix" => "Core","use_sql" => false, "server-name" => "server-name", "username" => "username", "db_name" => "database-name","password" => "password", "port" => 3306]);
        $this->database = new Config($this->getDataFolder()."database.yml", Config::YAML, []);
        $this->matchesConfig = new Config($this->getDataFolder()."matches.yml", Config::YAML, ["Kohi1v1-matches" => [],"IronSoup1v1-matches" => [], "BUHC1v1-matches" => [], "Gapple1v1-matches" => []]);
        $this->privateMessages = new Config($this->getDataFolder()."private-messages.txt", Config::ENUM, []);
        $this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
        date_default_timezone_set('EST');
        $this->getLogger()->info(Prefix::DEFAULT."Date: ".date("D, F d, Y, H:i T"));
        CoreCommand::registerAll($this, $this->getServer()->getCommandMap());
        $this->registerMatches();
        $this->registerTasks();
        $this->test_version = Utils::generateRandomString(20);
        $this->getLogger()->info(Prefix::DEFAULT."Enabled! Test version: ".$this->test_version);
        if($this->settings->get("use_sql")){
            $this->sql = new MySQLProvider($this);
            $this->sql->process();
        }else{
            $this->getLogger()->info(Prefix::DEFAULT_BAD."MySQLProvider not enabled! Using YAML");
        }
        $this->utils = new Utils($this);
    }


    //TODO: move some functions to utils class file ?

    public function onDisable(){
        $this->cancelTasks();
    }

    /**
     * @return Main
     */
    public static function getInstance() : self{
        return self::$obj;
    }

    /**
     * @param $salt
     * @param $password
     * @return string
     */
    public function hash($salt, $password){
        $salt = strtolower($salt);
        return bin2hex(hash("sha512", $password . $salt, true) ^ hash("whirlpool", $salt . $password, true));
    }

    /**
     * @return Utils
     */
    public function getUtils() : Utils{
        return $this->utils;
    }

    public function getMySQL() : MySQLProvider{
        return $this->sql;
    }

    public function registerMatches(){
        if($this->matchesConfig->exists("Kohi1v1-matches")){
            foreach($this->matchesConfig->get("Kohi1v1-matches") as $match){
                $this->kohiMatches[$match["Name"]] = new KohiManager($this, $match["Name"]);
            }
            $this->getLogger()->info(Prefix::DEFAULT."Kohi1v1 loaded ".count($this->matchesConfig->get("Kohi1v1-matches"))." matches!");
        }

        if($this->matchesConfig->exists("IronSoup1v1-matches")){
            foreach($this->matchesConfig->get("IronSoup1v1-matches") as $match){
                $this->ironSoupMatches[$match["Name"]] = new IronSoupManager($this, $match["Name"]);
            }
            $this->getLogger()->info(Prefix::DEFAULT."IronSoup1v1 loaded ".count($this->matchesConfig->get("IronSoup1v1-matches"))." matches!");
        }
        if($this->matchesConfig->exists("BUHC1v1-matches")){
            foreach($this->matchesConfig->get("BUHC1v1-matches") as $match){
                $this->buhcMatches[$match["Name"]] = new BUHCManager($this, $match["Name"]);
            }
            $this->getLogger()->info(Prefix::DEFAULT."BUHC1v1 loaded ".count($this->matchesConfig->get("BUHC1v1-matches"))." matches!");
        }
    }

    public function registerTasks(){
        $x = $this->getServer()->getDefaultLevel()->getSpawnLocation()->x;
        $y = $this->getServer()->getDefaultLevel()->getSpawnLocation()->y;
        $z = $this->getServer()->getDefaultLevel()->getSpawnLocation()->z;
        $this->getScheduler()->scheduleRepeatingTask(new ParticlesTask($this, new Vector3($x, $y+4.5, $z)), 20*4.5);
        $this->getScheduler()->scheduleRepeatingTask(new ServerRestartTask($this), 20*1);
        //$this->getScheduler()->scheduleRepeatingTask(new QueueTask($this), 20*1);
    }


    public function cancelTasks(){
        foreach(self::$tasks as $task => $key){
            $this->getScheduler()->cancelTask($task);
        }
        $this->getScheduler()->cancelAllTasks();
    }

    /**
     * @param array $pos1
     * @param array $pos2
     * @param string $type
     */
    public function newMatch(array $pos1, array $pos2, string $type){
        $cfg = $this->matchesConfig->getAll();
        if($type === "Kohi1v1"){
            $int = count($cfg["Kohi1v1-matches"])+1;
            $cfg["Kohi1v1-matches"][$int] = ["Name" => "", "Positions" => ["pos1" => [], "pos2" => []]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $cfg["Kohi1v1-matches"][$int] = ["Name" => $int, "Positions" => ["pos1" => $pos1, "pos2" => $pos2]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $this->kohiMatches[$int] = new KohiManager($this, $int);
        }
        if($type === "IronSoup1v1"){
            $int = count($cfg["IronSoup1v1-matches"])+1;
            $cfg["IronSoup1v1-matches"][$int] = ["Name" => "", "Positions" => ["pos1" => [], "pos2" => []]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $cfg["IronSoup1v1-matches"][$int] = ["Name" => $int, "Positions" => ["pos1" => $pos1, "pos2" => $pos2]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $this->ironSoupMatches[$int] = new IronSoupManager($this, $int);
        }
        if($type === "BUHC1v1"){
            $int = count($cfg["BUHC1v1-matches"])+1;
            $cfg["BUHC1v1-matches"][$int] = ["Name" => "", "Positions" => ["pos1" => [], "pos2" => []]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $cfg["BUHC1v1-matches"][$int] = ["Name" => $int, "Positions" => ["pos1" => $pos1, "pos2" => $pos2]];
            $this->matchesConfig->setAll($cfg);
            $this->matchesConfig->save();
            $this-> buhcMatches[$int] = new BUHCManager($this, $int);
        }
    }

    public function getChatRank($rank){
        if($rank === "default"){
            return "";
        }else if($rank === "owner"){
            return TextFormat::BOLD.TextFormat::BLACK."[".TextFormat::GOLD."Owner".TextFormat::BLACK."] ";
        }else if($rank === "admin"){
            return TextFormat::BOLD.TextFormat::BLACK."[".TextFormat::GOLD."Admin".TextFormat::BLACK."] ";
        }else if($rank === "mod"){
            return TextFormat::BOLD.TextFormat::BLACK."[".TextFormat::GOLD."Mod".TextFormat::BLACK."] ";
        }else if($rank === "builder"){
            return TextFormat::BOLD.TextFormat::BLACK."[".TextFormat::GOLD."Mod".TextFormat::BLACK."] ";
        }
    }


    /**
     * @param PlayerClass $player
     */
    public function findKohiMatch(PlayerClass $player){
        if($player->isQueued() && $player->isOnline()){
            if(count($this->kohiMatches) === 0){
                $player->sendMessage(Prefix::DEFAULT_BAD."There are no games available right now for ".$player->gameType."!");
                $player->setQueued(false, false, null);
                return;
            }
            foreach($this->kohiMatches as $match){
                if($match instanceof GameManager){
                    if(count($match->getPlayers()) === 1 or count($match->getPlayers()) === 0 && $player->joinMatch($match) === true){
                        $player->joinMatch($match);
                        return;
                    }else{
                        $this->findKohiMatch($player);
                        return;
                    }
                }else{
                    $this->findKohiMatch($player);
                    return;
                }
            }
        }else{
            return;
        }
    }

    /**
     * @param PlayerClass $player
     */
    public function findIronSoupMatch(PlayerClass $player){
        if($player->isQueued() && $player->isOnline()){
            if(count($this->ironSoupMatches) === 0){
                $player->sendMessage(Prefix::DEFAULT_BAD."There are no games available right now for ".$player->gameType."!");
                $player->setQueued(false, false, null);
                return;
            }
            foreach($this->ironSoupMatches as $match){
                if($match instanceof GameManager){
                    if(count($match->getPlayers()) === 1 or count($match->getPlayers()) === 0 && $player->joinMatch($match) === true){
                        $player->joinMatch($match);
                        return;
                    }else{
                        $this->findIronSoupMatch($player);
                        return;
                    }
                }else{
                    $this->findIronSoupMatch($player);
                    return;
                }
            }
        }else{
            return;
        }
    }

    /**
     * @param PlayerClass $player
     */
    public function findBUHCMatch(PlayerClass $player){
        if($player->isQueued() && $player->isOnline()){
            if(count($this->buhcMatches) === 0){
                $player->sendMessage(Prefix::DEFAULT_BAD."There are no games available right now for ".$player->gameType."!");
                $player->setQueued(false, false, null);
                return;
            }
            foreach($this->buhcMatches as $match){
                if($match instanceof GameManager){
                    if(count($match->getPlayers()) === 1 or count($match->getPlayers()) === 0 && $player->joinMatch($match) === true){
                        $player->joinMatch($match);
                        return;
                    }else{
                        $this->findBUHCMatch($player);
                        return;
                    }
                }else{
                    $this->findBUHCMatch($player);
                    return;
                }
            }
        }else{
            return;
        }
    }
}