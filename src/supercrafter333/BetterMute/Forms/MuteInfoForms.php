<?php


namespace supercrafter333\BetterMute\Forms;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class MuteInfoForms
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
            LanguageMgr::getMsg("form_muteinfo-title"),
            LanguageMgr::getMsg("form_muteinfo-main-content"),
            $playerButtons,
            function (Player $submitter, int $selected) use ($playerButtons): void {
                $playerName = $playerButtons[$selected]->getText();
                $submitter->sendForm(self::muteInfo($playerName));
            }
        );
    }

    public static function muteInfo(string $playerName): MenuForm
    {
        if (!MuteManager::isMuted($playerName)) return new MenuForm(
            "ERROR",
            "Error! Cannot find mute...",
            [
                new MenuOption("Close")
            ],
            function (Player $submitter, int $selected): void {
                return;
            }
        );

        $mute = MuteManager::getMute($playerName);
        $until = is_string($mute->getMutedUntil()) ? $mute->getMutedUntil() : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $reason = $mute->getReason() !== null ? $mute->getReason() : "---";

        return new MenuForm(
            LanguageMgr::getMsg("form_muteinfo-title"),
            LanguageMgr::getMsg("form_muteinfo-muteInfo", ["{player}" => $playerName, "{until}" => $until, "{by}" => $mute->getMutedBy(), "{reason}" => $reason]),
            [
                new MenuOption("Edit Mute"),
                new MenuOption("Unmute")
            ],
            function (Player $submitter, int $selected) use ($playerName): void {
                if ($selected == 0) {
                    $submitter->sendForm(EditmuteForms::selectTime($playerName));
                    return;
                }
                if ($selected == 1) {
                    BetterMute::getInstance()->commands["unmute"]->execute($submitter, "", [$playerName]);
                    return;
                }
            }
        );
    }
}