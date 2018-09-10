<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
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
use pocketmine\level\Level;

class ListSubcommand extends Subcommand{
	public const LABEL = "list";

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
			$level = Server::getInstance()->getLevelByName($args[0]);
			if($level === null){
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.list.failure.invalidWorld", [$args[0]]));
				return true;
			}
			$worldName = $args[0];
		}elseif($sender instanceof Player){
			$worldName = $sender->getLevel()->getFolderName();
		}else{
			return false;
		}

		//Make chunkhash list for show command
		$chunkHashs = $this->plugin->getChunkDataMap($worldName)->getAll();
		$list = array_chunk($chunkHashs, $sender->getScreenLineHeight());
		$max = count($list);

		//Get page number from args
		if(isset($args[1]) && is_numeric($args[1]) && $args[1] >= 1){
			$page = min($max, (int) $args[1]);
		}else{
			$page = 1;
		}

		//Send list of registered chunk
		$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.list.head", [$worldName, (string) $page, (string) $max]));
		if(isset($list[$page - 1])){
			foreach($list[$page - 1] as $chunkHash){
				Level::getXZ($chunkHash, $chunkX, $chunkZ);
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader.list.item", [(string) $chunkX, (string) $chunkZ]));
			}
		}
		return true;
	}
}
