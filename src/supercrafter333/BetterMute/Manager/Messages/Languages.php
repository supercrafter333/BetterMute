<?php

namespace supercrafter333\BetterMute\Manager\Messages;

use pocketmine\utils\Config;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;

class Languages
{

    public const LANG_en_BE = "en_BE"; # Britisch English
    //TODO: public const LANG_en_AE = "en_AE"; # American English
    public const LANG_ger_DE = "ger_DE";
    public const LANG_CUSTOM = "messages";

    /**
     * List of languages. (As array)
     * @var array|string[]
     */
    public static array $languages = [
        self::LANG_en_BE => self::LANG_en_BE,
        self::LANG_ger_DE, self::LANG_ger_DE,
        self::LANG_CUSTOM => self::LANG_CUSTOM
    ];

    /**
     * Get the language Data. (PocketMine-MP Config)
     * @return Config
     */
    public static function getLanguageData(): Config
    {
        $rawLang = BetterMute::getInstance()->getConfig()->get("language");
        if (strtolower($rawLang) == "custom") {
            BetterMute::getInstance()->saveResource("languages/messages.yml");
            ConfigManager::updateConfig(new Config(BetterMute::getInstance()->getDataFolder() . "languages/" . "messages.yml", Config::YAML), BetterMute::getInstance()->getDataFolder() . "languages/", "messages.yml", ConfigManager::UPDATE_MESSAGES, "languages/messages.yml");
            return new Config(BetterMute::getInstance()->getDataFolder() . "languages/messages.yml", Config::YAML);
        }
        if (isset(self::$languages[$rawLang]) && file_exists(BetterMute::getInstance()->getFile2() . "resources/languages/" . $rawLang . ".yml")) return new Config(BetterMute::getInstance()->getFile2() . "resources/languages/" . $rawLang . ".yml", Config::YAML);
        return self::getDefaultLanguageData();
    }

    /**
     * @return string
     */
    public static function getLanguage(): string
    {
        $rawLang = BetterMute::getInstance()->getConfig()->get("language");
        if (strtolower($rawLang) == "custom") {
            return self::LANG_CUSTOM;
        }
        if (isset(self::$languages[$rawLang]) && file_exists(BetterMute::getInstance()->getFile2() . "resources/languages/" . $rawLang . ".yml")) return $rawLang;
        return self::LANG_en_BE;
    }

    /**
     * @return Config
     */
    public static function getDefaultLanguageData(): Config
    {
        return new Config(BetterMute::getInstance()->getFile2() . "resources/languages/" . self::LANG_en_BE . ".yml", Config::YAML);
    }

    /**
     * @return Config
     */
    public static function getCustomLanguageData(): Config
    {
        return new Config(BetterMute::getInstance()->getDataFolder() . "languages/messages.yml", Config::YAML);
    }
}