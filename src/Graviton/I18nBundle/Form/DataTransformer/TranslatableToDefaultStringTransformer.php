<?php
/**
 * transform Translatable instance to default string value being stored on record
 */

namespace Graviton\I18nBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Graviton\I18nBundle\Service\I18nUtils;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class TranslatableToDefaultStringTransformer implements DataTransformerInterface
{
    /**
     * @var I18nUtils
     */
    private $utils;

    /**
     * @param I18nUtils $utils i18n utils for various needs
     */
    public function __construct(I18nUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * Transforms an object (translatable) to a string (default).
     *
     * @param  Translatable|null $translatable translatable object
     *
     * @return string
     */
    public function transform($translatable)
    {
        if ($translatable == null) {
            return null;
        }
        $translated = [
            $this->utils->getDefaultLanguage() => $translatable
        ];
        return $translated;
    }

    /**
     * Transforms a string (default) to an object (Translatable).
     *
     * @param  string $default default en vaue
     *
     * @return Translatable|null
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($default)
    {
        $defaultLang = $this->utils->getDefaultLanguage();
        if (!isset($default[$defaultLang])) {
            throw new TransformationFailedException(sprintf('Value must contain "%s" string', $defaultLang));
        }
        if (count($default) > 1) {
            $this->utils->insertTranslatable($default);
        }
        return $default[$defaultLang];
    }
}
