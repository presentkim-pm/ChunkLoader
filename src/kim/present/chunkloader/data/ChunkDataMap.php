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
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\chunkloader\data;


use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\{
	IntTag, ListTag
};

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
	 * @return int[] value is chunk hash (Level::chunkHash($chunkX, $chunkZ))
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
			Level::getXZ($chunkHash, $chunkX, $chunkZ);
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
		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
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
		$chunkHash = Level::chunkHash($chunkX, $chunkZ);
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
		return isset($this->chunkHashs[Level::chunkHash($chunkX, $chunkZ)]);
	}

	/**
	 * @param string $tagName = null, if null it replace to world name
	 *
	 * @return ListTag
	 */
	public function nbtSerialize(string $tagName = null) : ListTag{
		$value = [];
		foreach($this->chunkHashs as $chunkHash => $alwaysTrue){
			Level::getXZ($chunkHash, $chunkX, $chunkZ);
			$value[] = new ListTag("", [
				new IntTag("", $chunkX),
				new IntTag("", $chunkZ)
			], NBT::TAG_Int);
		}
		return new ListTag($tagName ?? $this->worldName, $value, NBT::TAG_List);
	}

	/**
	 * @param ListTag $tag
	 *
	 * @return ChunkDataMap
	 */
	public static function nbtDeserialize(ListTag $tag) : ChunkDataMap{
		$chunkHashs = [];
		/** @var ListTag $chunkHashTag */
		foreach($tag as $key => $chunkHashTag){
			$chunkHashs[] = Level::chunkHash(...$chunkHashTag->getAllValues());
		}
		return new ChunkDataMap($tag->getName(), $chunkHashs);
	}
}