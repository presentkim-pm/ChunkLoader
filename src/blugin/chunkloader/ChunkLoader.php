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

use blugin\chunkloader\command\ListSubcommand;
use blugin\chunkloader\command\RegisterSubcommand;
use blugin\chunkloader\command\UnregisterSubcommand;
use blugin\chunkloader\data\ChunkDataMap;
use blugin\lib\command\SubcommandTrait;
use blugin\lib\lang\LanguageHolder;
use blugin\lib\lang\LanguageTrait;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\ChunkLoader as PMChunkLoader;
use pocketmine\world\World;

class ChunkLoader extends PluginBase implements LanguageHolder, PMChunkLoader{
    use SingletonTrait, LanguageTrait, SubcommandTrait;

    /** @var ChunkDataMap[] */
    private $dataMaps = [];

    /**
     * Called when the plugin is loaded, before calling onEnable()
     */
    public function onLoad() : void{
        self::setInstance($this);

        $this->loadLanguage($this->getConfig()->getNested("settings.language"));

        //Create main command
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
        $command->registerSubcommand(new ListSubcommand($command));
        $this->getServer()->getCommandMap()->register($this->getName(), $command);

        //Load registered chunk map
        if(file_exists($file = "{$this->getDataFolder()}data.dat")){
            $contents = @file_get_contents($file);
            if($contents === false)
                throw new \RuntimeException("Failed to read player data file \"$file\" (permission denied?)");

            $decompressed = @zlib_decode($contents);
            if($decompressed === false){
                throw new \RuntimeException("Failed to decompress raw data for ChunkLoader");
            }

            try{
                $tag = (new BigEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
            }catch(NbtDataException $e){
                throw new \RuntimeException("Failed to decode NBT data for ChunkLoader");
            }

            if($tag instanceof CompoundTag){
                /**
                 * @var string  $worldName
                 * @var ListTag $mapTag
                 */
                foreach($tag as $worldName => $mapTag){
                    $this->setChunkDataMap(ChunkDataMap::nbtDeserialize($worldName, $mapTag));
                }
            }else{
                throw new \RuntimeException("The file is not in the NBT-CompoundTag format : $file");
            }
        }
    }

    /**
     * Called when the plugin is disabled
     * Use this to free open things and finish actions
     */
    public function onDisable() : void{
        //Unregister main command with subcommands
        $this->getServer()->getCommandMap()->unregister($this->getMainCommand());

        //Save registered chunk map
        $tag = CompoundTag::create();
        foreach($this->dataMaps as $worldName => $chunkDataMap){
            if(!empty($chunkDataMap->getAll())){
                $tag->setTag($chunkDataMap->getWorldName(), $chunkDataMap->nbtSerialize());
            }
        }
        if(!empty($value)){
            $nbt = new BigEndianNbtSerializer();
            try{
                file_put_contents("{$this->getDataFolder()}data.dat", zlib_encode($nbt->write(new TreeRoot($tag)), ZLIB_ENCODING_GZIP));
            }catch(\ErrorException $e){
                $this->getLogger()->critical($this->getServer()->getLanguage()->translateString("pocketmine.data.saveError", [
                    "ChunkLoader-data",
                    $e->getMessage()
                ]));
                $this->getLogger()->logException($e);
            }
        }
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
        $worldName = $chunkDataMap->getWorldName();
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if($world === null){
            $this->dataMaps[$worldName] = $chunkDataMap;
        }else{
            //Unregister chunk loaders from old chunk data map
            if(isset($this->dataMaps[$worldName])){
                foreach($this->dataMaps[$worldName]->getAll() as $key => $chunkHash){
                    World::getXZ($chunkHash, $chunkX, $chunkZ);
                    $world->unregisterChunkLoader($this, $chunkX, $chunkZ);
                }
            }
            //Register chunk loaders from new chunk data map
            $this->dataMaps[$worldName] = $chunkDataMap;
            foreach($this->dataMaps[$worldName]->getAll() as $key => $chunkHash){
                World::getXZ($chunkHash, $chunkX, $chunkZ);
                $world->registerChunkLoader($this, $chunkX, $chunkZ);
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
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if($world !== null){
            $world->registerChunkLoader($this, $chunkX, $chunkZ);
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
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if($world !== null){
            $world->unregisterChunkLoader($this, $chunkX, $chunkZ);
        }
        return $this->getChunkDataMap($worldName)->removeChunk($chunkX, $chunkZ);
    }

    /** @return float */
    public function getX(){
        return 0;
    }

    /** @return float */
    public function getZ(){
        return 0;
    }
}