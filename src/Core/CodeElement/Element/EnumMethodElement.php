<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\EnumMethodMetadata;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\InterfaceMethodMetadata;
use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Func\FunctionSignature;

class EnumMethodElement extends AbstractFqcnMethodElement
{
    protected EnumMethodMetadata $metadata;

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
        $this->metadata = $metadata ?? new EnumMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): EnumMethodMetadata
    {
        return $this->metadata;
    }
}
