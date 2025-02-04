<?php

namespace Nikitinuser\TypeSniff\Inspection\Subject;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Nikitinuser\TypeSniff\Core\DocBlock\Tag\VarTag;

class PropTypeSubject extends AbstractTypeSubject
{
    public static function fromElement(AbstractFqcnPropElement $prop): static
    {
        $docBlock = $prop->getDocBlock();

        /** @var VarTag|null $varTag */
        $varTag = $docBlock->getTagsByName('var')[0] ?? null;

        return new static(
            $varTag?->getType(),
            $prop->getType(),
            $prop->getDefaultValueType(),
            $varTag ? $varTag->getLine() : $prop->getLine(),
            $prop->getLine(),
            'property $' . $prop->getPropName(),
            $docBlock,
            $prop->getAttributeNames(),
            $prop->getFqcn() . '::' . $prop->getPropName(),
        );
    }
}
