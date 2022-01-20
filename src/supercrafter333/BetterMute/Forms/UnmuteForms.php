<?php

namespace supercrafter333\BetterMute\Forms;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\Label;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\player\Player;
use pocketmine\Server;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class UnmuteForms
{

    public static function main(): MenuForm
    {
        $playerList = Server::getInstance()->getOnlinePlayers();

        /**
         * @var MenuOption[] $playerButtons
         */
        $playerButtons = [];
        foreach (MuteManager::getMutes() as $mutedPlayer) {
            $playerButtons[] = new MenuOption($mutedPlayer);
        }

        return new MenuForm(
            LanguageMgr::getMsg("form_unmute-title"),
            LanguageMgr::getMsg("form_unmute-main-content"),
            $playerButtons,
            function (Player $submitter, int $selected) use ($playerButtons): void {
                $playerName = $playerButtons[$selected]->getText();
                BetterMute::getInstance()->commands["unmute"]->execute($submitter, "", [$playerName]);
            }
        );
    }
}