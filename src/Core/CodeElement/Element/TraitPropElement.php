<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\Metadata\TraitPropMetadata;
use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\Func\FunctionParam;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class TraitPropElement extends AbstractFqcnPropElement
{
    protected TraitPropMetadata $metadata;

    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        int $line,
        DocBlock $docBlock,
        string $fqcn,
        array $attributeNames,
        string $propName,
        TypeInterface $type,
        ?TypeInterface $defaultValueType,
        bool $promoted,
        ?TraitPropMetadata $metadata = null,
    ) {
        parent::__construct($line, $docBlock, $fqcn, $attributeNames, $propName, $type, $defaultValueType, $promoted);
        $this->metadata = $metadata ?? new TraitPropMetadata();
    }

    public static function fromFunctionParam(FunctionParam $param, string $fqcn): static
    {
        return new static(
            $param->getLine(),
            $param->getDocBlock(),
            $fqcn,
            $param->getAttributeNames(),
            $param->getName(),
            $param->getType(),
            $param->getValueType(),
            $param->isPromotedProp()
        );
    }

    public function getMetadata(): TraitPropMetadata
    {
        return $this->metadata;
    }
}
