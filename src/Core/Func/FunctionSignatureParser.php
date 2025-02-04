<?php

namespace Nikitinuser\TypeSniff\Core\Func;

use Nikitinuser\TypeSniff\Core\DocBlock\DocBlockParser;
use Nikitinuser\TypeSniff\Core\DocBlock\UndefinedDocBlock;
use Nikitinuser\TypeSniff\Core\TokenHelper;
use Nikitinuser\TypeSniff\Core\Type\Common\FqcnType;
use PHP_CodeSniffer\Files\File;
use RuntimeException;
use Nikitinuser\TypeSniff\Core\Type\TypeFactory;

/**
 * @see FunctionSignatureParserTest
 */
class FunctionSignatureParser
{
    public static function fromTokens(File $file, int $fnPtr): FunctionSignature
    {
        /** @see File::getMethodParameters() */
        /** @see File::getMethodProperties() */

        $tokens = $file->getTokens();

        $fnName = null;
        $fnNameLine = null;
        $returnLine = null;

        $ptr = $fnPtr + 1; // skip T_WHITESPACE
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];
            switch ($token['code']) {
                case T_STRING:
                    $fnName = $token['content'];
                    $fnNameLine = $token['line'];
                    break;
                case T_OPEN_PARENTHESIS:
                    break 2;
            }
        }
        if (null === $fnName) {
            throw new RuntimeException('Expected to find function name');
        }

        /** @see https://www.php.net/manual/en/tokens.php */
        /** @var FunctionParam[] $params */
        $params = [];
        $raw = [];
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];

            switch ($token['code']) {
                case T_CONSTANT_ENCAPSED_STRING:
                    $raw['default'] = 'string';
                    break;
                case T_LNUMBER:
                    $raw['default'] = 'int';
                    break;
                case T_DNUMBER:
                    $raw['default'] = 'float';
                    break;
                case T_NULL:
                    if (isset($raw['default'])) {
                        $raw['default'] = 'null'; // after T_EQUAL
                    } else {
                        $raw['type'] = ($raw['type'] ?? '') . $token['content'];
                    }
                    break;
                case T_FALSE:
                    if (isset($raw['default'])) {
                        $raw['default'] = 'bool'; // after T_EQUAL
                    } else {
                        $raw['type'] = ($raw['type'] ?? '') . $token['content'];
                    }
                    break;
                case T_TRUE:
                    $raw['default'] = 'bool';
                    break;
                case T_ARRAY:
                    $raw['default'] = 'array';
                    $ptr = $file->findNext(T_CLOSE_PARENTHESIS, $ptr + 1) ?: $ptr;
                    break;
                case T_OPEN_SHORT_ARRAY:
                    $raw['default'] = 'array';
                    $ptr = $file->findNext(T_CLOSE_SHORT_ARRAY, $ptr + 1) ?: $ptr;
                    break;
                case T_PARENT:
                case T_CALLABLE:
                case T_NULLABLE:
                case T_INLINE_THEN: // looks like a Nikitinuser bug
                    // these cannot be default
                    $raw['type'] = ($raw['type'] ?? '') . $token['content'];
                    break;
                case T_EQUAL:
                    $raw['default'] = '';
                    break;
                case T_STRING:
                case T_SELF:
                case T_DOUBLE_COLON:
                case T_TYPE_UNION:
                case T_NS_SEPARATOR:
                    if (isset($raw['default'])) {
                        $raw['default'] .= $token['content'];
                    } else {
                        $raw['type'] = ($raw['type'] ?? '') . $token['content'];
                    }
                    break;
                case T_ELLIPSIS:
                    $raw['variable_length'] = true;
                    break;
                case T_BITWISE_AND:
                    $raw['pass_be_reference'] = true;
                    break;
                case T_VARIABLE:
                    $raw['name'] = substr($token['content'], 1);
                    $raw['line'] = $token['line'];
                    break;

                case T_COMMA:
                    if (!empty($raw)) {
                        $params[] = static::createParam($raw);
                        $raw = [];
                    }
                    break;
                case T_NEW:
                    $raw['new'] = true;
                    break;
                case T_OPEN_PARENTHESIS:
                    if ($raw['new'] ?? false) {
                        // new constructor args may contain tokens arrays, object constructors, etc.
                        // so we must skip to next param or to end of function signature
                        $closingParenthesisPtr = TokenHelper::findClosingParenthesis($file, $ptr);
                        // Some dumbass may write = new Obj, but it's already a standard warning. This won't crash
                        if (null !== $closingParenthesisPtr) {
                            if (!empty($raw)) {
                                $params[] = static::createParam($raw);
                                $raw = [];
                            }
                            $ptr = $closingParenthesisPtr;
                            break; // skip to whatever is next
                        }
                    }
                    // else: opened function signature params, can ignore
                    break;
                case T_CLOSE_PARENTHESIS:
                    // end of function signature, close parenthesis for new (hopefully) have been skipped
                    $returnLine = $token['line'];
                    if (!empty($raw)) {
                        $params[] = static::createParam($raw);
                    }
                    break 2;
                case T_ATTRIBUTE:
                    $attrEndPtr = $file->findNext(T_ATTRIBUTE_END, $ptr + 1);
                    if (false !== $attrEndPtr) {
                        $rawAttribute = $file->getTokensAsString($ptr, $attrEndPtr - $ptr + 1);
                        $attributeName = TokenHelper::parseAttributeName($rawAttribute);
                        if (null !== $attributeName) {
                            $raw['attrNames'] = $raw['attrNames'] ?? [];
                            $raw['attrNames'][] = $attributeName;
                        }
                        $ptr = $attrEndPtr;
                    }
                    break;
                case T_PUBLIC:
                case T_PROTECTED:
                case T_PRIVATE:
                    $raw['promoted'] = true;
                    break;
                case T_DOC_COMMENT_OPEN_TAG:
                    $docEndPtr = $file->findNext(T_DOC_COMMENT_CLOSE_TAG, $ptr + 1);
                    if (false !== $docEndPtr) {
                        $raw['doc_block'] = DocBlockParser::fromTokens($file, $ptr, $docEndPtr);
                        $ptr = $docEndPtr;
                    }
                    break;
            }
        }

        $rawReturnType = '';
        while (isset($tokens[++$ptr])) {
            $token = $tokens[$ptr];
            switch ($token['code']) {
                case T_SELF:
                case T_STATIC:
                case T_CALLABLE:
                case T_NULLABLE:
                case T_STRING:
                case T_TYPE_UNION:
                case T_FALSE:
                case T_NULL:
                case T_NS_SEPARATOR:
                    $returnLine = $token['line'];
                    $rawReturnType .= $token['content'];
                    break;
                case T_SEMICOLON:
                case T_OPEN_CURLY_BRACKET:
                    break 2;
            }
        }
        $returnType = TypeFactory::fromRawType($rawReturnType);

        return new FunctionSignature(
            $fnNameLine,
            $fnName,
            $params,
            $returnType,
            $returnLine
        );
    }

    /**
     * @param mixed[] $raw
     *
     * @return FunctionParam
     */
    protected static function createParam(array $raw): FunctionParam
    {
        $rawValueType = $raw['default'] ?? '';
        if (str_contains($rawValueType, '::')) {
            $valueType = null; // a constant is used, need reflection :(
        } else {
            $valueType = TypeFactory::fromRawType($raw['default'] ?? '');
        }

        if ($valueType instanceof FqcnType && !($raw['new'] ?? false)) {
            if (defined($valueType->toString())) { // eg PHP_INT_MAX
                $valueType = TypeFactory::fromValue(constant($valueType->toString()));
            } else {
                $valueType = null; // give up
            }
        }

        $attrNames = [];
        if ($raw['attrNames'] ?? []) {
            $attrNames = array_values(array_unique($raw['attrNames']));
        }

        return new FunctionParam(
            $raw['line'],
            $raw['name'],
            TypeFactory::fromRawType($raw['type'] ?? ''),
            $valueType,
            $attrNames,
            $raw['doc_block'] ?? new UndefinedDocBlock(),
            $raw['promoted'] ?? false
        );
    }
}
