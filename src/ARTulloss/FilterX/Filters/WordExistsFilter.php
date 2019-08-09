<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 8/3/2019
 * Time: 10:07 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Filters;

use ARTulloss\FilterX\Utils;
use function strtolower;

class WordExistsFilter extends BaseFilter {
    public const MODE_PER_WORD = 1;
    public const MODE_PER_MESSAGE = 2;
    /** @var int $mode */
    private $mode;
    /** @var string[] $filteredWords */
    private $filteredWords;
    /**
     * WordExistsFilter constructor.
     * @param string $mode
     * @param string[] $filteredWords
     * @param int|null $infractions
     */
    public function __construct(string $mode, array $filteredWords, ?int $infractions = null) {
        $this->mode = strtolower($mode) === 'count' ? self::MODE_PER_WORD : self::MODE_PER_MESSAGE;
        $this->filteredWords = $filteredWords;
        parent::__construct($infractions);
    }
    /**
     * @param string $sentence
     * @return int
     */
    public function countViolations(string $sentence): int{
        return $this->mode === self::MODE_PER_WORD ? Utils::array_substr_count($this->filteredWords, $sentence) : (int) Utils::striExists($this->filteredWords, $sentence);
    }
}