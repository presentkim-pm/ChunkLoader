<?php

declare(strict_types=1);

namespace blugin\chunkloader;

use pocketmine\plugin\PluginBase;

class ChunkLoader extends PluginBase{

    /** @var ChunkLoader */
    private static $instance = null;

    /** @return ChunkLoader */
    public static function getInstance() : ChunkLoader{
        return self::$instance;
    }

    public function onLoad() : void{
        self::$instance = $this;
    }

    public function onEnable() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }
        $this->reloadConfig();
    }

    public function onDisable() : void{
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }
        $this->saveConfig();
    }
}