<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\ClassMethodMetadata;
use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Func\FunctionSignature;

class ClassMethodElement extends AbstractFqcnMethodElement
{
    protected ClassMethodMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        FunctionSignature $signature,
        ?ClassMethodMetadata $metadata = null,
    ) {
        parent::__construct($docBlock, $fqcn, $attributeNames, $signature);
        $this->metadata = $metadata ?? new ClassMethodMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(): ClassMethodMetadata
    {
        return $this->metadata;
    }
}
