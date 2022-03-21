<?php

namespace supercrafter333\BetterMute;

use DateTime;
use Exception;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use supercrafter333\BetterMute\Events\BMEditMuteEvent;
use supercrafter333\BetterMute\Events\BMMuteEvent;
use supercrafter333\BetterMute\Events\BMPreUnmuteEvent;
use supercrafter333\BetterMute\Events\BMUnmuteEvent;
use supercrafter333\BetterMute\Manager\Discord\DiscordWebhookManager;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class EventListener implements Listener
{

    /**
     * @throws Exception
     */
    public function onChat(PlayerChatEvent $ev): void
    {
        $player = $ev->getPlayer();

        if (MuteManager::isMuted($player)) {
            $mute = MuteManager::getMute($player);
            /**
             * @var string $until
            */
            $until = is_string($mute->getMutedUntil()) ? $mute->getMutedUntil() : $mute->getMutedUntil()->format("Y.m.d H:i:s");
            /**
             * @var string $reason
            */
            $reason = $mute->getReason() !== null ? $mute->getReason() : "---";
            $player->sendMessage(LanguageMgr::getMsg("you-are-muted", ["{by}" => $mute->getMutedBy(), "{until}" => $until, "{reason}" => $reason]));
            $ev->cancel();
        }
    }

    public function onMute(BMMuteEvent $ev): void
    {
        if (!$ev->isCancelled()) DiscordWebhookManager::muteMessage($ev->getMute());
    }

    public function onPreUnmute(BMPreUnmuteEvent $ev): void
    {
        if (!$ev->isCancelled()) DiscordWebhookManager::unmuteMessage($ev->getUnmutedPlayerName(), $ev->getUnmutedByName());
        if ($ev->getUnmutedByName() == "AUTOMATICALLY") {
            BetterMute::getInstance()->getServer()->getPlayerExact($ev->getUnmutedPlayerName())?->sendMessage(LanguageMgr::getMsg("target-unmuted-success", ["{by}" => $ev->getUnmutedByName()]));
        }
    }

    public function onEditmute(BMEditMuteEvent $ev): void
    {
        if (!$ev->isCancelled()) DiscordWebhookManager::editmuteMessage($ev->getMute());
    }
}