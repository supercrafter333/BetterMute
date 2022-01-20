<?php

namespace supercrafter333\BetterMute\Manager\Messages;

use pocketmine\utils\Config;

class LanguageMgr
{
/**
     * Language Data (PocketMine-MP Config)
     * @var Config $langData
     */
    public Config $langData;

    /**
     * Get a message.
     * [auto-replace: {line} to \n]
     *
     * @param string $message
     * @param array|null $replace
     * @return string
     */
    public static function getMsg(string $message, array|null $replace = null): string
    {
        $lang = Languages::getLanguageData();
        if (!$lang->exists($message)) {
            if (!Languages::getDefaultLanguageData()->exists($message)) return "ERROR! Message not found!";
            $replaced = str_replace("{line}", "\n", Languages::getDefaultLanguageData()->get($message));
            if ($replace !== null) {
                foreach (array_keys($replace) as $i) {
                    $replaced = str_replace($i, $replace[$i], $replaced);
                }
            }
            return $replaced;
        }
        $replaced = str_replace("{line}", "\n", $lang->get($message));
        if ($replace !== null) {
            foreach (array_keys($replace) as $i) {
                $replaced = str_replace($i, $replace[$i], $replaced);
            }
        }
        return $replaced;
    }

    /**
     * Get a message without auto-replace.
     * @param string $message
     * @return string
     */
    public static function getMsgWithNoExtras(string $message): string
    {
        $lang = Languages::getLanguageData();
        if (!$lang->exists($message)) {
            if (!Languages::getDefaultLanguageData()->exists($message)) return "ERROR! Message not found!";
            return Languages::getDefaultLanguageData()->get($message);
        }
        return $lang->get($message);
    }

    /**
     * @return string
     */
    public static function getNoPermMsg(): string
    {
        return self::getMsg("no-perm");
    }

    /**
     * @return string
     */
    public static function getOnlyIG(): string
    {
        return self::getMsg("only-In-Game");
    }
}