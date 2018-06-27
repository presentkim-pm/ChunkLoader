<?php

declare(strict_types=1);

namespace kim\present\chunkloader\command;

use kim\present\chunkloader\ChunkLoader;
use pocketmine\command\CommandSender;

abstract class Subcommand{
	/**
	 * @var ChunkLoader
	 */
	protected $plugin;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string[]
	 */
	private $aliases;

	/**
	 * @var string
	 */
	private $permission;

	/**
	 * Subcommand constructor.
	 *
	 * @param ChunkLoader $plugin
	 * @param string      $label
	 */
	public function __construct(ChunkLoader $plugin, string $label){
		$this->plugin = $plugin;
		$this->label = $label;

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
			$this->execute($sender, $args);
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString("commands.generic.permission"));
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public abstract function execute(CommandSender $sender, array $args = []) : void;

	/**
	 * @return string
	 */
	public function getLabel() : string{
		return $this->label;
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