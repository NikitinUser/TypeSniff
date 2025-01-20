<?php

namespace Nikitinuser\TypeSniff\Core\Type\Common;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class NullType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'null';
    }
}
