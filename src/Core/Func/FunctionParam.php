<?php

namespace Nikitinuser\TypeSniff\Core\Func;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

/**
 * @see FunctionParamTest
 */
class FunctionParam
{
    public function __construct(
        protected int $line,
        protected string $name,
        protected TypeInterface $type,
        protected ?TypeInterface $valueType,
        /** @var string[] */
        protected array $attributeNames,
        protected ?DocBlock $docBlock = null, // relevant for promoted props
        protected bool $promotedProp = false
    ) {
        $this->docBlock ??= new UndefinedDocBlock();
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        return $this->attributeNames;
    }

    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    public function isPromotedProp(): bool
    {
        return $this->promotedProp;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return in_array($attributeName, $this->attributeNames);
    }
}
