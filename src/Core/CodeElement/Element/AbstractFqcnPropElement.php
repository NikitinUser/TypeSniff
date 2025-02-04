<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

abstract class AbstractFqcnPropElement extends AbstractFqcnElement
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        protected string $propName,
        protected TypeInterface $type,
        protected ?TypeInterface $defaultValueType,
        protected bool $promoted
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
    }

    public function getPropName(): string
    {
        return $this->propName;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getDefaultValueType(): ?TypeInterface
    {
        return $this->defaultValueType;
    }

    public function isPromoted(): bool
    {
        return $this->promoted;
    }
}
