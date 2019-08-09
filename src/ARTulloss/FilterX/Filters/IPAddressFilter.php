<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 8/3/2019
 * Time: 10:12 AM
 */
declare(strict_types=1);

namespace ARTulloss\FilterX\Filters;

use const FILTER_FLAG_IPV4;
use const FILTER_VALIDATE_IP;
use function filter_var;
use function explode;

class IPAddressFilter extends BaseFilter {
    /**
     * @param string $sentence
     * @return int
     */
    public function countViolations(string $sentence): int{
        $infractions = $this->getInfractions();
        if($infractions === 0)
            return 0;
        foreach (explode(' ', $sentence) as $word) {
            if(filter_var($word, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
                return $infractions;
        }
        return 0;
    }
}