<?php
/**
 * Created by PhpStorm.
 * User: 20deavaults
 * Date: 10/4/18
 * Time: 10:28 AM
 */

namespace Core\utils;


use Core\Main;
use Core\managers\GameManager;
use Core\player\PlayerClass;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class Utils{

    const MATCH_TYPES = ["Kohi1v1", "IronSoup1v1", "Gapple1v1", "BUHC1v1"];

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function getPlugin(){
        return $this->plugin;
    }

    /**
     * @param $levelName
     * Map that is being used and will be backed up/restored
     * @param GameManager $match
     */
    public static function resetMap($levelName, GameManager $match){
        if(dir(Main::getInstance()->getDataFolder()."World_Backups/$levelName") === null){
            $match->setJoinable(false);
            Main::getInstance()->getLogger()->critical("Map backup for match ".$match->getName()." for ".$match->getGameType()." is missing!");
            return;
        }
        self::resetLevel($levelName);
    }


    /**
     * @param $backupPath
     * @param $worldPath
     */
    public static function doBackup($backupPath, $worldPath){
        $zip = new \ZipArchive;
        $zip->open($backupPath, \ZipArchive::CREATE);
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($worldPath)) as $file){
            $zip->addFile($file, str_replace("\\", "/", ltrim(substr($file, strlen($worldPath)), "/\\")));
        }
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !==($file = readdir($dir))){
            if(($file != '.') && ($file != '..' )){
                if(is_dir($src . '/' . $file)){
                    self::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }else{
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param $levelName
     */
    public static function resetLevel($levelName){
        $server = Server::getInstance();
        $main = Main::getInstance();
        $worldPath = $server->getDataPath() . "worlds/".$levelName;
        self::file_delDir($worldPath);
        self::recurse_copy($main->getDataFolder()."World_Backups/".$levelName."/",$server->getDataPath()."worlds/".$levelName."/");
    }

    /**
     * @param $dir
     */
    public static function file_delDir($dir){
        $dir = rtrim($dir, "/\\") . "/";
        foreach(scandir($dir) as $file){
            if($file === "." or $file === ".."){
                continue;
            }
            $path = $dir . $file;
            if(is_dir($path)){
                self::file_delDir($path);
            }else{
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function generateRandomInt($length = 10) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function sendLobbyItems(PlayerClass $player){
        $inventory = $player->getInventory();
        $inventory->clearAll();
        $inventory->setHeldItemIndex(5, true);
        $inventory->setItem(0, ItemFactory::get(Item::WOODEN_SWORD)->setCustomName(TF::AQUA.TF::BOLD."Kohi1v1\n\n".TF::AQUA.TF::BOLD."~Tap sword on ground to join a match!~"), true);
        $inventory->setItem(1, ItemFactory::get(Item::STONE_SWORD)->setCustomName(TF::AQUA.TF::BOLD."IronSoup1v1\n\n".TF::AQUA.TF::BOLD."~Tap sword on ground to join a match!~"), true);
        $inventory->setItem(2, ItemFactory::get(Item::GOLDEN_SWORD)->setCustomName(TF::AQUA.TF::BOLD."BUHC1v1\n\n".TF::AQUA.TF::BOLD."~Tap sword on ground to join a match!~"), true);
        $inventory->setItem(3, ItemFactory::get(Item::IRON_SWORD)->setCustomName(TF::AQUA.TF::BOLD."Gapple1v1\n\n".TF::AQUA.TF::BOLD."~Tap sword on ground to join a match!~"), true);
        $inventory->setItem(4, ItemFactory::get(Item::DIAMOND_SWORD)->setCustomName(TF::AQUA.TF::BOLD."SOON....."), true);
        $inventory->sendContents($player);
    }

    public function getChatMessages($key){
        $message = $this->getPlugin()->settings->get($key);
        return $message;
    }
}