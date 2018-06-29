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
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0.0
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\chunkloader\command;

use kim\present\chunkloader\ChunkLoader;
use pocketmine\{
	Player, Server
};
use pocketmine\command\CommandSender;
use pocketmine\level\Level;

class ListSubcommand extends Subcommand{
	/**
	 * DisableSubcommand constructor.
	 *
	 * @param ChunkLoader $plugin
	 */
	public function __construct(ChunkLoader $plugin){
		parent::__construct($plugin, "list");
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function execute(CommandSender $sender, array $args = []) : void{
		//Get world name from args or player
		$worldName = null;
		if(isset($args[0])){
			$level = Server::getInstance()->getLevelByName($args[0]);
			if($level === null){
				$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.failure.invalidWorld', [$args[0]]));
				return;
			}
			$worldName = $args[0];
		}elseif($sender instanceof Player){
			$worldName = $sender->getLevel()->getFolderName();
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.usage'));
			return;
		}
		$list = $this->plugin->getChunkDataMap($worldName)->getAll();
		$max = ceil(count($list) / 5);
		$page = 0;
		if(isset($args[1]) && is_numeric($args[1])){
			$page = ((int) $args[1]) - 1;
			if($page < 0){
				$page = 0;
			}elseif($page > $max){
				$page = $max;
			}
		}
		$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.head', [$worldName, (string) ($page + 1), (string) $max]));
		for($i = $page * 5, $count = count($list), $loopMax = ($page + 1) * 5; $i < $count && $i < $loopMax; $i++){
			Level::getXZ($list[$i], $chunkX, $chunkZ);
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.item', [(string) $chunkX, (string) $chunkZ]));
		}
	}
}
