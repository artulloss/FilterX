<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 7/11/2019
 * Time: 1:15 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX;

use function time;

/**
 * Class Timer
 * Simple timer class making use of
 * the time() function for simplicity
 * @package ARTulloss\FilterX
 */
class Timer {
    /** @var int $seconds */
    private $seconds;
    /** @var int $start */
    private $start;
    /** @var int $state */
    private $state = self::STATE_STOPPED;

    public const STATE_STARTED = 1;
    public const STATE_STOPPED = 0;

    /**
     * Timer constructor.
     * @param int $seconds
     */
    public function __construct(int $seconds) {
        $this->seconds = $seconds;
    }
    public function start(): void{
        $this->start = time();
        $this->state = self::STATE_STARTED;
    }
    public function stop(): void{
        $this->state = self::STATE_STOPPED;
    }
    public function isDone(): bool{
        $done = time() - $this->start >= $this->seconds;
        if($done)
            $this->stop();
        return $done;
    }
    /**
     * @return int
     */
    public function getState(): int{
        return $this->state;
    }
}