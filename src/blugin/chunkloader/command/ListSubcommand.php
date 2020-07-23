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
use pocketmine\command\CommandSender;
use pocketmine\world\World;

class ListSubcommand extends Subcommand{
    use DefaultArgumentTrait;

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
        $world = $this->getWorld($sender, array_shift($args));
        /** @var ChunkLoader $plugin */
        $plugin = $this->getMainCommand()->getOwningPlugin();
        $chunkHashs = $plugin->getByWorld($world);
        if(empty($chunkHashs)){
            $this->sendMessage($sender, "failure.empty", [$world->getFolderName()]);
            return true;
        }

        $list = array_chunk($chunkHashs, $sender->getScreenLineHeight());
        $page = NumberArgumentValidator::validateRange(array_shift($args) ?? "1", 1, count($list));

        //Send list of registered chunk
        $this->sendMessage($sender, "head", [$world->getFolderName(), $page, count($list)]);
        if(isset($list[$page - 1])){
            foreach($list[$page - 1] as $chunkHash){
                World::getXZ($chunkHash, $chunkX, $chunkZ);
                $this->sendMessage($sender, "item", [$chunkX, $chunkZ]);
            }
        }
        return true;
    }
}
