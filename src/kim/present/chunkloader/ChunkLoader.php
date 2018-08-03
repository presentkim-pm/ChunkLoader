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

namespace kim\present\chunkloader;

use kim\present\chunkloader\command\{
	ListSubcommand, RegisterSubcommand, Subcommand, UnregisterSubcommand
};
use kim\present\chunkloader\data\ChunkDataMap;
use kim\present\chunkloader\lang\PluginLang;
use kim\present\chunkloader\level\PluginChunkLoader;
use kim\present\chunkloader\task\CheckUpdateAsyncTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\level\Level;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\{
	CompoundTag, ListTag
};
use pocketmine\permission\{
	Permission, PermissionManager
};
use pocketmine\plugin\PluginBase;

class ChunkLoader extends PluginBase{
	public const REGISTER = 0;
	public const UNREGISTER = 1;
	public const LIST = 2;

	/** @var ChunkLoader */
	private static $instance;

	/**
	 * @return ChunkLoader
	 */
	public static function getInstance() : ChunkLoader{
		return self::$instance;
	}

	/** @var PluginLang */
	private $language;

	/** @var PluginCommand */
	private $command;

	/** @var Subcommand[] */
	private $subcommands;

	/** @var PluginChunkLoader */
	private $chunkLoader;

	/** @var ChunkDataMap[] */
	private $dataMaps = [];

	/**
	 * Called when the plugin is loaded, before calling onEnable()
	 */
	public function onLoad() : void{
		self::$instance = $this;
		$this->chunkLoader = new PluginChunkLoader($this);
	}

	/**
	 * Called when the plugin is enabled
	 */
	public function onEnable() : void{
		//Save default resources
		$this->saveResource("lang/eng/lang.ini", false);
		$this->saveResource("lang/kor/lang.ini", false);
		$this->saveResource("lang/language.list", false);

		//Load config file
		$this->saveDefaultConfig();
		$this->reloadConfig();
		$config = $this->getConfig();

		//Check latest version
		if($config->getNested("settings.update-check", false)){
			$this->getServer()->getAsyncPool()->submitTask(new CheckUpdateAsyncTask());
		}

		//Load language file
		$this->language = new PluginLang($this, $config->getNested("settings.language"));
		$this->getLogger()->info($this->language->translateString("language.selected", [$this->language->getName(), $this->language->getLang()]));

		//Load registered chunk map
		if(file_exists($file = "{$this->getDataFolder()}data.dat")){
			$namedTag = (new BigEndianNBTStream())->readCompressed(file_get_contents($file));
			if($namedTag instanceof CompoundTag){
				/**
				 * @var string  $worldName
				 * @var ListTag $mapTag
				 */
				foreach($namedTag as $worldName => $mapTag){
					$this->dataMaps[$worldName] = ChunkDataMap::nbtDeserialize($mapTag);
				}
			}else{
				$this->getLogger()->error("The file is not in the NBT-CompoundTag format : $file");
			}
		}

		//Register main command
		$this->command = new PluginCommand($config->getNested("command.name"), $this);
		$this->command->setPermission("chunkloader.cmd");
		$this->command->setAliases($config->getNested("command.aliases"));
		$this->command->setUsage($this->language->translateString("commands.chunkloader.usage"));
		$this->command->setDescription($this->language->translateString("commands.chunkloader.description"));
		$this->getServer()->getCommandMap()->register($this->getName(), $this->command);

		//Register subcommands
		$this->subcommands = [
			self::REGISTER => new RegisterSubcommand($this),
			self::UNREGISTER => new UnregisterSubcommand($this),
			self::LIST => new ListSubcommand($this)
		];

		//Load permission's default value from config
		$permissions = PermissionManager::getInstance()->getPermissions();
		$defaultValue = $config->getNested("permission.main");
		if($defaultValue !== null){
			$permissions["chunkloader.cmd"]->setDefault(Permission::getByName($config->getNested("permission.main")));
		}
		foreach($this->subcommands as $key => $subcommand){
			$label = $subcommand->getLabel();
			$defaultValue = $config->getNested("permission.children.{$label}");
			if($defaultValue !== null){
				$permissions["chunkloader.cmd.{$label}"]->setDefault(Permission::getByName($defaultValue));
			}
		}
	}

