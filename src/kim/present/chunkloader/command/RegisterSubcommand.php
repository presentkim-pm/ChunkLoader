<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\chunkloader\command;

use pocketmine\{
	Player, Server
};
use pocketmine\command\CommandSender;

class RegisterSubcommand extends Subcommand{
	public const LABEL = "register";

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
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.register.failure.invalidWorld", [$args[2]]));
				return true;
			}
		}elseif($sender instanceof Player){
			$level = $sender->getLevel();
		}else{
			return false;
		}
		if(!$level->isChunkGenerated($chunkX, $chunkZ)){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.register.failure.notGenerated", [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}elseif(!$this->plugin->registerChunk($chunkX, $chunkZ, $level->getFolderName())){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.register.failure.already", [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.register.success", [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}
		return true;
	}
}