<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace ARTulloss\FilterX\libs\PASVL\Validator;


class FloatValidator extends NumberValidator
{
    /** @var boolean */
    protected $skipValidation;

    public function __invoke($data, string $nullable = "false"): bool
    {
        $nullable = $this->convertStringToBool($nullable);
        $this->skipValidation = is_null($data) && $nullable;

        return $this->skipValidation || is_float($data);
    }
}