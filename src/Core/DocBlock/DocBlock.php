<?php

namespace Nikitinuser\TypeSniff\Core\DocBlock;

use Nikitinuser\TypeSniff\Core\DocBlock\Tag\ParamTag;
use Nikitinuser\TypeSniff\Core\DocBlock\Tag\ReturnTag;
use Nikitinuser\TypeSniff\Core\DocBlock\Tag\TagInterface;

/**
 * @see DocBlockTest
 */
class DocBlock
{
    public function __construct(
        /** @var string[] [ptr => string, ...] */
        protected array $descriptionLines,
        /** @var TagInterface[] */
        protected array $tags = [],
    ) {
    }

    /**
     * @return string[] [ptr => string, ...]
     */
    public function getDescriptionLines(): array
    {
        return $this->descriptionLines;
    }

    public function hasDescription(): bool
    {
        return !empty($this->descriptionLines);
    }

    public function getDescription(): ?string
    {
        return $this->descriptionLines ? implode("\n", $this->descriptionLines) : null;
    }

    /**
     * @return TagInterface[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function hasTag(string $tagName): bool
    {
        foreach ($this->tags as $tag) {
            if ($tag->getName() === $tagName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $tagNames
     *
     * @return bool
     */
    public function hasOneOfTags(array $tagNames): bool
    {
        foreach ($this->tags as $tag) {
            if (in_array($tag->getName(), $tagNames)) {
                return true;
            }
        }

        return false;
    }

    public function getReturnTag(): ?ReturnTag
    {
        foreach ($this->tags as $tag) {
            if ($tag instanceof ReturnTag) {
                return $tag;
            }
        }

        return null;
    }

    /**
     * @return ParamTag[]
     */
    public function getParamTags(): array
    {
        return $this->getTagsByName('param');
    }

    public function getParamTag(string $paramName): ?ParamTag
    {
        foreach ($this->tags as $tag) {
            if ($tag instanceof ParamTag) {
                if ($tag->getParamName() === $paramName) {
                    return $tag;
                }
            }
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return TagInterface[]
     */
    public function getTagsByName(string $name): array
    {
        $matchingTags = [];
        foreach ($this->tags as $tag) {
            if ($tag->getName() === $name) {
                $matchingTags[] = $tag;
            }
        }

        return $matchingTags;
    }

    /**
     * @return string[]
     */
    public function getTagNames(): array
    {
        $tagNames = [];
        foreach ($this->tags as $tag) {
            $tagNames[$tag->getName()] = null;
        }

        return array_keys($tagNames);
    }
}
