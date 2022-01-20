<?php

namespace supercrafter333\BetterMute\Commands;

use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Events\BMEditMuteEvent;
use supercrafter333\BetterMute\Forms\EditmuteForms;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;

class EditmuteCommand extends BetterMuteCommand
{

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {

        if (count($args) >= 3) {

            $subCmd = array_shift($args);
            $name = array_shift($args);
            $time = implode(" ", $args);
            $player = null;

            if (($playerX = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($name)) instanceof Player) {
                $player = $playerX;
                $name = $playerX->getName();
            }

            if (!MuteManager::isMuted($name)) {
                $s->sendMessage(LanguageMgr::getMsg("not-muted", ["{player}" => $name]));
                return;
            }

            $mute = MuteManager::getMute($name);

            if ($mute->isPermanentlyMuted()) {
                $s->sendMessage(LanguageMgr::getMsg("permanently-muted", ["{player}" => $name]));
                return;
            }

            switch ($subCmd) {
                case "addtime":
                    /**
                     * @var DateTime $dt
                    */
                    $dt = BetterMute::getInstance()->stringToTimestampAdd($time, $mute->getMutedUntil())[0];

                    $mute->setMutedUntil($dt);

                    $ev = new BMEditMuteEvent($mute, BMEditMuteEvent::ADD_TIME);
                    $ev->call();
                    if ($ev->isCancelled()) return;

                    $mute = $ev->getMute();

                    MuteManager::saveMute($mute);

                    $s->sendMessage(LanguageMgr::getMsg("mutetime-success", ["{player}" => $name, "{newDate}" => $dt->format("Y.m.d H:i:s")]));
                    break;

                case "reducetime":
                    /**
                     * @var DateTime $dt
                    */
                    $dt = BetterMute::getInstance()->stringToTimestampReduce($time, $mute->getMutedUntil())[0];

                    if ($dt < new \DateTime('now')) {
                        $s->sendMessage(LanguageMgr::getMsg("negative-time"));
                    }

                    $mute->setMutedUntil($dt);

                    $ev = new BMEditMuteEvent($mute, BMEditMuteEvent::REDUCE_TIME);
                    $ev->call();
                    if ($ev->isCancelled()) return;

                    $mute = $ev->getMute();

                    MuteManager::saveMute($mute);

                    $s->sendMessage(LanguageMgr::getMsg("mutetime-success", ["{player}" => $name, "{newDate}" => $dt->format("Y.m.d H:i:s")]));
                    break;

                default:
                    $s->sendMessage($this->usageMessage);
                    break;
            }
            return;
        } elseif (isset($args[0]) && (!isset($args[1]) || !isset($args[2]))) {
            $s->sendMessage($this->usageMessage);
            return;
        } elseif (!isset($args[0])) {
            if (ConfigManager::useForms() && $s instanceof Player) {
                $s->sendForm(EditmuteForms::main());
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        } elseif (isset($args[1])) {
            if (ConfigManager::useForms() && $s instanceof Player) {
                $s->sendForm(EditmuteForms::selectTime($args[1]));
            } else {
                $s->sendMessage($this->usageMessage);
            }
            return;
        }
    }
}