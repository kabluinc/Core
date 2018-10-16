<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/18/2017
 * Time: 8:48 AM
 */

namespace Core\tasks;


use Core\Main;
use Core\managers\GameManager;
use Core\managers\KohiManager;
use Core\player\PlayerClass;
use Core\utils\Prefix;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class KohiTask extends Task {

    private $plugin, $server, $match;

    public function __construct(Main $plugin, GameManager $match){
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
        $this->match = $match;
        $this->plugin->getLogger()->critical("Task starting for ".$this->match->getGameType()." match #".$this->match->getName());
    }

    /**
     * @return Main
     */
    public function getPlugin() : Main{
        return $this->plugin;
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(): \pocketmine\Server{
        return $this->server;
    }

    /**
     * @return KohiManager
     */
    public function getMatch() : GameManager{
        return $this->match;
    }

    public function onRun(int $currentTick){
        $match = $this->getMatch();
        if($match->getStatus() === KohiManager::WAITING){
            if(count($match->getPlayers()) === 0) $match->end();
            if(count($match->getPlayers()) !== 2){
                foreach($match->getPlayers() as $name){
                    $player = $this->getServer()->getPlayer($name);
                    if($player instanceof PlayerClass){
                        $player->sendTip(TextFormat::AQUA."               Waiting...".TextFormat::RED."\n     Need more players...[".count($match->getPlayers())."/2]");
                    }
                }
            }else if(count($match->getPlayers()) === 2){
                $match->start();
            }
        }
        if($match->getStatus() === GameManager::PVP){
            $match->setTime(($match->getTime()-1));
            foreach($match->getPlayers() as $name){
                $player = $this->getServer()->getPlayer($name);
                $player->sendTip(TextFormat::AQUA."Match time: ".TextFormat::WHITE.gmdate("i:s",$match->getTime()));
            }
        }
    }
}