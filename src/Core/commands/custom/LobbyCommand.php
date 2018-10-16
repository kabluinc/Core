<?php
/**
 * Created by PhpStorm.
 * User: 20deavaults
 * Date: 9/11/18
 * Time: 10:20 AM
 */

namespace Core\commands\custom;

use Core\commands\CoreCommand;
use Core\Main;
use Core\player\PlayerClass;
use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class LobbyCommand extends CoreCommand{

    private $plugin, $server;

    public function __construct(Main $plugin, $name, $desc, $usage, array $aliases = []){
        parent::__construct($plugin, $name, $desc, $usage, $aliases);
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
    }

    public function getPlugin(): Main{
        return $this->getPlugin();
    }

    public function getServer(){
        return $this->server;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender instanceof PlayerClass){
            if($sender->isQueued()){
                $sender->sendMessage(Prefix::DEFAULT."You have left the game/stopped queueing!");
                if($sender->getMatch() !== null){
                    $sender->removeFromMatch();
                }
                $sender->setQueued(false, false, null);
                $level = $this->getServer()->getDefaultLevel()->getSpawnLocation();
                $sender->teleport($level);
                $sender->removeAllEffects();
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->setMaxHealth(20);
                $sender->setHealth(20);
            }else{
                $level = $this->getServer()->getDefaultLevel()->getSpawnLocation();
                $sender->teleport($level);
                $sender->removeAllEffects();
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->setMaxHealth(20);
                $sender->setHealth(20);
            }
        }
    }
}
