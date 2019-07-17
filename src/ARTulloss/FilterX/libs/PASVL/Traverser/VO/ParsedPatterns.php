<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/02/2018
 */
declare(strict_types=1);


namespace ARTulloss\FilterX\libs\PASVL\Traverser\VO;

use ARTulloss\FilterX\libs\PASVL\Pattern\Pattern;

/**
 * A storage to save parsed pattern instances
 * @package ARTulloss\FilterX\libs\PASVL\Traverser\VO
 */
class ParsedPatterns
{
    private $patterns = [];

    function put($pattern)
    {
        if (!isset($this->patterns[$pattern])) {
            $this->patterns[$pattern] = new Pattern($pattern, null, true);
        }
    }

    function has($pattern): bool
    {
        return array_key_exists($pattern, $this->patterns);
    }

    function get($pattern): Pattern
    {
        if (!$this->has($pattern)) {
            $this->put($pattern);
        }

        return $this->patterns[$pattern];
    }
}