<?php

namespace Nikitinuser\TypeSniff\Core\Type\DocBlock;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class ClassStringType implements TypeInterface
{
    public function toString(): string
    {
        return 'class-string';
    }
}
