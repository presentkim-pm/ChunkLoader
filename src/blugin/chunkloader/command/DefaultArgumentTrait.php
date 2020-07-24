<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\chunkloader\command;

use blugin\lib\command\exception\defaults\ArgumentLackException;
use blugin\lib\command\validator\defaults\NumberArgumentValidator;
use blugin\lib\command\validator\defaults\WorldArgumentValidator;
use pocketmine\command\CommandSender;
use pocketmine\level\Level as World;
use pocketmine\Player;

trait DefaultArgumentTrait{
    /**
     * @param CommandSender $sender
     * @param string|null   $argument
     *
     * @return int
     */
    public function getChunkX(CommandSender $sender, ?string $argument) : int{
        if($argument !== null){
            return (int) NumberArgumentValidator::validate($argument);
        }elseif($sender instanceof Player){
            return $sender->getPosition()->getX() >> 4;
        }else{
            throw new ArgumentLackException();
        }
    }

    /**
     * @param CommandSender $sender
     * @param string|null   $argument
     *
     * @return int
     */
    public function getChunkZ(CommandSender $sender, ?string $argument) : int{
        if($argument !== null){
            return (int) NumberArgumentValidator::validate($argument);
        }elseif($sender instanceof Player){
            return $sender->getPosition()->getZ() >> 4;
        }else{
            throw new ArgumentLackException();
        }
    }

    /**
     * @param CommandSender $sender
     * @param string|null   $argument
     *
     * @return World
     */
    public function getWorld(CommandSender $sender, ?string $argument) : World{
        if($argument !== null){
            return WorldArgumentValidator::validate($argument);
        }elseif($sender instanceof Player){
            return $sender->getLevel();
        }else{
            throw new ArgumentLackException();
        }
    }

    /**
     * @param CommandSender  $sender
     * @param array          $args
     * @param int|null      &$chunkX
     * @param int|null      &$chunkZ
     * @param World|null    &$world
     */
    public function getAllArguments(CommandSender $sender, array $args, ?int &$chunkX, ?int &$chunkZ, ?World &$world) : void{
        $chunkX = $this->getChunkX($sender, $args[0] ?? null);
        $chunkZ = $this->getChunkZ($sender, $args[1] ?? null);
        $world = $this->getWorld($sender, $args[2] ?? null);
    }
}