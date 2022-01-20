<?php

namespace supercrafter333\BetterMute\Manager;

use DateTime;
use Exception;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use supercrafter333\BetterMute\Events\BMMuteEvent;
use supercrafter333\BetterMute\Events\BMMuteSaveEvent;
use supercrafter333\BetterMute\Events\BMPreMuteEvent;
use supercrafter333\BetterMute\Events\BMPreUnmuteEvent;
use supercrafter333\BetterMute\Events\BMUnmuteEvent;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Info\Mute;

/**
 *
 */
class MuteManager
{

    /**
     * Returns BetterMute's Mute-List as pocketmine's Config-class.
     *
     * @return Config
     */
    public static function getMuteList(): Config
    {
        return new Config(ConfigManager::getMutePath(), Config::YAML);
    }



    /**
     * Cleans and remove the format of a player's name.
     *
     * @param Player|string $player
     * @return string
     */
    public static function getCleanPlayerName(Player|string $player): string
    {
        if ($player instanceof Player) return TextFormat::clean(mb_strtolower($player->getName()));

        return TextFormat::clean(mb_strtolower($player));
    }

    /**
     * Checks if a player is muted and returns that.
     *
     * @param Player|string $player
     * @return bool
     * @throws Exception
     */
    public static function isMuted(Player|string $player): bool
    {
        $playerName = self::getCleanPlayerName($player);
        $list = self::getMuteList();

        if (!$list->exists($playerName)) return false;

        $mute = self::getMute($playerName);

        if ($mute->isPermanentlyMuted()) return true;
        if ($mute->getMutedUntil() > new DateTime('now')) return true;

        self::simpleUnmute($playerName);
        return false;
    }

    /**
     * Will return the Mute of a player if the player is muted.
     *
     * @param Player|string $player
     * @return Mute|null
     * @throws Exception
     */
    public static function getMute(Player|string $player): Mute|null
    {
        $playerName = self::getCleanPlayerName($player);
        $list = self::getMuteList();

        if (!$list->exists($playerName)) return null;

        return new Mute($playerName, true);
    }

    /**
     * Will unmute a player.
     *
     * @param Player|string $player
     * @return bool
     */
    public static function unmute(Player|string $player): bool
    {
        $playerName = self::getCleanPlayerName($player);

        $ev = new BMUnmuteEvent($playerName);
        $ev->call();
        if ($ev->isCancelled()) return false;

        $list = self::getMuteList();

        if (!$list->exists($playerName)) return false;

        $list->remove($playerName);
        $list->save();

        return true;
    }

    /**
     * Saves a mute.
     *
     * @param Mute $mute
     * @return void
     */
    public static function saveMute(Mute $mute): void
    {
        $ev = new BMMuteSaveEvent($mute);
        $ev->call();
        if ($ev->isCancelled()) return;
        $mute = $ev->getMute();

        $list = self::getMuteList();

        $playerName = $mute->getPlayerName();

        $list->set($playerName, $mute->createString());
        $list->save();
    }

    /**
     * Will simple mute a player.
     *
     * @param Player|string $player
     * @param string $mutedBy
     * @param string|DateTime $mutedUntil
     * @param string|null $reason
     * @return bool
     * @throws Exception
     */
    public static function simpleMute(Player|string $player, string $mutedBy, string|DateTime $mutedUntil = "PERMANENTLY", string|null $reason = null): bool
    {
        $playerName = self::getCleanPlayerName($player);

        $ev = new BMPreMuteEvent($playerName, $mutedUntil, $mutedBy, $reason);
        $ev->call();
        if ($ev->isCancelled()) return false;

        if (self::isMuted($playerName)) return false;

        $mute = new Mute($playerName, false, $mutedUntil, $mutedBy, $reason);

        $ev2 = new BMMuteEvent($mute);
        $ev2->call();
        if ($ev2->isCancelled()) return false;
        $mute = $ev2->getMute();

        self::saveMute($mute);
        return true;
    }

    /**
     * Will simple unmute a player.
     *
     * @param Player|string $player
     * @param string $unmutedBy
     * @return bool
     */
    public static function simpleUnmute(Player|string $player, string $unmutedBy = "AUTOMATICALLY"): bool
    {
        $playerName = self::getCleanPlayerName($player);

        $ev = new BMPreUnmuteEvent($playerName, $unmutedBy);
        $ev->call();
        if ($ev->isCancelled()) return false;

        return self::unmute($player);
    }

    /**
     * Will check all mutes.
     *
     * @return void
     * @throws Exception
     */
    public static function checkMutes(): void
    {
        foreach (self::getMuteList()->getAll(true) as $player) {
            $playerName = self::getCleanPlayerName($player);
            $list = self::getMuteList();

            if (!$list->exists($playerName)) return;

            $mute = self::getMute($playerName);

            self::isMuted($playerName);


            /*if (!is_string($mute->getMutedUntil()) && $mute->getMutedUntil() <= new DateTime('now')) {
                return;
            } else elseif (is_string($mute->getMutedUntil()) && $mute->getMutedUntil() == "PERMANENTLY") {
                return;
            }*/
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getMutes(): array
    {
        self::checkMutes();
        $mutes = [];
        $mutes = self::getMuteList()->getAll(true);
        return $mutes;
    }
}