<?php

/**
 * Created by PhpStorm.
 * User: 20deavaults
 * Date: 10/3/18
 * Time: 9:11 AM
 */

namespace Core\commands\custom;

use Core\Main;
use Core\commands\CoreCommand;
use pocketmine\command\CommandSender;

class TestCommand extends CoreCommand{

    private $plugin, $server, $name;

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

    }
}