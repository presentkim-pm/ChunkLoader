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

use blugin\chunkloader\ChunkLoader;
use blugin\lib\command\Subcommand;
use blugin\lib\command\validator\defaults\NumberArgumentValidator;
use blugin\lib\command\validator\defaults\WorldArgumentValidator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RegisterSubcommand extends Subcommand{
    /** @return string */
    public function getLabel() : string{
        return "register";
    }

    /**
     * @param CommandSender $sender
     * @param string[]      $args = []
     *
     * @return bool
     */
    public function execute(CommandSender $sender, array $args = []) : bool{
        if(isset($args[0])){
            $chunkX = (int) NumberArgumentValidator::validate($args[0]);
        }elseif($sender instanceof Player){
            $chunkX = $sender->getPosition()->getX() >> 4;
        }else{
            return false;
        }
        if(isset($args[1])){
            $chunkZ = (int) NumberArgumentValidator::validate($args[1]);
        }elseif($sender instanceof Player){
            $chunkZ = $sender->getPosition()->getZ() >> 4;
        }else{
            return false;
        }
        if(isset($args[2])){
            $world = WorldArgumentValidator::validate($args[2]);
        }elseif($sender instanceof Player){
            $world = $sender->getWorld();
        }else{
            return false;
        }
        /** @var ChunkLoader $plugin */
        $plugin = $this->getMainCommand()->getOwningPlugin();
        if(!$world->isChunkGenerated($chunkX, $chunkZ)){
            $this->sendMessage($sender, "failure.notGenerated", [
                (string) $chunkX,
                (string) $chunkZ,
                $world->getFolderName()
            ]);
        }elseif(!$plugin->registerChunk($chunkX, $chunkZ, $world->getFolderName())){
            $this->sendMessage($sender, "failure.already", [
                (string) $chunkX,
                (string) $chunkZ,
                $world->getFolderName()
            ]);
        }else{
            $this->sendMessage($sender, "success", [
                (string) $chunkX,
                (string) $chunkZ,
                $world->getFolderName()
            ]);
        }
        return true;
    }
}