<?php

namespace supercrafter333\BetterMute\Events;

use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\BetterMute\Manager\Info\Mute;

class BMMuteEvent extends Event
{
    use CancellableTrait;

    public function __construct(private Mute $mute) {}

    /**
     * @return Mute
     */
    public function getMute(): Mute
    {
        return $this->mute;
    }
}