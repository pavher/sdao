<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 1.12.2018
 */

namespace Pavher\Sdao\Utils;


use Nette\StaticClass;
use Pavher\Sdao\Tests\TestEntity;

class EntityPropertiesParser
{
    use StaticClass;

    public const ENTITY_PROPERTY_PARSER_NAME_KEY = "name";
    public const ENTITY_PROPERTY_PARSER_TYPE_KEY = "type";
    public const ENTITY_PROPERTY_PARSER_TYPE_IS_ARRAY_KEY = "isArrayOfType";
    public const ENTITY_PROPERTY_PARSER_OWNER_KEY = "owner";

    /**
     * @param \ReflectionClass $reflect
     * @return array of property information name => [name =>, type =>]
     * @internal param bool $recursion_call
     */
    public static function processPHPDocClass(\ReflectionClass $reflect): array
    {
        $phpDoc = [];
        $docComment = $reflect->getDocComment();
        if ($docComment === false || trim($docComment) == '') {
            return [];
        }

        $docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
        $parsedDocComment = ltrim($docComment, "\r\n");

        while (($newlinePos = strpos($parsedDocComment, "\n")) !== false) {

            $line = substr($parsedDocComment, 0, $newlinePos);

            $matches = array();
            if ((strpos($line, '@') === 0) && preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment,
                    $matches)
            ) {
                $tagDocblockLine = $matches[1];

                if (!preg_match('#^@(\w+)(\s|$)#', $tagDocblockLine)) {
                    // empty property definition
                    die('no');
                    break;
                }
                $matches3 = [];
                if (!preg_match('#^@(\w+)\s+([\w|\\\]+)(\\[\\])?(?:\s+(?:\$(\S+)))?#', $tagDocblockLine, $matches3)) {
                    break;
                }

                if ($matches3[1] === 'property') {
                    $phpDoc[$matches3[4]] = array(
                        self::ENTITY_PROPERTY_PARSER_NAME_KEY => $matches3[4],
                        self::ENTITY_PROPERTY_PARSER_TYPE_IS_ARRAY_KEY => ("[]" === $matches3[3]),
                        self::ENTITY_PROPERTY_PARSER_TYPE_KEY => $matches3[2],
                        self::ENTITY_PROPERTY_PARSER_OWNER_KEY => $reflect->getShortName()
                    );
                }
            }
            $parsedDocComment = substr($parsedDocComment, $newlinePos + 1);
        }

        if ($reflect->getParentClass() !== null) {
            $parentPhpDoc = self::processPHPDocClass($reflect->getParentClass());
            if($parentPhpDoc !== null) {
                $phpDoc = array_merge($phpDoc, $parentPhpDoc);
            }
        }

        return $phpDoc;
    }
}