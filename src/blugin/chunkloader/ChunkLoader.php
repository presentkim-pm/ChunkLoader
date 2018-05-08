<?php

declare(strict_types=1);

namespace blugin\chunkloader;

use pocketmine\plugin\PluginBase;
use blugin\chunkloader\lang\PluginLang;
use blugin\chunkloader\level\PluginChunkLoader;

class ChunkLoader extends PluginBase{

    /** @var ChunkLoader */
    private static $instance = null;

    /** @return ChunkLoader */
    public static function getInstance() : ChunkLoader{
        return self::$instance;
    }

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
    }

    public function onDisable() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }
        $this->saveConfig();
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