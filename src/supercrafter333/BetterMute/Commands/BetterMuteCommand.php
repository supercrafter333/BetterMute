<?php

namespace supercrafter333\BetterMute\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use supercrafter333\BetterMute\BetterMute;

/**
 *
 */
abstract class BetterMuteCommand extends Command implements PluginOwned
{

    /**
     * @param string $name
     * @param Translatable|string $description
     * @param string|null $permission
     * @param Translatable|string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, Translatable|string $description = "", string|null $permission = null, Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        if ($permission !== null) $this->setPermission($permission);
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    abstract public function execute(CommandSender $s, string $commandLabel, array $args): void;

    /**
     * @return BetterMute
     */
    public function getOwningPlugin(): BetterMute
    {
        return BetterMute::getInstance();
    }

    /**
     * @param CommandSender|Player $sender
     * @param bool $checkIsPlayer
     * @return bool
     */
    public function canUse(CommandSender|Player $sender, bool $checkIsPlayer = false): bool
    {
        if ($this->getPermission() !== null && !$this->testPermission($sender, $this->getPermission())) return false;

        if ($checkIsPlayer && !$sender instanceof Player) {
            $sender->sendMessage("Only In-Game!");
            return false;
        }

        return true;
    }
}