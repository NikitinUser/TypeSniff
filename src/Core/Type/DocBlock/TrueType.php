<?php

namespace Nikitinuser\TypeSniff\Core\Type\DocBlock;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class TrueType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'true';
    }
}
