<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\TraitMethodMetadata;
use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Func\FunctionSignature;

class TraitMethodElement extends AbstractFqcnMethodElement
{
    protected TraitMethodMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        FunctionSignature $signature,
        ?TraitMethodMetadata $metadata = null,
    ) {
        parent::__construct($docBlock, $fqcn, $attributeNames, $signature);
        $this->metadata = $metadata ?? new TraitMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): TraitMethodMetadata
    {
        return $this->metadata;
    }
}
