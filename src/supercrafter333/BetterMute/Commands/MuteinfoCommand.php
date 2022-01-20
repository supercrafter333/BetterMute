<?php

namespace supercrafter333\BetterMute\Commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use supercrafter333\BetterMute\Forms\MuteInfoForms;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class MuteinfoCommand extends BetterMuteCommand
{

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        if (!$this->canUse($s)) return;

        if (!isset($args[0])) {
            if (ConfigManager::useForms() && $s instanceof Player) {
                $s->sendForm(MuteInfoForms::main());
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }

        $name = implode(" ", $args);

        if (($playerX = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($name)) instanceof Player) $name = $playerX->getName();

        if (!MuteManager::isMuted($name)) {
            $s->sendMessage(LanguageMgr::getMsg("not-muted", ["{player}" => $name]));
            return;
        }

        if (ConfigManager::useForms() && $s instanceof Player) {
            $s->sendForm(MuteInfoForms::muteInfo($name));
            return;
        }

        $mute = MuteManager::getMute($name);
        $until = is_string($mute->getMutedUntil()) ? $mute->getMutedUntil() : $mute->getMutedUntil()->format("Y.m.d H:i:s");
        $by = $mute->getMutedBy();
        $reason = $mute->getReason() !== null ? $mute->getReason() : "---";

        $s->sendMessage(LanguageMgr::getMsg("mute-info", ["{player}" => $name, "{until}" => $until, "{by}" => $by, "{reason}" => $reason]));
        return;
    }
}