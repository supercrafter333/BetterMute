<?php

namespace supercrafter333\BetterMute\Forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Slider;
use dktapps\pmforms\element\Toggle;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use pocketmine\Server;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Manager\Info\Mute;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class MuteForms
{

    public static function main(): MenuForm
    {
        $playerList = Server::getInstance()->getOnlinePlayers();

        /**
         * @var MenuOption[] $playerButtons
         */
        $playerButtons = [];
        $playerButtons[] = new MenuOption(LanguageMgr::getMsg("form_mute-main-offlinePlayer-button"));
        foreach ($playerList as $player) {
            $playerButtons[] = new MenuOption($player->getName());
        }

        return new MenuForm(
            LanguageMgr::getMsg("form_mute-title"),
            LanguageMgr::getMsg("form_mute-main-content"),
            $playerButtons,
            function (Player $submitter, int $selected) use ($playerButtons): void {
                if ($selected == 0) {
                    $submitter->sendForm(self::playerInput());
                }
                $playerName = $playerButtons[$selected]->getText();
                $submitter->sendForm(self::mute($playerName));
            }
        );
    }

    public static function playerInput(): CustomForm
    {
        return new CustomForm(
            LanguageMgr::getMsg("form_mute-title"),
            [
                new Label("content", LanguageMgr::getMsg("form_mute-inputPlayer-content")),
                new Input("input", LanguageMgr::getMsg("form_mute-inputPlayer-inputField"))
            ],
            function (Player $submitter, CustomFormResponse $response): void {
                $submitter->sendForm(self::mute($response->getAll()["input"]));
            }
        );
    }

    public static function mute(string $playerName): CustomForm
    {
        return new CustomForm(
            LanguageMgr::getMsg("form_mute-title"),
            [
                new Label("content", LanguageMgr::getMsg("form_mute-submitMute-content", ["{player}" => $playerName])),
                new Slider("mins", "Minutes", 0, 60),
                new Slider("hours", "Hours", 0, 24),
                new Slider("days", "Days", 0, 30),
                new Slider("months", "Months", 0, 12),
                new Slider("years", "Years", 0, 60),
                new Toggle("perma", "Permanently?", false),
                new Input("reason", "Reason")
            ],
            function (Player $submitter, CustomFormResponse $response) use ($playerName): void {
                $res = $response->getAll();
                $reason = is_string($res["reason"]) ? $res["reason"] : null;
                $now = new \DateTime('now');

                if ($res["mins"] !== null) $now->modify('+' . $res["mins"] . ' minutes');
                if ($res["hours"] !== null) $now->modify('+' . $res["hours"] . ' hours');
                if ($res["days"] !== null) $now->modify('+' . $res["days"] . ' days');
                if ($res["months"] !== null) $now->modify('+' . $res["months"] . ' months');
                if ($res["years"] !== null) $now->modify('+' . $res["years"] . ' years');

                if (MuteManager::isMuted($playerName)) {
                    $submitter->sendMessage(LanguageMgr::getMsg("already-muted", ["{player}" => $playerName]));
                    return;
                }
                $untilDate = $res["perma"] ? "PERMANENTLY" : $now;
                MuteManager::simpleMute($playerName, $submitter->getName(), $untilDate, $reason);

                $until = $res["perma"] ? "PERMANENTLY" : $untilDate->format("Y.m.d H:i:s");
                $player = Server::getInstance()->getPlayerExact($playerName);

                if ($reason === null) {
                    $submitter->sendMessage(LanguageMgr::getMsg("muted-success", ["{player}" => $playerName, "{until}" => $until]));
                    $player?->sendMessage(LanguageMgr::getMsg("target-muted-success", ["{by}" => $submitter->getName(), "{until}" => $until]));
                } else {
                    $submitter->sendMessage(LanguageMgr::getMsg("muted-success-with-reason", ["{player}" => $playerName, "{until}" => $until, "{reason}" => $reason]));
                    $player?->sendMessage(LanguageMgr::getMsg("target-muted-success-with-reason", ["{by}" => $submitter->getName(), "{until}" => $until, "{reason}" => $reason]));
                }
                return;
            }
        );
    }
}