	/**
	 * Called when the plugin is disabled
	 * Use this to free open things and finish actions
	 */
	public function onDisable() : void{
		//Save registered chunk map
		$value = [];
		foreach($this->dataMaps as $worldName => $chunkDataMap){
			if(!empty($chunkDataMap->getAll())){
				$value[] = $chunkDataMap->nbtSerialize();
			}
		}
		if(!empty($value)){
			$namedTag = new CompoundTag("ChunkLoader", $value);
			file_put_contents("{$this->getDataFolder()}data.dat", (new BigEndianNBTStream())->writeCompressed($namedTag));
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param string[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(empty($args[0])){
			$targetSubcommand = null;
			foreach($this->subcommands as $key => $subcommand){
				if($sender->hasPermission($subcommand->getPermission())){
					if($targetSubcommand === null){
						$targetSubcommand = $subcommand;
					}else{
						//Filter out cases where more than two command has permission
						return false;
					}
				}
			}
			$targetSubcommand->handle($sender);
		}else{
			$label = array_shift($args);
			foreach($this->subcommands as $key => $subcommand){
				if($subcommand->checkLabel($label)){
					$subcommand->handle($sender, $args);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @Override for multilingual support of the config file
	 *
	 * @return bool
	 */
	public function saveDefaultConfig() : bool{
		$resource = $this->getResource("lang/{$this->getServer()->getLanguage()->getLang()}/config.yml");
		if($resource === null){
			$resource = $this->getResource("lang/" . PluginLang::FALLBACK_LANGUAGE . "/config.yml");
		}

		if(!file_exists($configFile = $this->getDataFolder() . "config.yml")){
			$ret = stream_copy_to_stream($resource, $fp = fopen($configFile, "wb")) > 0;
			fclose($fp);
			fclose($resource);
			return $ret;
		}
		return false;
	}

	/**
	 * @param string $worldName
	 *
	 * @return ChunkDataMap
	 */
	public function getChunkDataMap(string $worldName) : ChunkDataMap{
		if(!isset($this->dataMaps[$worldName])){
			$this->dataMaps[$worldName] = new ChunkDataMap($worldName);
		}
		return $this->dataMaps[$worldName];
	}

	/**
	 * @param ChunkDataMap $chunkDataMap
	 */
	public function setChunkDataMap(ChunkDataMap $chunkDataMap) : void{
		$level = $this->getServer()->getLevelByName($worldName = $chunkDataMap->getWorldName());
		if($level === null){
			$this->dataMaps[$worldName] = $chunkDataMap;
		}else{
			//Unregister chunk loaders from old chunk data map
			if(isset($this->dataMaps[$worldName])){
				foreach($this->dataMaps[$worldName]->getAll() as $key => $chunkHash){
					Level::getXZ($chunkHash, $chunkX, $chunkZ);
					$level->unregisterChunkLoader($this->chunkLoader, $chunkX, $chunkZ);

				}
			}
			//Register chunk loaders from new chunk data map
			$this->dataMaps[$worldName] = $chunkDataMap;
			foreach($this->dataMaps[$worldName]->getAll() as $key => $chunkHash){
				Level::getXZ($chunkHash, $chunkX, $chunkZ);
				$level->registerChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
			}
		}
	}

	/**
	 * @param int    $chunkX
	 * @param int    $chunkZ
	 * @param string $worldName
	 *
	 * @return bool true if the chunk registered successfully, false if not.
	 */
	public function registerChunk(int $chunkX, int $chunkZ, string $worldName) : bool{
		$level = $this->getServer()->getLevelByName($worldName);
		if($level !== null){
			$level->registerChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
		}
		return $this->getChunkDataMap($worldName)->addChunk($chunkX, $chunkZ);
	}

	/**
	 * @param int    $chunkX
	 * @param int    $chunkZ
	 * @param string $worldName
	 *
	 * @return bool true if the chunk unregistered successfully, false if not.
	 */
	public function unregisterChunk(int $chunkX, int $chunkZ, string $worldName) : bool{
		$level = $this->getServer()->getLevelByName($worldName);
		if($level !== null){
			$level->unregisterChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
		}
		return $this->getChunkDataMap($worldName)->removeChunk($chunkX, $chunkZ);
	}

	/**
	 * @return PluginLang
	 */
	public function getLanguage() : PluginLang{
		return $this->language;
	}
}