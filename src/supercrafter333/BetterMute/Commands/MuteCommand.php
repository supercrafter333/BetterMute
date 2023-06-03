<?php

namespace supercrafter333\BetterMute\Commands;

use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use supercrafter333\BetterMute\BetterMute;
use supercrafter333\BetterMute\Forms\MuteForms;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Messages\LanguageMgr;
use supercrafter333\BetterMute\Manager\MuteManager;
use function is_string;
use function str_replace;

class MuteCommand extends BetterMuteCommand
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

        if (isset($args[0])) {
            $name = array_shift($args);
            $player = null;

            if (($playerX = $this->getOwningPlugin()->getServer()->getPlayerByPrefix($name)) instanceof Player) {
                $player = $playerX;
                $name = $playerX->getName();
            }

            $reason = null;
            $until = "PERMANENTLY";

            if (isset($args[0])) {
                $reason = array_shift($args);
            }
            if (isset($args[1])) {
                $until = implode(" ", $args);
            }

            if (MuteManager::isMuted($name)) {
                $s->sendMessage(LanguageMgr::getMsg("already-muted", ["{player}" => $name]));
                return;
            }

            if (mb_strtoupper($until) !== "PERMANENTLY" && mb_strtoupper($until) !== "PERMANENT" && mb_strtoupper($until) !== "PERMA") {
                /**
                 * @var DateTime|string $until
                 */
                $until = BetterMute::getInstance()->stringToTimestamp($until)[0];
            } elseif (mb_strtoupper($until) === "PERMANENTLY" || mb_strtoupper($until) === "PERMANENT" || mb_strtoupper($until) === "PERMA") $until = "PERMANENTLY";

            if ($until === null) {
                $s->sendMessage($this->usageMessage);
                return;
            }

            MuteManager::simpleMute($name, $s->getName(), $until, $reason);

            $until_dt = !is_string($until) ? $until : null;
            $until = !is_string($until) ? $until->format("Y.m.d H:i:s") : $until;
            /**
             * @param string $message
             * @return string
             */
            $timeDifference = function (string $message) use ($until_dt): string
            {
                if ($until_dt === null) return str_replace(["{y}", "{m}", "{d}", "{h}", "{i}"], ["permanent", "permanent", "permanent", "permanent"], $message);

                $diff = $until_dt->diff(new DateTime('now'));

                    return str_replace(
                        ["{y}", "{m}", "{d}", "{h}", "{i}"],
                        [(string)$diff->y, (string)$diff->m, (string)$diff->d, (string)$diff->h, (string)$diff->i],
                        $message
                    );
            };

            if ($this->getOwningPlugin()->useBroadcastMessages()) $this->getOwningPlugin()->getServer()->broadcastMessage(LanguageMgr::getMsg("muted-broadcast", ["{player}" => $name, "{by}" => $s->getName()]));

            if ($reason === null) {
                $s->sendMessage($timeDifference(LanguageMgr::getMsg("muted-success", ["{player}" => $name, "{until}" => $until])));
                $player?->sendMessage($timeDifference(LanguageMgr::getMsg("target-muted-success", ["{by}" => $s->getName(), "{until}" => $until])));
            } else {
                $s->sendMessage($timeDifference(LanguageMgr::getMsg("muted-success-with-reason", ["{player}" => $name, "{until}" => $until, "{reason}" => $reason])));
                $player?->sendMessage($timeDifference(LanguageMgr::getMsg("target-muted-success-with-reason", ["{by}" => $s->getName(), "{until}" => $until, "{reason}" => $reason])));
            }

            return;
        }
        if (ConfigManager::useForms() && $s instanceof Player) {
            $s->sendForm(MuteForms::main());
        } else {
            $s->sendMessage($this->usageMessage);
        }
    }
}