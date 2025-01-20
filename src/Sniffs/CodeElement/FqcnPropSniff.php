<?php

namespace Nikitinuser\TypeSniff\Sniffs\CodeElement;

use Nikitinuser\TypeSniff\Core\CodeElement\Element\AbstractFqcnElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\ClassElement;
use Nikitinuser\TypeSniff\Core\ReflectionCache;
use Nikitinuser\TypeSniff\Inspection\FnTypeInspector;
use Nikitinuser\TypeSniff\Inspection\Subject\PropTypeSubject;
use PHP_CodeSniffer\Files\File;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\AbstractFqcnPropElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\ClassPropElement;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\CodeElementInterface;
use Nikitinuser\TypeSniff\Core\CodeElement\Element\TraitPropElement;
use Throwable;

class FqcnPropSniff implements CodeElementSniffInterface
{
    protected const CODE = 'FqcnPropSniff';

    protected string $reportType = 'warning';
    protected bool $addViolationId = true;
    protected bool $inspectPcpAsProp = true;

    /**
     * @inheritDoc
     */
    public function configure(array $config): void
    {
        $this->reportType = (string)($config['reportType'] ?? 'warning');
        $this->addViolationId = (bool)($config['addViolationId'] ?? true);
        $this->inspectPcpAsProp = 'prop' === ($config['inspectPromotedConstructorPropertyAs'] ?? 'prop');
    }

    /**
     * @inheritDoc
     */
    public function register(): array
    {
        return [
            ClassPropElement::class,
            TraitPropElement::class,
        ];
    }

    /**
     * @inheritDoc
     *
     * @param AbstractFqcnPropElement $prop
     * @param AbstractFqcnElement     $parentElement
     */
    public function process(File $file, CodeElementInterface $prop, CodeElementInterface $parentElement): void
    {
        $subject = PropTypeSubject::fromElement($prop);

        if (!$this->inspectPcpAsProp && $prop->isPromoted()) {
            // ask to remove inline doc block = treat as function param
            $this->reportUnneededInlineDocBlock($subject);
            $subject->writeViolationsTo($file, static::CODE, $this->reportType, $this->addViolationId);
            return;
        }

        // Do not report required fn type if prop is extended. To do this, reflection is needed.
        // If prop has fn type or class is not extended, then there is no point in checking for parent props, skip.
        $skipRequireFnType = false;
        if (
            !$subject->hasDefinedFnType()
            && $parentElement instanceof ClassElement
            && $parentElement->isExtended()
        ) {
            // Extended class = prop may be extended.
            try {
                $parentPropNames = ReflectionCache::getPropsRecursive($parentElement->getFqcn(), false);
                $skipRequireFnType = in_array($prop->getPropName(), $parentPropNames); // is prop extended?
            } catch (Throwable) {
                $skipRequireFnType = true; // most likely parent class not found, don't report
            }
        }

        if (!$skipRequireFnType) {
            FnTypeInspector::reportSuggestedTypes($subject);
        }

        $subject->writeViolationsTo($file, static::CODE, $this->reportType, $this->addViolationId);
    }

    protected function reportUnneededInlineDocBlock(PropTypeSubject $subject): void
    {
        if ($subject->hasDefinedDocBlock()) {
            $subject->addDocTypeWarning(
                'Promoted constructor property is configured to be documented using __construct() PHPDoc block as param, '
                . 'remove inline @var tag'
            );
        }
    }
}
