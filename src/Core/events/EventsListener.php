<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 4/27/2017
 * Time: 5:12 PM
 */

namespace Core\events;

use Core\Main;

use Core\managers\GameManager;
use Core\npc\HumanNPC;
use Core\player\PlayerClass;
use Core\utils\Permissions;
use Core\utils\Prefix;
use Core\utils\Utils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class EventsListener implements Listener{

    private $plugin;

    /**
     * EventsListener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @return Main
     */
    public function getPlugin(){
        return $this->plugin;
    }

    /**
     * @return \pocketmine\Server
     */
    public function getServer(){
        return $this->getPlugin()->getServer();
    }

    /**
     * @param PlayerCreationEvent $ev
     * @priority HIGHEST
     */
    public function setPlayerClass(PlayerCreationEvent $ev){
        $ev->setPlayerClass(PlayerClass::class);
    }

    /**
     * @param PlayerMoveEvent $ev
     * @return bool
     */
    public function onMove(PlayerMoveEvent $ev){
        $player = $ev->getPlayer();
        if($player instanceof PlayerClass){
            if ($player->isRegistered() && !$player->isLoggedIn()) {
                $ev->setCancelled();
                return false;
            }
            if (!$player->isRegistered() && !$player->isLoggedIn()) {
                $ev->setCancelled();
                return false;
            }
            if (!$player->isRegistered()) {
                $ev->setCancelled();
                return false;
            }
            if($player->getMatch() === null){
                return false;
            }
            if($player->isQueued() && $player->getMatch()->getStatus() === GameManager::WAITING){
                $ev->setCancelled();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @param PlayerPreLoginEvent $ev
     * @priority HIGHEST
     */
    public function onPreJoin(PlayerPreLoginEvent $ev){
        $player = $ev->getPlayer();
        foreach($this->getServer()->getOnlinePlayers() as $onp){
            if($onp instanceof PlayerClass){
                if($onp->getRealName() === $player->getName()){
                    if($onp->isLoggedIn()){
                        $player->close("Player is already logged in!","Kicked");
                    }else{
                        $player->close("","");
                        $onp->close("","");
                    }
                }
            }
        }
    }

    /**
     * @param PlayerInteractEvent $ev
     */
    public function onInteract(PlayerInteractEvent $ev){
        $player = $ev->getPlayer();
        $username = $player->getName();
        $block = $ev->getBlock();
        if(isset($this->getPlugin()->isSetting[$player->getName()])){
            switch($this->getPlugin()->isSetting[$username]["int"]){
                case 0:
                    $this->pos1 = ["x" => $block->x,
                        "y" => $block->y,
                        "z" => $block->z,
                        "level" => $player->getLevel()->getName()];
                    $this->getPlugin()->isSetting[$username]["int"]++;
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Position one set please select the next!");
                    break;
                case 1:
                    $this->pos2 = ["x" => $block->x,
                        "y" => $block->y,
                        "z" => $block->z,
                        "level" => $player->getLevel()->getName()];
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Done! All positions set!");
                    $this->getPlugin()->newMatch($this->pos1, $this->pos2, $this->getPlugin()->isSetting[$username]["type"]);
                    unset($this->getPlugin()->isSetting[$username]);
                    break;
            }
        }
        if($ev->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
            if($player->getInventory()->getItemInHand()->getId() === Item::MUSHROOM_STEW){
                if($player->getHealth() === $player->getMaxHealth()){
                    return;
                }else{
                    $player->getInventory()->remove(Item::get(Item::MUSHROOM_STEW,0,1));
                    $player->setHealth(($player->getHealth() + 1.5));
                }
            }
            //TODO: join games via items in inventory!( if i cant fix npcs :( )
            if($player instanceof PlayerClass && $player->isLoggedIn()){
                $inventory = $player->getInventory();
                if($inventory->getItemInHand()->getId() === Item::WOODEN_SWORD){
                    $this->getPlugin()->getServer()->dispatchCommand($player, "kohi join");
                }else if($inventory->getItemInHand()->getId() === Item::STONE_SWORD){
                    $this->getPlugin()->getServer()->dispatchCommand($player, "ironsoup join");
                }else if($inventory->getItemInHand()->getId() === Item::IRON_SWORD){
                    $this->getPlugin()->getServer()->dispatchCommand($player, "gapple join");
                }else{
                    if($inventory->getItemInHand()->getId() === Item::GOLDEN_SWORD){
                        $this->getPlugin()->getServer()->dispatchCommand($player, "buhc join");
                    }
                }
            }
        }
    }

    /**
     * @param PlayerJoinEvent $ev
     */
    public function onJoin(PlayerJoinEvent $ev){
        $player = $ev->getPlayer();
        $ev->setJoinMessage(null);
        if($player instanceof PlayerClass){
            Utils::sendLobbyItems($player);
            if($player->isRegistered()){
                $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::LOGIN));
            }else{
                $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::REGISTER));
            }
        }else{
            $player->close("", TextFormat::RED . "Kicked due to " . $player->getName() . " not being a PlayerClass interface!\nPlease try again!");
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event){
        $entity = $event->getEntity();
        if($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            if($damager instanceof PlayerClass){
                if($entity->getHealth() - $event->getFinalDamage() <= 0){
                    $event->setCancelled(true);
                    $damager->getMatch()->win();
                    $damager->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You won!");
                    if($entity instanceof PlayerClass){
                        $entity->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT_BAD)."You lost!");
                        //TODO: stats update!
                    }
                }
            }
        }
    }

    /**
     * @param PlayerQuitEvent $ev
     */
    public function onQuit(PlayerQuitEvent $ev){
        $player = $ev->getPlayer();
        $ev->setQuitMessage(null);
        if($player instanceof PlayerClass){
            if(isset($this->getPlugin()->tempPass[$player->getName()])) unset($this->getPlugin()->tempPass[$player->getName()]);
            if($player->isLoggedIn()){
                $player->logout();
            }
            if($player->isQueued()){
                $player->setQueued(false, false, null);
                if($player->getMatch() !== null){
                    foreach($player->getMatch()->getPlayers() as $name){
                        $player = $this->getServer()->getPlayer($name);
                        if($player instanceof PlayerClass){
                            $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Match ended due to other player leaving!");
                            $player->getMatch()->end();
                            if($player->getMatch() !== null){
                                $player->getMatch()->removePlayer($player);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param PlayerCommandPreprocessEvent $ev
     */
    public function onCommandPreProcess(PlayerCommandPreprocessEvent $ev){
        $player = $ev->getPlayer();
        if($player instanceof PlayerClass){
            if($player->isQueued() && $player->inGame === true){
                if(strpos($ev->getMessage(), "/quit", 0) !== true){
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Please use /quit to quit matches!");
                    $ev->setCancelled(true);
                }
            }
            if($player->isLoggedIn() === false){
                if(strpos($ev->getMessage(), "/", 0) !== false){
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Please login to use commands!");
                    $ev->setCancelled(true);
                }
            }
        }
    }

   /* public function onDeath(PlayerDeathEvent $ev){
        $ev->setDeathMessage(null);
        $entity = $ev->getEntity();
        if($ev instanceof EntityDamageByEntityEvent){
            $cause = $entity->getLastDamageCause();
            if($entity instanceof Player){
                $player = $entity;
                if($player instanceof PlayerClass){
                    $killer = $cause->getDamager();
                }
            }
        }
    }*/


    /**
     * @param PlayerChatEvent $ev
     */
    public function onChat(PlayerChatEvent $ev){
        $player = $ev->getPlayer();
        $message = $ev->getMessage();
        if($player instanceof PlayerClass){
            $database = $this->getPlugin()->database->getAll();
            if($player->isRegistered() && $player->isLoggedIn()){
                if($this->getPlugin()->hash($player->getName(), $message) === $player->getPassword()){
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::PASSWORD_IN_CHAT));//password in chat
                    $ev->setCancelled();
                }
            }
            if(!$player->isLoggedIn() && $player->isRegistered()){
                if($this->getPlugin()->hash($player->getName(), $message) === $database[strtolower($player->getName())]["password"]){
                    $player->login();
                    $ev->setCancelled();
                }else{
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::PASSWORD_INCORRECT));
                    if(!isset($player->loginTrys[$player->getName()])) $player->loginTrys[$player->getName()] = 0;
                    $player->loginTrys[$player->getName()]++;
                    if($player->loginTrys[$player->getName()] === 5){
                        $player->close(" ",$this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."You have been kicked for reaching the max login attempt!");
                    }
                    $ev->setCancelled();
                }
                if($player->isLoggedIn() && $player->isRegistered()){
                    foreach(Main::$badwords as $badword){
                        if(strpos(strtolower($message), strtolower($badword)) === true){
                            $new_message = str_replace($badword, TextFormat::BLACK."****", $message);
                            $ev->setMessage($new_message);
                        }
                    }
                }
            }
            if(!$player->isRegistered() && !$player->isLoggedIn()){
                if(isset($this->getPlugin()->tempPass[$player->getName()]) && $message === $this->getPlugin()->tempPass[$player->getName()]){
                    $database[strtolower($player->getName())] = [];
                    $database[strtolower($player->getName())]["password"] = $this->getPlugin()->hash($player->getName(), $this->getPlugin()->tempPass[$player->getName()]);
                    $database[strtolower($player->getName())]["uuid"] = $player->getUniqueId()->toString();
                    $database[strtolower($player->getName())]["time"] = date("D, F d, Y, H:i T");
                    $database[strtolower($player->getName())]["rank"] = "default";
                    $database[strtolower($player->getName())]["uploaded_to_sql"] = false;
                    $database[strtolower($player->getName())]["name"] = strtolower($player->getName());
                    $database[$player->getName()]["lang"] = "en";
                    $database[strtolower($player->getName())]["permissions"][Permissions::DEFAULT] = true;
                    $database[strtolower($player->getName())]["permissions"][Server::BROADCAST_CHANNEL_USERS] = true;
                    $this->getPlugin()->database->setAll($database);
                    $this->getPlugin()->database->save();
                    $this->getPlugin()->database->reload();
                    unset($this->getPlugin()->tempPass[$player->getName()]);
                    $player->login();//login via password
                    $ev->setCancelled();
                    unset($this->getPlugin()->tempPass[$player->getName()]);
                }
                if(!isset($this->getPlugin()->tempPass[$player->getName()]) && !$player->isRegistered() && !$player->isLoggedIn()){
                    $this->getPlugin()->tempPass[$player->getName()] = $message;
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Please type your password one more time");
                    $ev->setCancelled();
                }
                if(isset($this->getPlugin()->tempPass[$player->getName()]) && $message !== $this->getPlugin()->tempPass[$player->getName()] && !$player->isRegistered() && $player->isLoggedIn()){
                    $player->sendMessage($this->getPlugin()->getUtils()->getChatMessages(Prefix::DEFAULT)."Passwords didn't match try again!");
                    $ev->setCancelled();
                }
            }
        }
        if($player instanceof PlayerClass && $player->isLoggedIn()){
            $database = $this->getPlugin()->database->getAll();
            $rank = $database[strtolower($player->getName())]["rank"];
            $ev->setFormat($this->getPlugin()->getChatRank($rank)."".TextFormat::RESET.TextFormat::AQUA.$player->getRealName().": ".TextFormat::RESET.TextFormat::WHITE.$message);
        }
    }

    /**
     * @param BlockBreakEvent $ev
     * @return bool
     */
    public function onBreak(BlockBreakEvent $ev){
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if($player instanceof PlayerClass){
            if(!$player->hasPermission(Permissions::BUILD) || !$player->hasPermission(Permissions::ALL_PERMS)){
                $ev->setCancelled(true);
                return false;
            }
        }
    }

    /**
     * @param BlockPlaceEvent $ev
     * @return bool
     */
    public function onPlace(BlockPlaceEvent $ev){
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        if ($player instanceof PlayerClass) {
            if (!$player->hasPermission(Permissions::PLACE) || !$player->hasPermission(Permissions::ALL_PERMS)){
                $ev->setCancelled(true);
                return false;
            }
        }
    }
}