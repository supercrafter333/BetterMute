<?php

namespace supercrafter333\BetterMute\Events;

use DateTime;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class BMPreMuteEvent extends Event
{
    use CancellableTrait;

    public function __construct(private string $mutedPlayerName, private DateTime|string $mutedUntil, private string $mutedByName, private string|null $reason = null) {}

    /**
     * @return string
     */
    public function getMutedPlayerName(): string
    {
        return $this->mutedPlayerName;
    }

    /**
     * @return DateTime|string
     */
    public function getMutedUntil(): DateTime|string
    {
        return $this->mutedUntil;
    }

    /**
     * @return string
     */
    public function getMutedByName(): string
    {
        return $this->mutedByName;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}