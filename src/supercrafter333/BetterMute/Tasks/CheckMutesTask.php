<?php

namespace supercrafter333\BetterMute\Tasks;

use pocketmine\scheduler\Task;
use supercrafter333\BetterMute\Manager\MuteManager;

class CheckMutesTask extends Task
{

    public function onRun(): void
    {
        MuteManager::checkMutes();
    }
}