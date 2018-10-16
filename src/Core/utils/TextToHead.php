<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 5/23/2017
 * Time: 3:52 PM
 */

namespace Core\utils;

use Core\player\PlayerClass;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TextToHead{

    /*
     * Shoutout to Legoboy of PocketMine forums for the code!
     */


    const TEXTFORMAT_RGB = [
        [0, 0, 0],
        [0, 0, 170],
        [0, 170, 0],
        [0, 170, 170],
        [170, 0, 0],
        [170, 0, 170],
        [255, 170, 0],
        [170, 170, 170],
        [85, 85, 85],
        [85, 85, 255],
        [85, 255, 85],
        [85, 255, 255],
        [255, 85, 85],
        [255, 85, 255],
        [255, 255, 85],
        [255, 255, 255]
    ];
    const TEXTFORMAT_LIST = [
        TextFormat::BLACK,
        TextFormat::DARK_BLUE,
        TextFormat::DARK_GREEN,
        TextFormat::DARK_AQUA,
        TextFormat::DARK_RED,
        TextFormat::DARK_PURPLE,
        TextFormat::GOLD,
        TextFormat::GRAY,
        TextFormat::DARK_GRAY,
        TextFormat::BLUE,
        TextFormat::GREEN,
        TextFormat::AQUA,
        TextFormat::RED,
        TextFormat::LIGHT_PURPLE,
        TextFormat::YELLOW,
        TextFormat::WHITE
    ];

    /**
     * @param $r
     * @param $g
     * @param $b
     * @return mixed
     */
    public static function rgbToTextFormat($r, $g, $b){
        $differenceList = [];
        foreach(self::TEXTFORMAT_RGB as $value){
            $difference = sqrt(pow($r - $value[0], 2) + pow($g - $value[1], 2) + pow($b - $value[2], 2));
            $differenceList[] = $difference;
        }
        $smallest = min($differenceList);
        $key = array_search($smallest, $differenceList);
        return self::TEXTFORMAT_LIST[$key];
    }

    /**
     * @param Player $player
     */
    public static function sendText(Player $player){
        $strArray = [];
        $skin = substr($player->getSkin()->getSkinData(), ($pos = (64 * 8 * 4)) - 4, $pos);
        for($y = 0; $y < 8; ++$y){
            for($x = 1; $x < 9; ++$x){
                if(!isset($strArray[$y])) $strArray[$y] = "";
                $key = ((64 * $y) + 8 + $x) * 4;
                $r = ord($skin{$key});
                $g = ord($skin{$key + 1});
                $b = ord($skin{$key + 2}); //$a = ord($skin{$key + 3});
                $format = self::rgbToTextFormat($r, $g, $b);
                $strArray[$y] .= $format . "█";
            }
        }
        foreach($strArray as $line){
            $player->sendMessage($line);
        }
    }

    /**
     * @param PlayerClass $player
     * @return array
     */
    public static function sendFloatingText(PlayerClass $player){
        $strArray = [];
        $skin = substr($player->getSkin()->getSkinData(), ($pos = (64 * 8 * 4)) - 4, $pos);
        for($y = 0; $y < 8; ++$y){
            for($x = 1; $x < 9; ++$x){
                if(!isset($strArray[$y])) $strArray[$y] = "";
                $key = ((64 * $y) + 8 + $x) * 4;
                $r = ord($skin{$key});
                $g = ord($skin{$key + 1});
                $b = ord($skin{$key + 2}); //$a = ord($skin{$key + 3});
                $format = self::rgbToTextFormat($r, $g, $b);
                $strArray[$y] .= $format . "█";
            }
        }
        return $strArray;
    }
}