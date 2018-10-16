<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/10/2017
 * Time: 6:51 PM
 */

namespace Core\commands\custom;

use Core\Main;
use Core\player\PlayerClass;
use Core\commands\CoreCommand;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class LangCommand extends CoreCommand{

    private $plugin;
    private $server;

    /**
     * LangCommand constructor.
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
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(isset($args[0])){
            if(isset(Main::$langs[$args[0]])){
                if(is_string($args[0])){
                    if($sender instanceof PlayerClass){
                        $sender->setLang($args[0]);
                        $sender->sendMessage("Set language preference to ".Main::$langs[$args[0]]);
                        //TODO: implement language preference formatting
                    }
                }
            }
        }
    }
}