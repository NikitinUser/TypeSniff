<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;

class InterfaceElement extends AbstractFqcnElement
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames = [],
        /** @var InterfaceConstElement[] */
        protected array $constants = [],
        /** @var InterfaceMethodElement[] */
        protected array $methods = [],
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames);
    }

    /**
     * @return InterfaceConstElement[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return InterfaceMethodElement[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function addConstant(InterfaceConstElement $constant): void
    {
        $this->constants[] = $constant;
    }

    public function addMethod(InterfaceMethodElement $method): void
    {
        $this->methods[] = $method;
    }
}
