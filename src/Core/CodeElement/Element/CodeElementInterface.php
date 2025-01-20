<?php

namespace Nikitinuser\TypeSniff\Core\CodeElement\Element;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;

interface CodeElementInterface
{
    public function getLine(): int;

    public function getDocBlock(): DocBlock;

    /**
     * @return string[]
     */
    public function getAttributeNames(): array;
}
