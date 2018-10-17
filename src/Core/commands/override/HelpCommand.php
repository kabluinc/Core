<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/12/2017
 * Time: 4:17 PM
 */

namespace Core\commands\override;


use Core\Main;
use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use Core\commands\CoreCommand;
use pocketmine\plugin\Plugin;

class HelpCommand extends CoreCommand{

    private $plugin, $server;

    /**
     * HelpCommand constructor.
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
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."No help pages currently!");
    }
}