<?php

declare(strict_types=1);

namespace blugin\chunkloader\command\subcommands;

use pocketmine\{
  Server, Player
};
use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use blugin\chunkloader\command\{
  PoolCommand, SubCommand
};

class ListSubcommand extends SubCommand{

    public function __construct(PoolCommand $owner){
        parent::__construct($owner, 'list');
    }

    /**
     * @param CommandSender $sender
     * @param String[]      $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, array $args) : bool{
        if (isset($args[0])) {
            $level = Server::getInstance()->getLevelByName($args[0]);
            if ($level === null) {
                $sender->sendMessage($this->translate('failure.invalidWorld', $args[0]));
                return true;
            }
        } elseif ($sender instanceof Player) {
            $level = $sender->getLevel();
        } else {
            return false;
        }
        /** @var string[] $list */
        $list = $this->plugin->getConfig()->get($worldName = $level->getFolderName(), []);
        $max = ceil(count($list) / 5);
        $page = 0;
        if (isset($args[1]) && is_numeric($args[1])) {
            $page = ((int) $args[1]) - 1;
            if ($page < 0) {
                $page = 0;
            } elseif ($page > $max) {
                $page = $max;
            }
        }
        $sender->sendMessage($this->translate('head', $worldName, (string) ($page + 1), (string) $max));
        for ($i = $page * 5, $count = count($list), $loopMax = ($page + 1) * 5; $i < $count && $i < $loopMax; $i++) {
            Level::getXZ((int) $list[$i], $chunkX, $chunkZ);
            $sender->sendMessage($this->translate('item', (string) $chunkX, (string) $chunkZ));
        }
        return true;
    }
}