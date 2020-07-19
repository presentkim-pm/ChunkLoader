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

class RegisterSubcommand extends Subcommand{
    use DefaultArgumentTrait;

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
        $this->getAllArguments($sender, $args, $chunkX, $chunkZ, $world);
        /** @var ChunkLoader $plugin */
        $plugin = $this->getMainCommand()->getOwningPlugin();
        $translateArgs = [$chunkX, $chunkZ, $world->getFolderName()];
        if(!$world->isChunkGenerated($chunkX, $chunkZ)){
            $this->sendMessage($sender, "failure.notGenerated", $translateArgs);
        }elseif(!$plugin->registerChunk($chunkX, $chunkZ, $world->getFolderName())){
            $this->sendMessage($sender, "failure.already", $translateArgs);
        }else{
            $this->sendMessage($sender, "success", $translateArgs);
        }
        return true;
    }
}