<?php

namespace Nikitinuser\TypeSniff\Inspection\Subject;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Nikitinuser\TypeSniff\Core\Func\FunctionSignature;
use Nikitinuser\TypeSniff\Core\Type\Common\UndefinedType;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class ReturnTypeSubject extends AbstractTypeSubject
{
    /**
     * @param string[] $attributeNames
     */
    public function __construct(
        ?TypeInterface $docType,
        TypeInterface $fnType,
        ?int $docTypeLine,
        int $fnTypeLine,
        string $name,
        DocBlock $docBlock,
        array $attributeNames,
        string $id,
    ) {
        parent::__construct(
            $docType,
            $fnType,
            new UndefinedType(), // return does not have an assignment
            $docTypeLine,
            $fnTypeLine,
            $name,
            $docBlock,
            $attributeNames,
            $id,
        );
    }

    /**
     * @param FunctionSignature $fnSig
     * @param ReturnTag|null    $returnTag
     * @param DocBlock          $docBlock
     * @param string            $id
     * @param string[]          $attributeNames
     *
     * @return static
     */
    public static function fromSignature(
        FunctionSignature $fnSig,
        ?ReturnTag $returnTag,
        DocBlock $docBlock,
        array $attributeNames,
        string $id,
    ): static {
        return new static(
            $returnTag?->getType(),
            $fnSig->getReturnType(),
            $returnTag ? $returnTag->getLine() : $fnSig->getReturnLine(),
            $fnSig->getReturnLine(),
            'return value',
            $docBlock,
            $attributeNames,
            $id,
        );
    }
}
