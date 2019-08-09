<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 8/3/2019
 * Time: 9:54 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Filters;

abstract class BaseFilter {
    /** @var int|null */
    private $infractions;
    /**
     * BaseFilter constructor.
     * @param int|null $infractions
     */
    public function __construct(int $infractions = null) {
        $this->infractions = $infractions;
    }
    /**
     * This function should run the filter and return the amount of violations it should be worth
     * This number will only be used if the infractions property is null
     * @param string $sentence
     * @return int
     */
    abstract public function countViolations(string $sentence): int;
    /**
     * @return int|null
     */
    public function getInfractions(): ?int{
        return $this->infractions;
    }
    /**
     * @param string $sentence
     * @return bool
     */
    public function isFiltered(string $sentence): bool{
        return $this->countViolations($sentence) > 0;
    }
}