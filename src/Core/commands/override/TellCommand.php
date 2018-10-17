<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 4/29/2017
 * Time: 12:34 PM
 */

namespace Core\commands\override;

use Core\Main;

use Core\player\PlayerClass;
use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use Core\commands\CoreCommand;
use pocketmine\plugin\Plugin;

class TellCommand extends CoreCommand{

    private $plugin;
    private $server;

    /**
     * TellCommand constructor.
     * @param Main $plugin
     * @param string $name
     * @param null|string $desc
     * @param array|\string[] $usage
     * @param array $aliases
     */
    public function __construct(Main $plugin, $name, $desc, $usage, array $aliases = []){
        parent::__construct($plugin, $name, $desc, $usage, $aliases);
        $this->plugin = $plugin;
        $this->server = $this->plugin->getServer();
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
    public function getServer(){
        return $this->server;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(isset($args[0])){
            $player = $this->getServer()->getPlayer($args[0]);
            if($player === null){
                $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::PLAYER_NOT_ONLINE));
            }else{
                if($sender instanceof PlayerClass and $player instanceof PlayerClass){
                    $message = implode(" ", $args[1]);
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT).$sender->getRealName()."->You ".$message);
                    $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You messaged ".$player->getRealName().": ".$message);
                    $privateMessage = date("D, F d, Y, H:i T")." ".$sender->getRealName()."->".$player->getRealName().": ".$message;
                    $this->getPlugin()->privateMessages->set($privateMessage);
                    $this->getPlugin()->privateMessages->save();
                }
            }
        }
    }

}