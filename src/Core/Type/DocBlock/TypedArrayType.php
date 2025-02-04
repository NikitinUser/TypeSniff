<?php

namespace Nikitinuser\TypeSniff\Core\Type\DocBlock;

use Nikitinuser\TypeSniff\Core\Type\Common\UnionType;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class TypedArrayType implements TypeInterface
{
    public function __construct(
        protected TypeInterface $type,
        protected int $depth
    ) {
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $innerType = $this->type->toString();
        if ($this->type instanceof UnionType) {
            $innerType = sprintf('(%s)', $innerType);
        }

        return $innerType . str_repeat('[]', $this->depth);
    }
}
