<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\InterfaceMethodMetadata;
use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Func\FunctionSignature;

class InterfaceMethodElement extends AbstractFqcnMethodElement
{
    protected InterfaceMethodMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        FunctionSignature $signature,
        ?InterfaceMethodMetadata $metadata = null,
    ) {
        parent::__construct($docBlock, $fqcn, $attributeNames, $signature);
        $this->metadata = $metadata ?? new InterfaceMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): InterfaceMethodMetadata
    {
        return $this->metadata;
    }
}
