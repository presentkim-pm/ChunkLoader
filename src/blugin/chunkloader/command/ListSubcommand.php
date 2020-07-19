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
use blugin\lib\command\validator\defaults\WorldArgumentValidator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\World;

class ListSubcommand extends Subcommand{
    /** @return string */
    public function getLabel() : string{
        return "list";
    }

    /**
     * @param CommandSender $sender
     * @param string[]      $args = []
     *
     * @return bool
     */
    public function execute(CommandSender $sender, array $args = []) : bool{
        //Get world name from args or player
        $worldName = null;
        if(isset($args[0])){
            $worldName = WorldArgumentValidator::validate($args[0])->getFolderName();
        }elseif($sender instanceof Player){
            $worldName = $sender->getWorld()->getFolderName();
        }else{
            return false;
        }

        //Make chunkhash list for show command
        /** @var ChunkLoader $plugin */
        $plugin = $this->getMainCommand()->getOwningPlugin();
        $chunkHashs = $plugin->getChunkDataMap($worldName)->getAll();
        $list = array_chunk($chunkHashs, $sender->getScreenLineHeight());
        $max = count($list);

        //Get page number from args
        if(isset($args[1]) && is_numeric($args[1]) && $args[1] >= 1){
            $page = min($max, (int) $args[1]);
        }else{
            $page = 1;
        }

        //Send list of registered chunk
        $this->sendMessage($sender, "head", [
            $worldName,
            (string) $page,
            (string) $max
        ]);
        if(isset($list[$page - 1])){
            foreach($list[$page - 1] as $chunkHash){
                World::getXZ($chunkHash, $chunkX, $chunkZ);
                $this->sendMessage($sender, "item", [
                    (string) $chunkX,
                    (string) $chunkZ
                ]);
            }
        }
        return true;
    }
}
