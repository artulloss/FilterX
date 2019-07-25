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
use pocketmine\Player\Player;
use function time;

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
    /** @var int $infractions */
    private $infractions;
    /** @var int[] $alreadyPunishedInfractions */
    private $alreadyPunishedInfractions = [];
    /** @var $player */
    private $player;
    /** @var int $id */
    private $id;
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
    public function addToAlreadyPunishedInfraction(int $threshold): void{
        $this->alreadyPunishedInfractions[$threshold] = true;
    }
    /**
     * @param int $threshold
     * @return bool
     */
    public function hasBeenPunishedAtThreshold(int $threshold): bool{
        return isset($this->alreadyPunishedInfractions[$threshold]);
    }
    public function resetPunishedAtThreshold(): void{
        $this->alreadyPunishedInfractions = [];
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
    //    var_dump($by);
        $this->infractions += $by;
    }
    public function resetInfractions(): void{
    //    echo "\nINFRACTIONS RESET\n";
        $this->infractions = 0;
    }
    /**
     * @return int
     */
    public function getInfractions(): int{
        return $this->infractions ?? 0;
    }
    /**
     * Must be called after creation
     * @param int $id
     */
    public function setId(int $id): void{
        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId(): ?int{
        return $this->id;
    }
    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }
}