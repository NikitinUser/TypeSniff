<?php

namespace Nikitinuser\TypeSniff\Inspection\Subject;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlock;
use Nikitinuser\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Nikitinuser\TypeSniff\Core\SniffHelper;
use Nikitinuser\TypeSniff\Core\Type\Common\UndefinedType;
use Nikitinuser\TypeSniff\Core\Type\TypeInterface;
use PHP_CodeSniffer\Files\File;

/**
 * @see AbstractTypeSubjectTest
 */
abstract class AbstractTypeSubject
{
    /** @var string[] */
    protected array $docTypeWarnings = [];
    /** @var string[] */
    protected array $fnTypeWarnings = [];

    public function __construct(
        protected ?TypeInterface $docType, // null = missing in PHPDoc
        protected TypeInterface $fnType,
        protected ?TypeInterface $valueType, // null = could not be detected
        protected ?int $docTypeLine, // null = missing in PHPDoc
        protected int $fnTypeLine,
        protected string $name, // "parameter $param1", "property $prop1", "constant CONST1"
        protected DocBlock $docBlock,
        /** @var string[] */
        protected array $attributeNames,
        protected string $id, // TestClass::method1(), etc.
    ) {
    }

    public function getDocType(): ?TypeInterface
    {
        return $this->docType;
    }

    public function getFnType(): TypeInterface
    {
        return $this->fnType;
    }

    public function getValueType(): ?TypeInterface
    {
        return $this->valueType;
    }

    public function getDocTypeLine(): ?int
    {
        return $this->docTypeLine;
    }

    public function getFnTypeLine(): int
    {
        return $this->fnTypeLine;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDocBlock(): DocBlock
    {
        return $this->docBlock;
    }

    /**
     * @return string[]
     */
    public function getDocTypeWarnings(): array
    {
        return $this->docTypeWarnings;
    }

    /**
     * @return string[]
     */
    public function getFnTypeWarnings(): array
    {
        return $this->fnTypeWarnings;
    }

    public function hasDefinedDocType(): bool
    {
        return $this->docType && !($this->docType instanceof UndefinedType);
    }

    public function hasDefinedFnType(): bool
    {
        return !($this->fnType instanceof UndefinedType);
    }

    public function hasDefinedDocBlock(): bool
    {
        return !($this->docBlock instanceof UndefinedDocBlock);
    }

    public function addDocTypeWarning(string $warning): void
    {
        $this->docTypeWarnings[] = $warning;
    }

    public function addFnTypeWarning(string $warning): void
    {
        $this->fnTypeWarnings[] = $warning;
    }

    public function writeViolationsTo(File $file, string $sniffCode, string $reportType, bool $addViolationId): void
    {
        $originId = $addViolationId ? $this->getId() : null;

        $ucName = ucfirst($this->name);
        foreach ($this->docTypeWarnings as $docTypeWarning) {
            $warning = str_replace([':subject:', ':Subject:'], [$this->name, $ucName], $docTypeWarning);
            SniffHelper::addViolation($file, $warning, $this->docTypeLine ?? $this->fnTypeLine, $sniffCode, $reportType, $originId);
        }

        foreach ($this->fnTypeWarnings as $fnTypeWarning) {
            $warning = str_replace([':subject:', ':Subject:'], [$this->name, $ucName], $fnTypeWarning);
            SniffHelper::addViolation($file, $warning, $this->fnTypeLine, $sniffCode, $reportType, $originId);
        }
    }

    public function hasDocTypeTag(): bool
    {
        return null !== $this->docType;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return in_array($attributeName, $this->attributeNames);
    }
}
