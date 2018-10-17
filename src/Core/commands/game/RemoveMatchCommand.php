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

class RemoveMatchCommand extends CoreCommand{

    private $plugin, $server;

    /**
     * RemoveMatchCommand constructor.
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
        //SOON
    }
}