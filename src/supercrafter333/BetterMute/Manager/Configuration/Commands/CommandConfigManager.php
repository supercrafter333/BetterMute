<?php

namespace supercrafter333\BetterMute\Manager\Configuration\Commands;

use pocketmine\utils\Config;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;

/**
 *
 */
class CommandConfigManager
{

    public static function startup(): void
    {
        BetterMute::getInstance()->saveResource("commands.yml");
        ConfigManager::updateConfig(new Config(BetterMute::getInstance()->getDataFolder() . "commands.yml", Config::YAML), BetterMute::getInstance()->getDataFolder(), "commands.yml");
    }

    /**
     * @return Config
     */
    public static function getConfig(): Config
    {
        return new Config(BetterMute::getInstance()->getDataFolder() . "commands.yml");
    }

    /**
     * @param string $cmdName
     * @return array|null
     */
    public static function getCommandInformations(string $cmdName): array|null
    {
        $cfg = self::getConfig();
        $cmdName = mb_strtolower($cmdName);

        if (!$cfg->exists($cmdName . "-name")) return null;

        return [
            "name" => $cfg->get($cmdName . "-name"),
            "desc" => $cfg->get($cmdName . "-description"),
            "usage" => $cfg->get($cmdName . "-usage"),
            "aliases" => $cfg->get($cmdName . "-aliases", [])
        ];
    }
}