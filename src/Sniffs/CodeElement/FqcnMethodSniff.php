<?php

namespace Nikitinuser\TypeSniff\Sniffs\CodeElement;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\AbstractFqcnMethodElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\ClassElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\ClassMethodElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\EnumMethodElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\InterfaceMethodElement;
use Nikitinuser\TypeSniff\Core\DocBlock\Tag\VarTag;
use Nikitinuser\TypeSniff\Core\SniffHelper;
use Nikitinuser\TypeSniff\Core\Type\Common\MixedType;
use Nikitinuser\TypeSniff\Core\Type\Common\NullType;
use Nikitinuser\TypeSniff\Core\Type\Common\UndefinedType;
use Nikitinuser\TypeSniff\Core\Type\Declaration\NullableType;
use Nikitinuser\TypeSniff\Core\Type\TypeHelper;
use Nikitinuser\TypeSniff\Inspection\FnTypeInspector;
use Nikitinuser\TypeSniff\Inspection\Subject\AbstractTypeSubject;
use Nikitinuser\TypeSniff\Inspection\Subject\ParamTypeSubject;
use Nikitinuser\TypeSniff\Inspection\Subject\ReturnTypeSubject;
use PHP_CodeSniffer\Files\File;

/**
 * @see FqcnMethodSniffTest
 */
class FqcnMethodSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnMethodSniff';

    /** @var string[] */
    protected array $invalidTags = [];
    protected bool $reportMissingTags = false;
    protected bool $reportNullableBasicGetter = true;
    protected string $reportType = 'warning';
    protected bool $addViolationId = true;
    protected bool $inspectPcpAsParam = false;
    protected bool $requireInheritDoc = false;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        // TagInterface uses lowercase tags names, no @ symbol in front
        $invalidTags = [];
        foreach ($config['invalidTags'] ?? [] as $rawTag) {
            $invalidTags[] = strtolower(ltrim($rawTag, '@'));
        }
        $invalidTags = array_unique($invalidTags);

        $this->invalidTags = $invalidTags;
        $this->reportMissingTags = (bool)($config['reportMissingTags'] ?? false);
        $this->reportNullableBasicGetter = (bool)($config['reportNullableBasicGetter'] ?? true);
        $this->reportType = (string)($config['reportType'] ?? 'warning');
        $this->addViolationId = (bool)($config['addViolationId'] ?? true);
        $this->inspectPcpAsParam = 'param' === ($config['inspectPromotedConstructorPropertyAs'] ?? 'prop');
        $this->requireInheritDoc = (bool)($config['requireInheritDoc'] ?? false);
    }

    /**
     * @inheritDoc
     */
    public function register(): array
    {
        return [
            ClassMethodElement::class,
            // TraitMethodElement::class, // can be used to implement interface, not possible to know if it is extended
            InterfaceMethodElement::class,
            EnumMethodElement::class,
        ];
    }

    /**
     * @inheritDoc
     * @param AbstractFqcnMethodElement $method
     */
    public function process(File $file, CodeElementInterface $method, CodeElementInterface $parentElement): void
    {
        $this->reportInvalidTags($file, $method, $this->invalidTags);
        $this->processMethod($file, $method, $parentElement);
    }

    protected function processMethod(File $file, AbstractFqcnMethodElement $method, CodeElementInterface $parent): void
    {
        $fnSig = $method->getSignature();
        $docBlock = $method->getDocBlock();
        $isMagicMethod = str_starts_with($fnSig->getName(), '__');
        $isConstructMethod = '__construct' === $fnSig->getName();
        $hasInheritDocTag = $docBlock->hasTag('inheritdoc');

        // @inheritDoc
        // __construct can be detected as extended and magic, but we want to inspect it anyway
        if (!$isConstructMethod) {
            if ($hasInheritDocTag || $isMagicMethod) {
                return;
            } elseif ($method->getMetadata()->isExtended() && $this->requireInheritDoc) {
                $originId = $this->addViolationId ? $method->getId() : null;
                SniffHelper::addViolation($file, 'Missing @inheritDoc tag. Remove duplicated parent PHPDoc content.', $method->getLine(), static::CODE, $this->reportType, $originId);
                return;
            }
        }

        // @param
        foreach ($fnSig->getParams() as $fnParam) {
            $paramTag = $docBlock->getParamTag($fnParam->getName());
            $id = $method->getId() . $fnParam->getName();
            $subject = ParamTypeSubject::fromParam($fnParam, $paramTag, $docBlock, $id);
            $this->processParam($subject);
            $subject->writeViolationsTo($file, static::CODE, $this->reportType, $this->addViolationId);
        }

        // @return
        if (!$isConstructMethod) {
            $returnTag = $docBlock->getReturnTag();
            $subject = ReturnTypeSubject::fromSignature($fnSig, $returnTag, $docBlock, $method->getAttributeNames(), $method->getId());
            $this->processParam($subject);
            if ($method instanceof ClassMethodElement && $parent instanceof ClassElement) {
                $this->reportNullableBasicGetter && $this->reportNullableBasicGetter($subject, $method, $parent);
            }
            $subject->writeViolationsTo($file, static::CODE, $this->reportType, $this->addViolationId);
        } else {
            foreach ($docBlock->getDescriptionLines() as $lineNum => $descLine) {
                if (preg_match('#^\w+\s+constructor\.?$#', $descLine)) {
                    $originId = $this->addViolationId ? $method->getId() : null;
                    SniffHelper::addViolation($file, 'Useless description.', $lineNum, static::CODE, $this->reportType, $originId);
                }
            }
        }
    }

    protected function processParam(AbstractTypeSubject $subject): void
    {
        FnTypeInspector::reportSuggestedTypes($subject);
        FnTypeInspector::reportReplaceableTypes($subject);
    }

    /**
     * @param File                      $file
     * @param AbstractFqcnMethodElement $method
     * @param string[]                  $invalidTags
     */
    protected function reportInvalidTags(File $file, AbstractFqcnMethodElement $method, array $invalidTags): void
    {
        foreach ($method->getDocBlock()->getTags() as $tag) {
            foreach ($invalidTags as $invalidTagName) {
                if ($tag->getName() === $invalidTagName) {
                    $originId = $this->addViolationId ? $method->getId() . $invalidTagName : null;
                    SniffHelper::addViolation($file, 'Useless tag', $tag->getLine(), static::CODE, $this->reportType, $originId);
                }
            }
        }
    }

    protected function reportNullableBasicGetter(
        ReturnTypeSubject $subject,
        ClassMethodElement $method,
        ClassElement $class,
    ): void {
        $propName = $method->getMetadata()->getBasicGetterPropName();
        if (null === $propName) {
            return;
        }

        $prop = $class->getProperty($propName);
        if (null === $prop) {
            return;
        }

        /** @var VarTag|null $varTag */
        $varTag = $prop->getDocBlock()->getTagsByName('var')[0] ?? null;
        if (null === $varTag) {
            return;
        }

        $propDocType = $varTag->getType();
        $isPropNullable = TypeHelper::containsType($varTag->getType(), NullType::class);
        if (!$isPropNullable) {
            return;
        }

        $returnDocType = $subject->getDocType();
        $isGetterDocTypeNullable = TypeHelper::containsType($returnDocType, NullType::class);
        if ($returnDocType || $this->reportMissingTags) {
            if (!$isGetterDocTypeNullable && $subject->hasDefinedDocBlock()) {
                $subject->addDocTypeWarning(sprintf(
                    'Returned property $%s is nullable, add null return doc type, e.g. %s',
                    $propName,
                    $propDocType->toString(),
                ));
            }
        }

        // Only report in fn type is defined. Doc type and fn type is synced by other sniffs.
        $returnFnType = $subject->getFnType();
        if (
            !($returnFnType instanceof UndefinedType) &&
            !($returnFnType instanceof NullableType) &&
            !($returnFnType instanceof MixedType)
        ) {
            $subject->addFnTypeWarning(sprintf(
                'Returned property $%s is nullable, use nullable return type declaration, e.g. ?%s',
                $propName,
                $returnFnType->toString(),
            ));
        }
    }
}
