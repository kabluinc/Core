<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 4/29/2017
 * Time: 12:57 PM
 */

namespace Core\commands\override;

use Core\Main;

use Core\utils\Prefix;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use Core\commands\CoreCommand;

class MeCommand extends CoreCommand{

    private $plugin;
    private $server;

    /**
     * MeCommand constructor.
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
       $sender->sendMessage(TextFormat::RED.TextFormat::BOLD."YOU CANT DO THAT COMMAND HAHA!");
        return;
    }
}