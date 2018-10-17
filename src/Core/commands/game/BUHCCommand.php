<?php
/**
 * Created by PhpStorm.
 * User: 20deavaults
 * Date: 10/16/18
 * Time: 8:49 AM
 */

namespace Core\commands\game;


use Core\commands\CoreCommand;
use Core\Main;
use Core\managers\GameManager;
use Core\player\PlayerClass;
use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class BUHCCommand extends CoreCommand{

    private $plugin, $server;

    /**
     * BUHCCommand constructor.
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
        $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."In progress...");
    }
}