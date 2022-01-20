<?php

namespace supercrafter333\BetterMute\Events;

use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\BetterMute\Manager\Info\Mute;

class BMEditMuteEvent extends Event
{
    use CancellableTrait;

    public const UNKNOWN = 0;

    public const ADD_TIME = 1;

    public const REDUCE_TIME = 2;


    public function __construct(private Mute $mute, private int $action = self::UNKNOWN) {}

    /**
     * @return Mute
     */
    public function getMute(): Mute
    {
        return $this->mute;
    }

    /**
     * @param Mute $mute
     */
    public function setMute(Mute $mute): void
    {
        $this->mute = $mute;
    }

    /**
     * @return int
     */
    public function getAction(): int
    {
        return $this->action;
    }
}