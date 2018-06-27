<?php

declare(strict_types=1);

namespace kim\present\chunkloader;

use kim\present\chunkloader\command\{
	ListSubcommand, RegisterSubcommand, Subcommand, UnregisterSubcommand
};
use kim\present\chunkloader\lang\PluginLang;
use kim\present\chunkloader\level\PluginChunkLoader;
use pocketmine\command\PluginCommand;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;

class ChunkLoader extends PluginBase{
	public const REGISTER = 0;
	public const UNREGISTER = 1;
	public const LIST = 2;
	/**
	 * @var ChunkLoader
	 */
	private static $instance;

	/**
	 * @return ChunkLoader
	 */
	public static function getInstance() : ChunkLoader{
		return self::$instance;
	}

	/**
	 * @var PluginLang
	 */
	private $language;

	/**
	 * @var PluginCommand
	 */
	private $command;

	/**
	 * @var Subcommand[]
	 */
	private $subcommands;

	/**
	 * @var PluginChunkLoader
	 */
	private $chunkLoader;

	public function onLoad() : void{
		self::$instance = $this;
		$this->chunkLoader = new PluginChunkLoader($this);
	}

	public function onEnable() : void{
		//Save default resources
		$this->saveResource("lang/eng/lang.ini", false);
		$this->saveResource("lang/kor/lang.ini", false);
		$this->saveResource("lang/language.list", false);

		//Load config file
		$this->saveDefaultConfig();
		$this->reloadConfig();

		//Load language file
		$config = $this->getConfig();
		$this->language = new PluginLang($this, $config->getNested("settings.language"));
		$this->getLogger()->info($this->language->translateString("language.selected", [$this->language->getName(), $this->language->getLang()]));

		//Register chunk loaders
		/** @var string[][] $configData */
		$configData = $this->getConfig()->getAll();
		foreach($configData as $worldName => $chunks){
			$level = $this->getServer()->getLevelByName($worldName);
			if($level === null){
				$this->getLogger()->error("{$worldName} is invalid world name");
			}else{
				foreach($chunks as $key => $chunkHash){
					Level::getXZ((int) $chunkHash, $chunkX, $chunkZ);
					$level->registerChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
				}
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
	 * @param int   $chunkX
	 * @param int   $chunkZ
	 * @param Level $level
	 *
	 * @return bool true if registered chunk
	 */
	public function registerChunk(int $chunkX, int $chunkZ, Level $level) : bool{
		$config = $this->getConfig();

		$chunkHash = (string) Level::chunkHash($chunkX, $chunkZ);
		/** @var string[] $chunks */
		$chunks = $config->get($worldName = $level->getFolderName(), []);
		if(in_array($chunkHash, $chunks)){
			return false;
		}else{
			$chunks[] = $chunkHash;
			$config->set($worldName, array_values($chunks));

			$level->registerChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
			return true;
		}
	}

	/**
	 * @param int   $chunkX
	 * @param int   $chunkZ
	 * @param Level $level
	 *
	 * @return bool true if unregistered chunk
	 */
	public function unregisterChunk(int $chunkX, int $chunkZ, Level $level) : bool{
		$config = $this->getConfig();

		$chunkHash = (string) Level::chunkHash($chunkX, $chunkZ);
		/** @var string[] $chunks */
		$chunks = $config->get($worldName = $level->getFolderName());
		if($chunks === false){
			return false;
		}
		if(in_array($chunkHash, $chunks)){
			unset($chunks[array_search($chunkHash, $chunks)]);
			if(count($chunks) === 0){
				$config->remove($worldName);
			}else{
				$config->set($worldName, array_values($chunks));
			}

			$level->unregisterChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return PluginLang
	 */
	public function getLanguage() : PluginLang{
		return $this->language;
	}
}