<?php

declare(strict_types=1);

namespace kim\present\chunkloader\command;

use kim\present\chunkloader\ChunkLoader;
use pocketmine\{
	Player, Server
};
use pocketmine\command\CommandSender;

class UnregisterSubcommand extends Subcommand{
	/**
	 * DisableSubcommand constructor.
	 *
	 * @param ChunkLoader $plugin
	 */
	public function __construct(ChunkLoader $plugin){
		parent::__construct($plugin, "unregister");
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function execute(CommandSender $sender, array $args = []) : void{
		if(isset($args[0])){
			if(!is_numeric($args[0])){
				$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.generic.num.notNumber', [$args[0]]));
				return;
			}else{
				$chunkX = (int) $args[0];
			}
		}elseif($sender instanceof Player){
			$chunkX = $sender->x >> 4;
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.usage'));
			return;
		}
		if(isset($args[1])){
			if(!is_numeric($args[1])){
				$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.generic.num.notNumber', [$args[1]]));
				return;
			}else{
				$chunkZ = (int) $args[1];
			}
		}elseif($sender instanceof Player){
			$chunkZ = $sender->z >> 4;
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.usage'));
			return;
		}
		if(isset($args[2])){
			$level = Server::getInstance()->getLevelByName($args[2]);
			if($level === null){
				$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.failure.invalidWorld', [$args[2]]));
				return;
			}
		}elseif($sender instanceof Player){
			$level = $sender->getLevel();
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.usage'));
			return;
		}
		if(!$this->plugin->unregisterChunk($chunkX, $chunkZ, $level->getFolderName())){
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.failure.notRegistered', [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.unregister.success', [(string) $chunkX, (string) $chunkZ, $level->getFolderName()]));
		}
	}
}