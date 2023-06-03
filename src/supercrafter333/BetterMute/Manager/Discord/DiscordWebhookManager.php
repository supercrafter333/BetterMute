<?php

namespace supercrafter333\BetterMute\Manager\Discord;

use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Info\Mute;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\DiscordWebhooksX\Embed;
use supercrafter333\DiscordWebhooksX\Message;
use supercrafter333\DiscordWebhooksX\Webhook;

class DiscordWebhookManager
{

    private static function getWebhookInfo(string $webhookMsg, string $info, ?array $replace = null): string|null
    {
        return LanguageMgr::getMsg("webhook_" . $webhookMsg . "_" . $info, $replace) !== "ERROR! Message not found!" ? LanguageMgr::getMsg("webhook_" . $webhookMsg . "_" . $info) : null;
    }

    public static function simpleSendWebhook(Embed $embed): void
    {
        $webhook = new Webhook(ConfigManager::getDiscordWebhookUrl());

        $webhook->send(Message::create([$embed]));
    }

    public static function muteMessage(Mute $mute): void
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        $name = $mute->getPlayerName();
        $by = $mute->getMutedBy();
        $until = $mute->isPermanentlyMuted() ? "PERMANENTLY" : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $reason = $mute->getReason() === null ? "---" : $mute->getReason();

        self::simpleSendWebhook(Embed::create()
        ->setTitle(self::getWebhookInfo("mute", "title"))
        ->setDescription(LanguageMgr::getMsg("webhook_mute_content", ["{player}" => $name, "{by}" => $by, "{until}" => $until, "{reason}" => $reason]))
        ->setColor(self::getWebhookInfo("mute", "color")));
    }
    
    public static function unmuteMessage(string $playerName, string $unmutedBy): void
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        self::simpleSendWebhook(Embed::create()
        ->setTitle(self::getWebhookInfo("unmute", "title"))
        ->setDescription(LanguageMgr::getMsg("webhook_unmute_content", ["{player}" => $playerName, "{by}" => $unmutedBy]))
        ->setColor(self::getWebhookInfo("unmute", "color")));
    }

    public static function editmuteMessage(Mute $mute): void //add $editedBy ??? To-Do?
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        $playerName = $mute->getPlayerName();
        $mutedBy = $mute->getMutedBy();
        $until = $mute->isPermanentlyMuted() ? "PERMANENTLY" : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $reason = $mute->getReason() === null ? "---" : $mute->getReason();

        self::simpleSendWebhook(Embed::create()
        ->setTitle(self::getWebhookInfo("editmute", "title"))
        ->setDescription(LanguageMgr::getMsg("webhook_editmute_content", ["{player}" => $playerName, "{newDate}" => $until, "{reason}" => $reason, "{mutedBy}" => $mutedBy]))
        ->setColor(self::getWebhookInfo("editmute", "color")));
    }
}