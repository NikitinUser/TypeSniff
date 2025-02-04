<?php

namespace Nikitinuser\TypeSniff\Core\Type\Declaration;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class NullableType implements TypeInterface
{
    public function __construct(
        protected TypeInterface $type
    ) {
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function containsType(string $typeClassName): bool
    {
        return is_a($this->type, $typeClassName);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return '?' . $this->type->toString();
    }

    public function toDocString(): string
    {
        $rawType = $this->type->toString();

        // This must match sorting in UnionType::toString() for raw comparisons.
        return $rawType > 'null' ? 'null|' . $rawType : $rawType . '|null';
    }
}
