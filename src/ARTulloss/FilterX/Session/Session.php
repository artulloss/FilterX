<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/10/2019
 * Time: 10:59 PM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Session;

use Exception;
use pocketmine\Player;
use function array_keys;
use function array_sum;

/**
 * Class Session
 * Constructed when a player talks in the chat-
 * their individualized data gets stored in these
 * @package ARTulloss\FilterX
 */
class Session {
    /** @var string $lastMessage */
    private $lastMessage;
    /** @var int|null $softMuted */
    private $softMuted;
    /** @var int[] $infractions */
    private $infractions;
    /** @var $player */
    private $player;
    /**
     * Session constructor.
     * @param Player $player
     * @param string $lastMessage
     */
    public function __construct(Player $player, $lastMessage) {
        $this->player = $player;
        $this->lastMessage = $lastMessage;
    }
    /**
     * Set soft mute length
     * @param null|int $timeStamp Null will unmute
     * @throws Exception
     */
    public function setSoftMutedFor(?int $timeStamp): void{
        $this->softMuted = $timeStamp;
    }
    /**
     * Add to soft mute length
     * @param int $timeStamp
     */
    public function addToSoftMutedTime(int $timeStamp): void{
    //    echo "\nADDED $timeStamp SECONDS\n";
        $this->softMuted = ($this->softMuted ?? time()) + $timeStamp;
    }
    public function unSoftMute(): void{
        $this->softMuted = null;
    }
    /**
     * @return bool
     */
    public function isSoftMuted(): bool{
        return ($this->softMuted ?? 0) > time();
    }
    /**
     * @return null|int
     */
    public function getSoftMutedUntil(): ?int{
        return $this->softMuted;
    }
    /**
     * @param string $lastMessage
     */
    public function setLastMessage(string $lastMessage): void{
    //    echo "\nSET LAST MESSAGE\n";
        $this->lastMessage = $lastMessage;
    }
    /**
     * @return string
     */
    public function getLastMessage(): string{
        return $this->lastMessage;
    }
    /**
     * @param int $by
     */
    public function incrementInfractions($by = 1): void{
    //    echo "\nINFRACTIONS INCREMENTED\n";
        if($by === 0)
            return;
        $time = time();
        isset($this->infractions[$time])
            ? $this->infractions[$time] += $by
            : $this->infractions[$time] = $by;
    }
    public function removeInfractions(int $count): void{
        foreach ($this->infractions as $key => $infractions) {
            if($infractions < $count) {
                unset($this->infractions[$key]);
                $count -= $infractions;
            } else {
                $this->infractions[$key] -= $count;
                break;
            }
        }
    }
    /**
     * @param int $expireTime
     */
    public function removeExpiredInfractions(int $expireTime): void{
    //    echo "\nINFRACTIONS RESET\n";
        $now = time();
        foreach (array_keys((array)$this->infractions) as $time) {
            if($now - $time > $expireTime) {
                unset($this->infractions[$time]);
            }
        }
    }
    /**
     * @return int
     */
    public function getInfractions(): int{
        return array_sum($this->infractions ?? []);
    }
    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }
}