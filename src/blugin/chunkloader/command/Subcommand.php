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

namespace blugin\chunkloader\command;

use blugin\chunkloader\ChunkLoader;
use pocketmine\command\CommandSender;

abstract class Subcommand{
	public const LABEL = "";

	/** @var ChunkLoader */
	protected $plugin;

	/** @var string */
	private $name;

	/** @var string[] */
	private $aliases;

	/** @var string */
	private $permission;

	/**
	 * Subcommand constructor.
	 *
	 * @param ChunkLoader $plugin
	 */
	public function __construct(ChunkLoader $plugin){
		$this->plugin = $plugin;

		$label = $this->getLabel();
		$config = $plugin->getConfig();
		$this->name = $config->getNested("command.children.{$label}.name");
		$this->aliases = $config->getNested("command.children.{$label}.aliases");
		$this->permission = "chunkloader.cmd.{$label}";
	}


	/**
	 * @param string $label
	 *
	 * @return bool
	 */
	public function checkLabel(string $label) : bool{
		return strcasecmp($label, $this->name) === 0 || in_array($label, $this->aliases);
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function handle(CommandSender $sender, array $args = []) : void{
		if($sender->hasPermission($this->permission)){
			if(!$this->execute($sender, $args)){
				$sender->sendMessage($this->plugin->getLanguage()->translate("commands.chunkloader." . static::LABEL . ".usage"));
			}
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.generic.permission"));
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 *
	 * @return bool
	 */
	public abstract function execute(CommandSender $sender, array $args = []) : bool;

	/**
	 * @return string
	 */
	public function getLabel() : string{
		return static::LABEL;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void{
		$this->name = $name;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->aliases;
	}

	/**
	 * @param string[] $aliases
	 */
	public function setAliases(array $aliases) : void{
		$this->aliases = $aliases;
	}

	/**
	 * @return string
	 */
	public function getPermission() : string{
		return $this->permission;
	}

	/**
	 * @param string $permission
	 */
	public function setPermission(string $permission) : void{
		$this->permission = $permission;
	}
}