<?php

namespace supercrafter333\BetterMute\Manager\Configuration;

use dktapps\pmforms\BaseForm;
use pocketmine\utils\Config;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\DiscordWebhooksX\Webhook;

class ConfigManager
{

    const CURRENT_CONFIG_VERSION = "1.1.0";

    const UPDATE_CONFIG = true;
    const UPDATE_MESSAGES = true;
    const UPDATE_COMMANDS = false;


    public static function startup(): void
    {
        BetterMute::getInstance()->saveResource("config.yml");
        self::updateConfig(BetterMute::getInstance()->getConfig(), BetterMute::getInstance()->getDataFolder(), "config.yml", self::UPDATE_CONFIG);
    }

    public static function getConfig(): Config
    {
        return BetterMute::getInstance()->getConfig();
    }


    public static function getMutePath(): string
    {
        $cfg = self::getConfig();
        return mb_strtolower($cfg->get("mute-path")) === "null" ? BetterMute::getInstance()->getDataFolder() . "mutes.yml" : $cfg->get("mute-path");
    }

    public static function getRawLanguage(): string
    {
        return self::getConfig()->get("language");
    }

    public static function useForms(): bool
    {
        if ((bool)self::get("use-forms") && class_exists(BaseForm::class)) return true;
        return false;
    }

    public static function useDiscordWebhooks(): bool
    {
        if ((bool)self::get("use-discord-webhooks") && class_exists(Webhook::class)) return true;
        return false;
    }

    public static function getDiscordWebhookUrl(): string|null
    {
        if (self::useDiscordWebhooks()) return self::get("discord-webhook-url");
        return null;
    }

    public static function getConfigVersion(): string
    {
        return self::getConfig()->get("version");
    }

    public static function get(mixed $k, mixed $default = false): mixed
    {
        return self::getConfig()->get($k, $default);
    }

    public static function sendBrMsg(): bool
    {
        return self::get("send-broadcast-message", false);
    }

    public static function updateConfig(Config $config, string $pathTo, string $fullFileName, bool $update = true, string $saveName = null): void
    {
        if (!file_exists($config->getPath())) return;

        $version = $config->get("version");

        if ($version >= self::CURRENT_CONFIG_VERSION) return;

        $logger = BetterMute::getInstance()->getLogger();

        if (!$update) {
            $logger->info("Old configuration file " . $fullFileName . " detected. Configuration-update is not required.");
            return;
        }

        $logger->warning("Old configuration file " . $fullFileName . " detected. Beginning auto-update...");

        $microtime = microtime(true);

        rename($config->getPath(), $pathTo . "OLD_" . $fullFileName);

        $saveName = $saveName !== null ? $saveName : $fullFileName;

        BetterMute::getInstance()->saveResource($fullFileName);

        $logger->warning("Configuration update of " . $fullFileName . " finished in " . round(microtime(true) - $microtime, 3) . "seconds!");
    }
}