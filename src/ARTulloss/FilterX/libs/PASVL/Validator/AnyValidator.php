<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 02/01/2018
 */

namespace ARTulloss\FilterX\libs\PASVL\Validator;


class AnyValidator extends Validator
{
    public function __invoke($data): bool
    {
        return true;
    }

}