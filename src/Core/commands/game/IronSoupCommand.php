<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/15/2017
 * Time: 5:01 PM
 */

namespace Core\commands\game;


use Core\commands\CoreCommand;
use Core\Main;
use Core\managers\GameManager;
use Core\player\PlayerClass;
use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class IronSoupCommand extends CoreCommand{

    private $plugin, $server;

    /**
     * IronSoupCommand constructor.
     * @param Main $plugin
     * @param string $name
     * @param null|string $desc
     * @param array|\string[] $usage
     * @param array $aliases
     */
    public function __construct(Main $plugin, $name, $desc, $usage, array $aliases = []){
        parent::__construct($plugin, $name, $desc, $usage, $aliases);
        $this->plugin = $plugin;
        $this->server = $plugin->getServer();
    }


                public function getPlugin() : Main{
                    return $this->plugin;
                }

                /**
                 * @return \pocketmine\Server
                 */
                public function getServer(){
                    return $this->server;
                }

                /**
                 * @param CommandSender $sender
                 * @param string $commandLabel
                 * @param array $args
                 * @return void
                 */
                public function execute(CommandSender $sender, string $commandLabel, array $args){
                    if($args[0] === "join"){
                        if($sender instanceof PlayerClass && $sender->isQueued() && $sender->gameType !== null){
                            $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You are already queueing for ".$sender->gameType);
                            return;
                        }
                        if($sender instanceof PlayerClass){
                            $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You are queued and looking for a match....");
                            $sender->setQueued(true, false, "IronSoup1v1");
                            $this->getPlugin()->findIronSoupMatch($sender);
                        }else{
                            $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Your player data isn't compatible with the server please rejoin!");
                        }
                    }
                    if($args[0] === "set"){
                        $this->getPlugin()->isSetting[$sender->getName()] = [];
                        $this->getPlugin()->isSetting[$sender->getName()]["type"] = "IronSoup1v1";
                        $this->getPlugin()->isSetting[$sender->getName()]["int"] = 0;
                        $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Please tap the first position for IronSoup1v1!");
                    }
                }
}