<?php

declare(strict_types=1);

namespace kim\present\chunkloader;

use kim\present\chunkloader\command\{
	ListSubcommand, RegisterSubcommand, Subcommand, UnregisterSubcommand
};
use kim\present\chunkloader\data\ChunkDataMap;
use kim\present\chunkloader\lang\PluginLang;
use kim\present\chunkloader\level\PluginChunkLoader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\level\Level;
use pocketmine\nbt\{
	BigEndianNBTStream, NBT
};
use pocketmine\nbt\tag\ListTag;
use pocketmine\permission\Permission;
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

	/**
	 * @var ChunkDataMap[]
	 */
	private $dataMaps = [];

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

		//Load registered chunk map
		if(file_exists($file = "{$this->getDataFolder()}data.dat")){
			$namedTag = (new BigEndianNBTStream())->readCompressed(file_get_contents($file));
			if($namedTag instanceof ListTag){
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
		$permissions = $this->getServer()->getPluginManager()->getPermissions();
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

	public function onDisable() : void{
		//Save registered chunk map
		$value = [];
		foreach($this->dataMaps as $worldName => $chunkDataMap){
			if(!empty($chunkDataMap->getAll())){
				$value[] = $chunkDataMap->nbtSerialize();
			}
		}
		if(!empty($value)){
			$namedTag = new ListTag("ChunkLoader", $value, NBT::TAG_List);
			file_put_contents("{$this->getDataFolder()}data.dat", (new BigEndianNBTStream())->writeCompressed($namedTag));
		}
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