<?php

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
		if(isset($args[0])){
			$level = Server::getInstance()->getLevelByName($args[0]);
			if($level === null){
				$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.failure.invalidWorld', [$args[0]]));
				return;
			}
		}elseif($sender instanceof Player){
			$level = $sender->getLevel();
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.usage'));
			return;
		}
		/** @var string[] $list */
		$list = $this->plugin->getConfig()->get($worldName = $level->getFolderName(), []);
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
			Level::getXZ((int) $list[$i], $chunkX, $chunkZ);
			$sender->sendMessage($this->plugin->getLanguage()->translateString('commands.chunkloader.list.item', [(string) $chunkX, (string) $chunkZ]));
		}
	}
}
