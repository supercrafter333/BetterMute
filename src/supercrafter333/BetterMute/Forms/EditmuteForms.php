<?php

namespace supercrafter333\BetterMute\Forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\element\Slider;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use supercrafter333\BetterMute\Events\BMEditMuteEvent;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class EditmuteForms
{

    public static function main(): MenuForm
    {
        /**
         * @var MenuOption[] $playerButtons
         */
        $playerButtons = [];
        foreach (MuteManager::getMutes() as $mutedPlayer) {
            if (!MuteManager::getMute($mutedPlayer)->isPermanentlyMuted()) {
                $playerButtons[] = new MenuOption($mutedPlayer);
            }
        }

        return new MenuForm(
            LanguageMgr::getMsg("form_editmute-title"),
            LanguageMgr::getMsg("form_editmute-main-content"),
            $playerButtons,
            function (Player $submitter, int $selected) use ($playerButtons): void {
                $playerName = $playerButtons[$selected]->getText();
                $mute = MuteManager::getMute($playerName);
                if ($mute->isPermanentlyMuted()) {
                    $submitter->sendMessage(LanguageMgr::getMsg("permanently-muted", ["{player}" => $playerName]));
                    return;
                }
                $submitter->sendForm(self::selectTime($playerName));
            }
        );
    }

    public static function selectTime(string $playerName): MenuForm
    {
        return new MenuForm(
            LanguageMgr::getMsg("form_editmute-title"),
            LanguageMgr::getMsg("form_editmute-selectTime-content"),
            [
                new MenuOption("Add Time"),
                new MenuOption("Reduce Time")
            ],
            function (Player $submitter, int $selectedOption) use ($playerName): void
            {
                if ($selectedOption == 0) {
                    $submitter->sendForm(self::addTime($playerName));
                } elseif ($selectedOption == 1) {
                    $submitter->sendForm(self::reduceTime($playerName));
                }
            }
        );
    }

    public static function reduceTime(string $playerName): CustomForm
    {
        return new CustomForm(
            LanguageMgr::getMsg("form_editmute-reduceTime-title"),
            [
                new Label("content", LanguageMgr::getMsg("form_editmute-reduceTime-content")),
                new Slider("mins", "Minutes", 0, 60),
                new Slider("hours", "Hours", 0, 24),
                new Slider("days", "Days", 0, 30),
                new Slider("months", "Months", 0, 12),
                new Slider("years", "Years", 0, 60)
            ],
            function (Player $submitter, CustomFormResponse $response) use ($playerName): void {
                $res = $response->getAll();

                if (!MuteManager::isMuted($playerName)) {
                    $submitter->sendMessage(LanguageMgr::getMsg("not-muted", ["{player}" => $playerName]));
                    return;
                }

                $mute = MuteManager::getMute($playerName);

                if ($mute->isPermanentlyMuted()) {
                    $submitter->sendMessage(LanguageMgr::getMsg("permanently-muted", ["{player}" => $playerName]));
                    return;
                }

                $date = $mute->getMutedUntil();

                if ($res["mins"] !== null) $date->modify('-' . $res["mins"] . ' minutes');
                if ($res["hours"] !== null) $date->modify('-' . $res["hours"] . ' hours');
                if ($res["days"] !== null) $date->modify('-' . $res["days"] . ' days');
                if ($res["months"] !== null) $date->modify('-' . $res["months"] . ' months');
                if ($res["years"] !== null) $date->modify('-' . $res["years"] . ' years');

                $mute->setMutedUntil($date);

                $ev = new BMEditMuteEvent($mute, BMEditMuteEvent::REDUCE_TIME);
                $ev->call();
                if ($ev->isCancelled()) return;

                $mute = $ev->getMute();
                MuteManager::saveMute($mute);
                $submitter->sendMessage(LanguageMgr::getMsg("mutetime-success", ["{player}" => $playerName, "{newDate}" => $date->format("Y.m.d H:i:s")]));
            }
        );
    }

    public static function addTime(string $playerName): CustomForm
    {
        return new CustomForm(
            LanguageMgr::getMsg("form_editmute-addTime-title"),
            [
                new Label("content", LanguageMgr::getMsg("form_editmute-addTime-content")),
                new Slider("mins", "Minutes", 0, 60),
                new Slider("hours", "Hours", 0, 24),
                new Slider("days", "Days", 0, 30),
                new Slider("months", "Months", 0, 12),
                new Slider("years", "Years", 0, 60)
            ],
            function (Player $submitter, CustomFormResponse $response) use ($playerName): void {
                $res = $response->getAll();

                if (!MuteManager::isMuted($playerName)) {
                    $submitter->sendMessage(LanguageMgr::getMsg("not-muted", ["{player}" => $playerName]));
                    return;
                }

                $mute = MuteManager::getMute($playerName);

                if ($mute->isPermanentlyMuted()) {
                    $submitter->sendMessage(LanguageMgr::getMsg("permanently-muted", ["{player}" => $playerName]));
                    return;
                }

                $date = $mute->getMutedUntil();

                if ($res["mins"] !== null) $date->modify('+' . $res["mins"] . ' minutes');
                if ($res["hours"] !== null) $date->modify('+' . $res["hours"] . ' hours');
                if ($res["days"] !== null) $date->modify('+' . $res["days"] . ' days');
                if ($res["months"] !== null) $date->modify('+' . $res["months"] . ' months');
                if ($res["years"] !== null) $date->modify('+' . $res["years"] . ' years');

                $mute->setMutedUntil($date);

                $ev = new BMEditMuteEvent($mute, BMEditMuteEvent::ADD_TIME);
                $ev->call();
                if ($ev->isCancelled()) return;

                $mute = $ev->getMute();
                MuteManager::saveMute($mute);
                $submitter->sendMessage(LanguageMgr::getMsg("mutetime-success", ["{player}" => $playerName, "{newDate}" => $date->format("Y.m.d H:i:s")]));
            }
        );
    }
}