<?php

namespace supercrafter333\BetterMute\Events;

use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class BMUnmuteEvent extends Event
{
    use CancellableTrait;

    public function __construct(private string $unmutedPlayerName) {}

    /**
     * @return string
     */
    public function getUnmutedPlayerName(): string
    {
        return $this->unmutedPlayerName;
    }
}