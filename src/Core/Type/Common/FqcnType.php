<?php

namespace Nikitinuser\TypeSniff\Core\Type\Common;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class FqcnType implements TypeInterface
{
    public function __construct(
        protected string $fqcn,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return $this->fqcn;
    }
}
