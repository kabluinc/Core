<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/15/2017
 * Time: 5:47 PM
 */

namespace Core\commands\custom;

use Core\commands\CoreCommand;
use Core\Main;
use Core\player\PlayerClass;
use Core\utils\Prefix;
use Core\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class QuitCommand extends CoreCommand{

    private $plugin, $server;

    public function __construct(Main $plugin, $name, $desc, $usage, array $aliases = []){
        parent::__construct($plugin, $name, $desc, $usage, $aliases);
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
    }

    public function getPlugin() : Main{
        return $this->plugin;
    }

    public function getServer(){
        return $this->server;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if($sender instanceof PlayerClass){
            if($sender->isQueued()){
                $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You have left the game/stopped queueing!");
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
                Utils::sendLobbyItems($sender);
            }else{
                $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You aren't in a match nor are you queueing.");
            }
        }
    }
}