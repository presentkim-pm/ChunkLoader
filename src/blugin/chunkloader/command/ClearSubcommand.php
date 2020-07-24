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
use pocketmine\command\CommandSender;
use pocketmine\level\Level as World;
use pocketmine\Server;

class ClearSubcommand extends Subcommand{
    use DefaultArgumentTrait;

    /** @return string */
    public function getLabel() : string{
        return "clear";
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
            $sender->sendMessage(Server::getInstance()->getLanguage()->translateString("commands.generic.emptyWorld", [$world->getFolderName()]));
            return true;
        }

        foreach($chunkHashs as $key => $chunkHash){
            World::getXZ($chunkHash, $chunkX, $chunkZ);
            $plugin->unregisterChunk($world, $chunkX, $chunkZ);
        }
        $this->sendMessage($sender, "success", [$world->getFolderName()]);
        return true;
    }
}
