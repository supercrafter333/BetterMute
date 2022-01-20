<?php

namespace supercrafter333\BetterMute\Commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use supercrafter333\BetterMute\Forms\UnmuteForms;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class UnmuteCommand extends BetterMuteCommand
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
                $s->sendForm(UnmuteForms::main());
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }

        $name = implode(" ", $args);
        $player = null;

        if (($playerX = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($name)) instanceof Player) {
            $player = $playerX;
            $name = $playerX->getName();
        }

        if (MuteManager::isMuted($name) && MuteManager::simpleUnmute($name, $s->getName())) {
            $s->sendMessage(LanguageMgr::getMsg("unmuted-success", ["{player}" => $name]));
            $player?->sendMessage(LanguageMgr::getMsg("target-unmuted-success", ["{by}" => $s->getName()]));
        } else {
            $s->sendMessage(LanguageMgr::getMsg("not-muted", ["{player}" => $name]));
        }
        return;
    }
}