<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\chunkloader;

use blugin\chunkloader\command\ClearSubcommand;
use blugin\chunkloader\command\ListSubcommand;
use blugin\chunkloader\command\RegisterSubcommand;
use blugin\chunkloader\command\UnregisterSubcommand;
use blugin\lib\command\SubcommandTrait;
use blugin\lib\lang\LanguageHolder;
use blugin\lib\lang\LanguageTrait;
use pocketmine\event\level\LevelInitEvent as WorldInitEvent;
use pocketmine\event\level\LevelLoadEvent as WorldLoadEvent;
use pocketmine\event\level\LevelUnloadEvent as WorldUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\level\ChunkLoader as PMChunkLoader;
use pocketmine\level\Level as World;
use pocketmine\plugin\PluginBase;

class ChunkLoader extends PluginBase implements LanguageHolder, PMChunkLoader, Listener{
    use LanguageTrait, SubcommandTrait, ChunkLoaderTrait;

    /** @var self|null */
    private static $instance = null;

    /** @var int[][] world name => chunk hash[] */
    private $loadList = [];

    /**
     * Called when the plugin is loaded, before calling onEnable()
     */
    public function onLoad() : void{
        self::$instance = $this;

        $this->loadLanguage($this->getConfig()->getNested("settings.language"));
        $this->getMainCommand();
    }

    /**
     * Called when the plugin is enabled
     */
    public function onEnable() : void{
        //Register main command with subcommands
        $command = $this->getMainCommand();
        $command->registerSubcommand(new RegisterSubcommand($command));
        $command->registerSubcommand(new UnregisterSubcommand($command));
        $command->registerSubcommand(new ClearSubcommand($command));
        $command->registerSubcommand(new ListSubcommand($command));
        $this->recalculatePermissions();
        $this->getServer()->getCommandMap()->register($this->getName(), $command);

        //Load chunk loader data of all world
        foreach($this->getServer()->getLevels() as $key => $world){
            $this->loadWorld($world);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * Called when the plugin is disabled
     * Use this to free open things and finish actions
     */
    public function onDisable() : void{
        //Unregister main command with subcommands
        $this->getServer()->getCommandMap()->unregister($this->getMainCommand());

        //Save chunk loader data of all world
        foreach($this->getServer()->getLevels() as $key => $world){
            $this->unloadWorld($world);
        }
    }

    /** @param WorldLoadEvent $event */
    public function onWorldLoadEvent(WorldLoadEvent $event) : void{
        $this->loadWorld($event->getLevel());
    }

    /** @param WorldInitEvent $event */
    public function onWorldInitEvent(WorldInitEvent $event) : void{
        $this->loadWorld($event->getLevel());
    }

    /** @param WorldUnloadEvent $event */
    public function onWorldUnloadEvent(WorldUnloadEvent $event) : void{
        $this->unloadWorld($event->getLevel());
    }

    /** @param World $world */
    public function loadWorld(World $world) : void{
        $worldName = $world->getFolderName();
        $listPath = "{$this->getServer()->getDataPath()}/worlds/$worldName/chunkloads.json";
        if(!file_exists($listPath)){
            $this->loadList[$worldName] = [];
            return;
        }

        $content = file_get_contents($listPath);
        if($content === false)
            throw new \RuntimeException("Unable to load $worldName's chunkloads.json file");

        $list = json_decode($content, true);
        if(!is_array($list))
            throw new \RuntimeException("Invalid data in $worldName's chunkloads.json file. Must be int array");

        $list = array_values($list);
        foreach($list as $key => $chunkHash){
            if(!is_int($chunkHash))
                throw new \RuntimeException("Invalid data in $worldName's chunkloads.json file. Must be int array");

            World::getXZ($chunkHash, $chunkX, $chunkZ);
            $this->registerChunk($world, $chunkX, $chunkZ);
        }
    }

    /** @param World $world */
    public function unloadWorld(World $world) : void{
        $worldName = $world->getFolderName();
        if(!isset($this->loadList[$worldName]))
            return;

        $listPath = "{$this->getServer()->getDataPath()}/worlds/$worldName/chunkloads.json";
        file_put_contents($listPath, json_encode($this->loadList[$worldName], JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING));
        unset($this->loadList[$worldName]);
    }

    /**
     * @param World $world
     *
     * @return int[]|null return chunk hash array, if empty return null.
     */
    public function getByWorld(World $world) : ?array{
        return $this->loadList[$world->getFolderName()] ?? null;
    }

    /**
     * @param World $world
     * @param int   $chunkX
     * @param int   $chunkZ
     *
     * @return bool true if the chunk registered successfully, false if not.
     */
    public function registerChunk(World $world, int $chunkX, int $chunkZ) : bool{
        $worldName = $world->getFolderName();
        if(!isset($this->loadList[$worldName]))
            $this->loadList[$worldName] = [];

        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        if(in_array($chunkHash, $this->loadList[$worldName]))
            return false;

        $world->registerChunkLoader($this, $chunkX, $chunkZ);
        $this->loadList[$worldName][] = $chunkHash;
        return true;
    }

    /**
     * @param World $world
     * @param int   $chunkX
     * @param int   $chunkZ
     *
     * @return bool true if the chunk unregistered successfully, false if not.
     */
    public function unregisterChunk(World $world, int $chunkX, int $chunkZ) : bool{
        $worldName = $world->getFolderName();
        if(!isset($this->loadList[$worldName]))
            $this->loadList[$worldName] = [];

        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        $key = array_search($chunkHash, $this->loadList[$worldName]);
        if($key === false)
            return false;

        $world->unregisterChunkLoader($this, $chunkX, $chunkZ);
        unset($this->loadList[$worldName][$key]);
        return true;
    }

    /** @return ChunkLoader|null */
    public static function getInstance() : ?self{
        return self::$instance;
    }
}