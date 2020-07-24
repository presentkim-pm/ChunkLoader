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

use pocketmine\level\format\Chunk;
use pocketmine\level\Level as World;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

trait ChunkLoaderTrait{
    /** @var int */
    private static $loaderId;

    /** @return float */
    public function getX(){
        return 0;
    }

    /** @return float */
    public function getZ(){
        return 0;
    }

    /** @return bool */
    public function isLoaderActive() : bool{
        return $this->isEnabled();
    }

    /** @param Chunk $chunk */
    public function onChunkChanged(Chunk $chunk) : void{
    }

    /** @param Chunk $chunk */
    public function onChunkLoaded(Chunk $chunk) : void{
    }

    /** @param Chunk $chunk */
    public function onChunkUnloaded(Chunk $chunk) : void{
    }

    /**@param Chunk $chunk */
    public function onChunkPopulated(Chunk $chunk) : void{
    }

    /** @param Vector3 $block */
    public function onBlockChanged(Vector3 $block) : void{
    }

    /** @return int */
    public function getLoaderId() : int{
        if(self::$loaderId === null)
            self::$loaderId = World::generateChunkLoaderId($this);
        return self::$loaderId;
    }

    /**
     * @return Position
     */
    public function getPosition() : Position{
        return new Position(0, 0, 0, $this->getLevel());
    }

    /**
     * @return World
     */
    public function getLevel() : World{
        return $this->getServer()->getDefaultLevel();
    }
}