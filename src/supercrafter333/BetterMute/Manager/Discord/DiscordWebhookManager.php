<?php

namespace supercrafter333\BetterMute\Manager\Discord;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Info\Mute;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;

class DiscordWebhookManager
{

    private static function getWebhookInfo(string $webhookMsg, string $info, ?array $replace = null): string|null
    {
        return LanguageMgr::getMsg("webhook_" . $webhookMsg . "_" . $info, $replace) !== "ERROR! Message not found!" ? LanguageMgr::getMsg("webhook_" . $webhookMsg . "_" . $info) : null;
    }

    public static function simpleSendWebhook(Embed $embed): void
    {
        $webhook = new Webhook(ConfigManager::getDiscordWebhookUrl());

        $msg = new Message();
        $msg->addEmbed($embed);

        $webhook->send($msg);
    }

    public static function muteMessage(Mute $mute): void
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        $name = $mute->getPlayerName();
        $by = $mute->getMutedBy();
        $until = $mute->isPermanentlyMuted() ? "PERMANENTLY" : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $reason = $mute->getReason() === null ? "---" : $mute->getReason();

        $embed = new Embed();
        $embed->setTitle(self::getWebhookInfo("mute", "title"));
        $embed->setDescription(LanguageMgr::getMsg("webhook_mute_content", ["{player}" => $name, "{by}" => $by, "{until}" => $until, "{reason}" => $reason]));
        $embed->setColor(self::getWebhookInfo("mute", "color"));

        self::simpleSendWebhook($embed);
    }
    
    public static function unmuteMessage(string $playerName, string $unmutedBy): void
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        $embed = new Embed();
        $embed->setTitle(self::getWebhookInfo("unmute", "title"));
        $embed->setDescription(LanguageMgr::getMsg("webhook_unmute_content", ["{player}" => $playerName, "{by}" => $unmutedBy]));
        $embed->setColor(self::getWebhookInfo("unmute", "color"));

        self::simpleSendWebhook($embed);
    }

    public static function editmuteMessage(Mute $mute): void //add $editedBy ??? To-Do?
    {
        if (!ConfigManager::useDiscordWebhooks()) return;

        $playerName = $mute->getPlayerName();
        $mutedBy = $mute->getMutedBy();
        $until = $mute->isPermanentlyMuted() ? "PERMANENTLY" : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $reason = $mute->getReason() === null ? "---" : $mute->getReason();

        $embed = new Embed();
        $embed->setTitle(self::getWebhookInfo("editmute", "title"));
        $embed->setDescription(LanguageMgr::getMsg("webhook_editmute_content", ["{player}" => $playerName, "{newDate}" => $until, "{reason}" => $reason, "{mutedBy}" => $mutedBy]));
        $embed->setColor(self::getWebhookInfo("editmute", "color"));

        self::simpleSendWebhook($embed);
    }
}