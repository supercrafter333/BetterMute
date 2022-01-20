<?php

namespace supercrafter333\BetterMute\Events;

use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class BMPreUnmuteEvent extends Event
{
    use CancellableTrait;

    public function __construct(private string $unmutedPlayerName, private string $unmutedByName = "AUTOMATICALLY") {}

    /**
     * @return string
     */
    public function getUnmutedPlayerName(): string
    {
        return $this->unmutedPlayerName;
    }

    /**
     * @return string
     */
    public function getUnmutedByName(): string
    {
        return $this->unmutedByName;
    }
}