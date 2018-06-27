<?php

declare(strict_types=1);

namespace kim\present\chunkloader\command;

use kim\present\chunkloader\ChunkLoader;
use pocketmine\command\{
	Command, CommandExecutor, CommandSender, ConsoleCommandSender, PluginCommand
};

class PoolCommand extends PluginCommand implements CommandExecutor{
	/**
	 * @var string
	 */
	public $uname;

	/**
	 * @var SubCommand[]
	 */
	protected $subCommands = [];

	/**
	 * @param ChunkLoader  $owner
	 * @param string       $name
	 * @param SubCommand[] $subCommands
	 */
	public function __construct(ChunkLoader $owner, string $name, SubCommand ...$subCommands){
		parent::__construct($owner->getConfig()->getNested("command.name"), $owner);
		$this->setExecutor($this);

		$this->uname = $name;
		$this->setPermission("{$name}.cmd");

		$this->description = $owner->getLanguage()->translateString("commands.{$this->uname}.description");
		$this->usageMessage = $this->getUsage(new ConsoleCommandSender());
		$this->setAliases($owner->getConfig()->getNested("command.aliases"));

		$this->subCommands = $subCommands;
	}

	/**
	 * @param CommandSender|null $sender
	 *
	 * @return string
	 */
	public function getUsage(CommandSender $sender = null) : string{
		if($sender === null){
			return $this->usageMessage;
		}else{
			$subCommands = [];
			foreach($this->subCommands as $key => $subCommand){
				if($subCommand->checkPermission($sender)){
					$subCommands[] = $subCommand->getLabel();
				}
			}
			/** @var ChunkLoader $plugin */
			$plugin = $this->getPlugin();
			$lang = $plugin->getLanguage();
			return $lang->translateString("commands.{$this->uname}.usage", [implode($lang->translateString("commands.{$this->uname}.usage.separator"), $subCommands)]);
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param string[]      $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if(isset($args[0])){
			$label = array_shift($args);
			foreach($this->subCommands as $key => $value){
				if($value->checkLabel($label)){
					$value->execute($sender, $args);
					return true;
				}
			}
		}
		$sender->sendMessage($this->getPlugin()->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->getUsage($sender)]));
		return true;
	}

	/**
	 * @return SubCommand[]
	 */
	public function getSubCommands() : array{
		return $this->subCommands;
	}

	/**
	 * @param SubCommand[] $subCommands
	 */
	public function setSubCommands(SubCommand ...$subCommands) : void{
		$this->subCommands = $subCommands;
	}

	/**
	 * @param SubCommand::class $subCommandClass
	 */
	public function createSubCommand($subCommandClass) : void{
		$this->subCommands[] = new $subCommandClass($this);
	}
}