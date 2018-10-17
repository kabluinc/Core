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
use Core\utils\Permissions;
use Core\utils\Prefix;
use Core\utils\Utils;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
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
        $matches = $this->getPlugin()->matchesConfig->getAll();
        if($sender->hasPermission(Permissions::REMOVE_MATCH_COMMAND)){
            if(isset($args[0]) && isset(Utils::MATCH_TYPES[$args[0]])){
                if(isset($args[1]) && is_int($args[1])){
                    $matches[$args[0]][$args[1]][] = "REMOVED";
                    $this->getPlugin()->matchesConfig->setAll($matches);
                    $this->getPlugin()->matchesConfig->save();
                    $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Removed match #".$args[0]." for ".$args[1]);
                }else{
                    $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT_BAD)."Please provide a correct match number!");
                }
            }else{
                $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT_BAD)."That isn't a match type!"));
            }
        }else{
            $sender->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT_BAD)."You do not have permission to run this command or please join the server to run this command!");
        }
    }
}