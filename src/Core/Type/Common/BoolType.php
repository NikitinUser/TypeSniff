<?php

namespace Nikitinuser\TypeSniff\Core\Type\Common;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class BoolType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'bool';
    }
}
