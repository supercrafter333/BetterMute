<?php

namespace supercrafter333\BetterMute;

use DateInterval;
use DateTime;
use Exception;
use pocketmine\command\Command;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use supercrafter333\BetterMute\Commands\EditmuteCommand;
use supercrafter333\BetterMute\Commands\MuteCommand;
use supercrafter333\BetterMute\Commands\MuteinfoCommand;
use supercrafter333\BetterMute\Commands\UnmuteCommand;
use supercrafter333\BetterMute\Manager\Configuration\Commands\CommandConfigManager;
use supercrafter333\BetterMute\Manager\Configuration\ConfigManager;
use supercrafter333\BetterMute\Manager\Messages\Languages;
use supercrafter333\BetterMute\Tasks\CheckMutesTask;

class BetterMute extends PluginBase
{
    use SingletonTrait;

    /**
     * @var Command[]
     */
    public array $commands = [];

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->saveResource("config.yml");
        $this->saveResource("commands.yml");
        ConfigManager::startup();
        CommandConfigManager::startup();
        Languages::getLanguage();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->registerPermissions();
        $this->registerCommands();

        $this->getScheduler()->scheduleRepeatingTask(new CheckMutesTask(), (300*20));
    }

    public function registerCommands(): void
    {
        $mute = CommandConfigManager::getCommandInformations("mute");
        $this->commands["mute"] = new MuteCommand($mute["name"], $mute["desc"], "BetterMute.mute.cmd", $mute["usage"], $mute["aliases"]);

        $unmute = CommandConfigManager::getCommandInformations("unmute");
        $this->commands["unmute"] = new UnmuteCommand($unmute["name"], $unmute["desc"], "BetterMute.unmute.cmd", $unmute["usage"], $unmute["aliases"]);
        
        $editmute = CommandConfigManager::getCommandInformations("editmute");
        $this->commands["editmute"] = new EditmuteCommand($editmute["name"], $editmute["desc"], "BetterMute.editmute.cmd", $editmute["usage"], $editmute["aliases"]);
        
        $muteinfo = CommandConfigManager::getCommandInformations("muteinfo");
        $this->commands["muteinfo"] = new MuteinfoCommand($muteinfo["name"], $muteinfo["desc"], "BetterMute.muteinfo.cmd", $muteinfo["usage"], $muteinfo["aliases"]);

        foreach (array_keys($this->commands) as $cmdName) {
            $this->getServer()->getCommandMap()->register("BetterMute", $this->commands[$cmdName]);
        }
    }

    public function registerPermissions(): void
    {
        $adminPerm = PermissionManager::getInstance()->getPermission("BetterMute.admin");

        $children = [
            "BetterMute.mute.cmd",
            "BetterMute.unmute.cmd",
            "BetterMute.editmute.cmd",
            "BetterMute.muteinfo.cmd"
        ];

        foreach ($children as $child) {
            $adminPerm->addChild($child, true);
        }
    }

    public function getFile2(): string
    {
        return $this->getFile();
    }

    public function useBroadcastMessages(): bool
    {
        if (($send = $this->getConfig()->get("send-broadcast-messages", false)) !== true && $send !== "on") return false;
        return true;
    }


    # Copied from https://github.com/supercrafter333/BetterBan/blob/master/src/supercrafter333/BetterBan/BetterBan.php

     /**
     * @param string $string
     * @return array|null
     * @throws Exception
     */
    public function stringToTimestamp(string $string): ?array
    {
        /**
         * Rules:
         * Integers without suffix are considered as seconds
         * "s" is for seconds
         * "m" is for minutes
         * "h" is for hours
         * "d" is for days
         * "w" is for weeks
         * "mo" is for months
         * "y" is for years
         */
        if (trim($string) === "") {
            return null;
        }
        $t = new DateTime();
        preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $string, $found);
        if (count($found[0]) < 1) {
            return null;
        }
        $found[2] = preg_replace("/[^0-9]/", "", $found[0]);
        foreach ($found[2] as $k => $i) {
            switch ($c = $found[1][$k]) {
                case "y":
                case "w":
                case "d":
                    $t->add(new DateInterval("P" . $i . strtoupper($c)));
                    break;
                case "mo":
                    $t->add(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
                    break;
                case "h":
                case "m":
                case "s":
                    $t->add(new DateInterval("PT" . $i . strtoupper($c)));
                    break;
                default:
                    $t->add(new DateInterval("PT" . $i . "S"));
                    break;
            }
            $string = str_replace($found[0][$k], "", $string);
        }
        return [$t, ltrim(str_replace($found[0], "", $string))];
    }

    /**
     * @param string $string
     * @param DateTime $time
     * @return array|null
     * @throws Exception
     */
    public function stringToTimestampAdd(string $string, DateTime $time): ?array
    {
        /**
         * Rules:
         * Integers without suffix are considered as seconds
         * "s" is for seconds
         * "m" is for minutes
         * "h" is for hours
         * "d" is for days
         * "w" is for weeks
         * "mo" is for months
         * "y" is for years
         */
        if (trim($string) === "") {
            return null;
        }
        $t = $time;
        preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $string, $found);
        if (count($found[0]) < 1) {
            return null;
        }
        $found[2] = preg_replace("/[^0-9]/", "", $found[0]);
        foreach ($found[2] as $k => $i) {
            switch ($c = $found[1][$k]) {
                case "y":
                case "w":
                case "d":
                    $t->add(new DateInterval("P" . $i . strtoupper($c)));
                    break;
                case "mo":
                    $t->add(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
                    break;
                case "h":
                case "m":
                case "s":
                    $t->add(new DateInterval("PT" . $i . strtoupper($c)));
                    break;
                default:
                    $t->add(new DateInterval("PT" . $i . "S"));
                    break;
            }
            $string = str_replace($found[0][$k], "", $string);
        }
        return [$t, ltrim(str_replace($found[0], "", $string))];
    }

    /**
     * @param string $string
     * @param DateTime $time
     * @return array|null
     * @throws Exception
     */
    public function stringToTimestampReduce(string $string, DateTime $time): ?array
    {
        /**
         * Rules:
         * Integers without suffix are considered as seconds
         * "s" is for seconds
         * "m" is for minutes
         * "h" is for hours
         * "d" is for days
         * "w" is for weeks
         * "mo" is for months
         * "y" is for years
         */
        if (trim($string) === "") {
            return null;
        }
        $t = $time;
        preg_match_all("/[0-9]+(y|mo|w|d|h|m|s)|[0-9]+/", $string, $found);
        if (count($found[0]) < 1) {
            return null;
        }
        $found[2] = preg_replace("/[^0-9]/", "", $found[0]);
        foreach ($found[2] as $k => $i) {
            switch ($c = $found[1][$k]) {
                case "y":
                case "w":
                case "d":
                    $t->sub(new DateInterval("P" . $i . strtoupper($c)));
                    break;
                case "mo":
                    $t->sub(new DateInterval("P" . $i . strtoupper(substr($c, 0, strlen($c) - 1))));
                    break;
                case "h":
                case "m":
                case "s":
                    $t->sub(new DateInterval("PT" . $i . strtoupper($c)));
                    break;
                default:
                    $t->sub(new DateInterval("PT" . $i . "S"));
                    break;
            }
            $string = str_replace($found[0][$k], "", $string);
        }
        return [$t, ltrim(str_replace($found[0], "", $string))];
    }
}