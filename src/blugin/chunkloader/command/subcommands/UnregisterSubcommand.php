<?php

declare(strict_types=1);

namespace blugin\chunkloader\command\subcommands;

use pocketmine\{
  Server, Player
};
use pocketmine\command\CommandSender;
use blugin\chunkloader\command\{
  PoolCommand, SubCommand
};

class UnregisterSubcommand extends SubCommand{

    public function __construct(PoolCommand $owner){
        parent::__construct($owner, 'unregister');
    }

    /**
     * @param CommandSender $sender
     * @param String[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, array $args) : bool{
        if (isset($args[0])) {
            if (!is_numeric($args[0])) {
                $sender->sendMessage($this->plugin->getLanguage()->translate('commands.generic.num.notNumber', [$args[0]]));
                return true;
            } else {
                $chunkX = (int) $args[0];
            }
        } elseif ($sender instanceof Player) {
            $chunkX = $sender->x >> 4;
        } else {
            return false;
        }
        if (isset($args[1])) {
            if (!is_numeric($args[1])) {
                $sender->sendMessage($this->plugin->getLanguage()->translate('commands.generic.num.notNumber', [$args[1]]));
                return true;
            } else {
                $chunkZ = (int) $args[1];
            }
        } elseif ($sender instanceof Player) {
            $chunkZ = $sender->x >> 4;
        } else {
            return false;
        }
        if (isset($args[2])) {
            $level = Server::getInstance()->getLevelByName($args[2]);
            if ($level === null) {
                $sender->sendMessage($this->translate('failure.invalidWorld', $args[2]));
                return true;
            }
        } elseif ($sender instanceof Player) {
            $level = $sender->getLevel();
        } else {
            return false;
        }
        if (!$this->plugin->unregisterChunk($chunkX, $chunkZ, $level)) {
            $sender->sendMessage($this->translate('failure.notRegistered', (string) $chunkX, (string) $chunkZ, $level->getFolderName()));
        } else {
            $sender->sendMessage($this->translate('success', (string) $chunkX, (string) $chunkZ, $level->getFolderName()));
        }
        return true;
    }
}