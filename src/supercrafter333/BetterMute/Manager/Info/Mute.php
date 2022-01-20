<?php

namespace supercrafter333\BetterMute\Manager\Info;

use DateTime;
use Exception;
use supercrafter333\BetterMute\Manager\MuteManager;

/**
 * Mute class.
 */
class Mute
{

    /**
     * @param string $playerName
     * @param bool $autoDetect
     * @param DateTime|string $mutedUntil
     * @param string $mutedBy
     * @param string|null $reason
     * @throws Exception
     */
    public function __construct(private string $playerName, bool $autoDetect = false, private \DateTime|string $mutedUntil = "PERMANENTLY", private string $mutedBy = "[UNKNOWN]", private string|null $reason = null)
    {
        if ($autoDetect) {
            $vals = MuteManager::getMuteList()->get(MuteManager::getCleanPlayerName($this->playerName));
            $vals = explode("|", $vals);
            $this->setMutedUntil($vals[0]);
            if ($this->isPermanentlyMuted()) {
                $this->setMutedUntil("PERMANENTLY");
            } else {
                $this->setMutedUntil(new DateTime($vals[0]));
            }
            $this->setMutedBy($vals[1]);
            if (isset($vals[2])) $this->setReason($vals[2]);
        }
    }

    /**
     * @return string
     */
    public function getPlayerName(): string
    {
        return MuteManager::getCleanPlayerName($this->playerName);
    }

    /**
     * @return DateTime|string
     */
    public function getMutedUntil(): DateTime|string
    {
        return $this->mutedUntil;
    }

    /**
     * @return bool
     */
    public function isPermanentlyMuted(): bool
    {
        if ($this->getMutedUntil() instanceof DateTime) return false;
        if ($this->mutedUntil == "PERMANENTLY" || $this->mutedUntil == "PERMANENT" || $this->mutedUntil == "PERMA") return true;
        return false;
    }

    /**
     * @return string
     */
    public function getMutedBy(): string
    {
        return $this->mutedBy;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @param string $playerName
     */
    public function setPlayerName(string $playerName): void
    {
        $this->playerName = $playerName;
    }

    /**
     * @param DateTime|string $mutedUntil
     */
    public function setMutedUntil(\DateTime|string $mutedUntil = "PERMANENTLY"): void
    {
        $this->mutedUntil = $mutedUntil;
    }

    /**
     * @param string $mutedBy
     */
    public function setMutedBy(string $mutedBy = "[UNKNOWN]"): void
    {
        $this->mutedBy = $mutedBy;
    }

    /**
     * @param string|null $reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * Returns the mute-string for the config.
     *
     * @return string
     */
    public function createString(): string
    {
        /**
         * @var string $until
        */
        $until = is_string($this->mutedUntil) ? $this->mutedUntil : $this->mutedUntil->format("Y-m-d H:i:s");
        $reason = $this->reason !== null ? "|" . $this->reason : "";
        return $until . "|" . $this->mutedBy . $reason;
    }
}