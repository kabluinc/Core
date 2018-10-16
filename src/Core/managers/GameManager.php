<?php
/**
 * Created by PhpStorm.
 * User: Savion
 * Date: 9/14/2017
 * Time: 5:51 PM
 */

namespace Core\managers;


use Core\player\PlayerClass;

abstract class GameManager{

    const WAITING = 0;
    const PVP = 1;

    const KOHI = "Kohi1v1";
    const GAPPLE = "Gapple1v1";
    const IRON = "IronSoup1v1";
    const BUHC = "BUHC1v1";

    /**
     * @param PlayerClass $player
     * @return mixed
     */
    abstract public function removePlayer(PlayerClass $player);

    /**
     * @return mixed
     */
    abstract public function isJoinable();

    /**
     * @param $bool
     * @return mixed
     */
    abstract public function setJoinable($bool);

    /**
     * @param PlayerClass $player
     * @return mixed
     */
    abstract public function addPlayer(PlayerClass $player);

    /**
     * @return mixed
     */
    abstract public function getStatus();

    /**
     * @param $status
     * @return mixed
     */
    abstract public function setStatus($status);

    /**
     * @param int $time
     * @return mixed
     */
    abstract public function setTime(int $time);

    /**
     * @return mixed
     */
    abstract public function getTime();

    /**
     * @return mixed
     */
    abstract public function getPlayers();

    /**
     * @return mixed
     */
    abstract public function end();

    /**
     * @return mixed
     */
    abstract public function win();

    /**
     * @return mixed
     */
    abstract public function getGameType();

    /**
     * @return mixed
     */
    abstract public function getName();
}