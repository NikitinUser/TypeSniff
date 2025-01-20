<?php

namespace Nikitinuser\TypeSniff\Core\DocBlock\Tag;

interface TagInterface
{
    public function getLine(): int;

    public function getName(): string;
}
