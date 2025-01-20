<?php

namespace Nikitinuser\TypeSniff\Core\Type\DocBlock;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class ResourceType implements TypeInterface
{
    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return 'resource';
    }
}
