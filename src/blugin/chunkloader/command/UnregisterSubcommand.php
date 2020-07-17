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

use pocketmine\{
	Player, Server
};
use pocketmine\command\CommandSender;

class UnregisterSubcommand extends Subcommand{
	public const LABEL = "unregister";

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args = []) : bool{
		if(isset($args[0])){
			if(!is_numeric($args[0])){
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.generic.num.notNumber", [$args[0]]));
				return true;
			}else{
				$chunkX = (int) $args[0];
			}
		}elseif($sender instanceof Player){
			$chunkX = $sender->x >> 4;
		}else{
			return false;
		}
		if(isset($args[1])){
			if(!is_numeric($args[1])){
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.generic.num.notNumber", [$args[1]]));
				return true;
			}else{
				$chunkZ = (int) $args[1];
			}
		}elseif($sender instanceof Player){
			$chunkZ = $sender->z >> 4;
		}else{
			return false;
		}
		if(isset($args[2])){
			$level = Server::getInstance()->getLevelByName($args[2]);
			if($level === null){
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.unregister.failure.invalidWorld", [$args[2]]));
				return true;
			}
		}elseif($sender instanceof Player){
			$level = $sender->getLevel();
		}else{
			return false;
		}
		if(!$this->plugin->unregisterChunk($chunkX, $chunkZ, $level->getFolderName())){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.unregister.failure.notRegistered", [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.unregister.success", [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}
	}
}