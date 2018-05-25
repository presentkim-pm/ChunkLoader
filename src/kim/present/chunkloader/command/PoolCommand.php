<?php

declare(strict_types=1);

namespace kim\present\chunkloader\command;


use pocketmine\command\{
  Command, PluginCommand, CommandExecutor, CommandSender, ConsoleCommandSender
};
use kim\present\chunkloader\ChunkLoader;

class PoolCommand extends PluginCommand implements CommandExecutor{

    /** @var SubCommand[] */
    protected $subCommands = [];

    /** @var string */
    public $uname;

    /**
     * @param ChunkLoader $owner
     * @param string       $name
     * @param SubCommand[] $subCommands
     */
    public function __construct(ChunkLoader $owner, string $name, SubCommand ...$subCommands){
        parent::__construct($owner->getLanguage()->translate("commands.{$name}"), $owner);
        $this->setExecutor($this);

        $this->uname = $name;
        $this->setPermission("{$name}.cmd");

        $this->description = $owner->getLanguage()->translate("commands.{$this->uname}.description");
        $this->usageMessage = $this->getUsage(new ConsoleCommandSender());
        $aliases = $owner->getLanguage()->getArray("commands.{$this->uname}.aliases");
        if (is_array($aliases)) {
            $this->setAliases($aliases);
        }

        $this->subCommands = $subCommands;
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
        if (isset($args[0])) {
            $label = array_shift($args);
            foreach ($this->subCommands as $key => $value) {
                if ($value->checkLabel($label)) {
                    $value->execute($sender, $args);
                    return true;
                }
            }
        }
        $sender->sendMessage($this->getPlugin()->getServer()->getLanguage()->translateString("commands.generic.usage", [$this->getUsage($sender)]));
        return true;
    }

    /**
     * @param CommandSender|null $sender
     *
     * @return string
     */
    public function getUsage(CommandSender $sender = null) : string{
        if ($sender === null) {
            return $this->usageMessage;
        } else {
            $subCommands = [];
            foreach ($this->subCommands as $key => $subCommand) {
                if ($subCommand->checkPermission($sender)) {
                    $subCommands[] = $subCommand->getLabel();
                }
            }
            return $this->getPlugin()->getLanguage()->translate("commands.{$this->uname}.usage", [implode($this->getPlugin()->getLanguage()->translate("commands.{$this->uname}.usage.separator"), $subCommands)]);
        }
    }

    /** @return SubCommand[] */
    public function getSubCommands() : array{
        return $this->subCommands;
    }

    /** @param SubCommand[] $subCommands */
    public function setSubCommands(SubCommand ...$subCommands) : void{
        $this->subCommands = $subCommands;
    }

    /** @param SubCommand::class $subCommandClass */
    public function createSubCommand($subCommandClass) : void{
        $this->subCommands[] = new $subCommandClass($this);
    }
}