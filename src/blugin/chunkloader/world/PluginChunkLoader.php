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

namespace blugin\chunkloader\world;

use blugin\chunkloader\ChunkLoader;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\world\ChunkLoader as PMChunkLoader;
use pocketmine\world\format\Chunk;

class PluginChunkLoader extends Vector3 implements PMChunkLoader{
    /** @var ChunkLoader */
    protected $plugin;

    /**
     * PluginChunkLoader constructor.
     *
     * @param ChunkLoader $plugin
     */
    public function __construct(ChunkLoader $plugin){
        parent::__construct(0, 0, 0);
        $this->plugin = $plugin;
    }

    /**
     * Returns if the chunk loader is currently active
     *
     * @return bool
     */
    public function isLoaderActive() : bool{
        return $this->plugin->isEnabled();
    }

    /**
     * This method will be called when a Chunk is replaced by a new one
     *
     * @param Chunk $chunk
     */
    public function onChunkChanged(Chunk $chunk) : void{
    }

    /**
     * This method will be called when a registered chunk is loaded
     *
     * @param Chunk $chunk
     */
    public function onChunkLoaded(Chunk $chunk) : void{
    }

    /**
     * This method will be called when a registered chunk is unloaded
     *
     * @param Chunk $chunk
     */
    public function onChunkUnloaded(Chunk $chunk) : void{
    }

    /**
     * This method will be called when a registered chunk is populated
     * Usually it'll be sent with another call to onChunkChanged()
     *
     * @param Chunk $chunk
     */
    public function onChunkPopulated(Chunk $chunk) : void{
    }

    /**
     * This method will be called when a block changes in a registered chunk
     *
     * @param Block|Vector3 $block
     */
    public function onBlockChanged(Vector3 $block) : void{
    }
}