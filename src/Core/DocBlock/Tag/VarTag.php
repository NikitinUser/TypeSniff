<?php

namespace Nikitinuser\TypeSniff\Core\DocBlock\Tag;

use Nikitinuser\TypeSniff\Core\Type\TypeInterface;

class VarTag implements TagInterface
{
    public function __construct(
        protected int $line,
        protected TypeInterface $type,
        protected ?string $paramName,
        protected ?string $description,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLine(): int
    {
        return $this->line;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }

    public function getParamName(): ?string
    {
        return $this->paramName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'var';
    }
}
