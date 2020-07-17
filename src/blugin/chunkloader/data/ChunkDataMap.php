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

namespace blugin\chunkloader\data;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\world\World;

class ChunkDataMap{
    /** @var string */
    protected $worldName;

    /** @var bool[] key is (int) chunk hash (all value is `true`) */
    protected $chunkHashs = [];

    /**
     * ChunkDataMap constructor.
     *
     * @param string $worldName
     * @param int[]  $chunkHashs = []
     */
    public function __construct(string $worldName, array $chunkHashs = []){
        $this->worldName = $worldName;
        $this->setAll($chunkHashs);
    }

    /**
     * @return string
     */
    public function getWorldName() : string{
        return $this->worldName;
    }

    /**
     * @param string $worldName
     */
    public function setWorldName(string $worldName) : void{
        $this->worldName = $worldName;
    }

    /**
     * @return int[] value is chunk hash (World::chunkHash($chunkX, $chunkZ))
     */
    public function getAll() : array{
        return array_keys($this->chunkHashs);
    }

    /**
     * @param int[] $chunkHashs
     */
    public function setAll(array $chunkHashs) : void{
        $this->chunkHashs = [];
        foreach($chunkHashs as $key => $chunkHash){
            World::getXZ($chunkHash, $chunkX, $chunkZ);
            $this->addChunk($chunkX, $chunkZ);
        }
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     *
     * @return bool true if the chunk hash added successfully, false if not.
     */
    public function addChunk(int $chunkX, int $chunkZ) : bool{
        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        if(!isset($this->chunkHashs[$chunkHash])){
            $this->chunkHashs[$chunkHash] = true;
            return true;
        }
        return false;
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     *
     * @return bool true if the chunk hash removed successfully, false if not.
     */
    public function removeChunk(int $chunkX, int $chunkZ) : bool{
        $chunkHash = World::chunkHash($chunkX, $chunkZ);
        if(isset($this->chunkHashs[$chunkHash])){
            unset($this->chunkHashs[$chunkHash]);
            return true;
        }
        return false;
    }

    /**
     * @param int $chunkX
     * @param int $chunkZ
     *
     * @return bool true if the chunk exists in array, false if not.
     */
    public function exists(int $chunkX, int $chunkZ) : bool{
        return isset($this->chunkHashs[World::chunkHash($chunkX, $chunkZ)]);
    }

    /** @return ListTag */
    public function nbtSerialize() : ListTag{
        $value = [];
        foreach($this->chunkHashs as $chunkHash => $alwaysTrue){
            World::getXZ($chunkHash, $chunkX, $chunkZ);
            $value[] = new ListTag([
                new IntTag($chunkX),
                new IntTag($chunkZ)
            ], NBT::TAG_Int);
        }
        return new ListTag($value, NBT::TAG_List);
    }

    /**
     * @param string  $name
     * @param ListTag $tag
     *
     * @return ChunkDataMap
     */
    public static function nbtDeserialize(string $name, ListTag $tag) : ChunkDataMap{
        $chunkHashs = [];
        /** @var ListTag $chunkHashTag */
        foreach($tag as $key => $chunkHashTag){
            $chunkHashs[] = World::chunkHash(...$chunkHashTag->getAllValues());
        }
        return new ChunkDataMap($name, $chunkHashs);
    }
}