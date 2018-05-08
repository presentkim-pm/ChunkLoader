<?php

declare(strict_types=1);

namespace blugin\chunkloader;

use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use blugin\chunkloader\lang\PluginLang;
use blugin\chunkloader\command\PoolCommand;
use blugin\chunkloader\level\PluginChunkLoader;

class ChunkLoader extends PluginBase{

    /** @var ChunkLoader */
    private static $instance = null;

    /** @return ChunkLoader */
    public static function getInstance() : ChunkLoader{
        return self::$instance;
    }

    /** @var PoolCommand */
    private $command;

    /** @var PluginLang */
    private $language;

    /** @var PluginChunkLoader */
    private $chunkLoader;

    public function onLoad() : void{
        self::$instance = $this;
        $this->chunkLoader = new PluginChunkLoader($this);
    }

    public function onEnable() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }
        $this->language = new PluginLang($this);
        $this->reloadConfig();

        /** @var int[][] $configData */
        $configData = $this->getConfig()->getAll();
        foreach ($configData as $worldName => $chunks) {
            $level = $this->getServer()->getLevelByName($worldName);
            if ($level === null) {
                $this->getLogger()->error("{$worldName} is invalid world name");
            } else {
                foreach ($chunks as $key => $chunkHash) {
                    Level::getXZ($chunkHash, $chunkX, $chunkZ);
                    $level->registerChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
                }
            }
        }


        if ($this->command == null) {
            $this->command = new PoolCommand($this, 'chunkloader');
        }
        if ($this->command->isRegistered()) {
            $this->getServer()->getCommandMap()->unregister($this->command);
        }
        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), $this->command);
    }

    public function onDisable() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }
        $this->saveConfig();
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

        $chunkHash = Level::chunkHash($chunkX, $chunkZ);
        /** @var string[] $chunks */
        $chunks = $config->get($worldName = $level->getFolderName(), []);
        if (in_array($chunkHash, $chunks)) {
            return false;
        } else {
            $chunks[] = $chunkHash;
            $config->set($worldName, $chunks);

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

        $chunkHash = Level::chunkHash($chunkX, $chunkZ);
        /** @var string[] $chunkLoaders */
        $chunks = $config->get($worldName = $level->getFolderName());
        if ($chunks === false) {
            return false;
        }
        if (in_array($chunkHash, $chunks)) {
            unset($chunks[array_search($chunkHash, $chunks)]);
            if (count($chunks) === 0) {
                $config->remove($worldName);
            } else {
                $config->set($worldName, $chunks);
            }

            $level->unregisterChunkLoader($this->chunkLoader, $chunkX, $chunkZ);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $name = ''
     *
     * @return PoolCommand
     */
    public function getCommand(string $name = '') : PoolCommand{
        return $this->command;
    }

    /**
     * @return PluginLang
     */
    public function getLanguage() : PluginLang{
        return $this->language;
    }

    /**
     * @return string
     */
    public function getSourceFolder() : string{
        $pharPath = \Phar::running();
        if (empty($pharPath)) {
            return dirname(__FILE__, 4) . DIRECTORY_SEPARATOR;
        } else {
            return $pharPath . DIRECTORY_SEPARATOR;
        }
    }
}