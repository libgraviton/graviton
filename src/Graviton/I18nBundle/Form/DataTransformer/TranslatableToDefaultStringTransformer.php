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
     * return as array
     *
     * the value returned here will be translated by the i18n listener later on
     *
     * @param  Translatable|null $translatable value from model
     *
     * @return array
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
     * return simple string
     *
     * all we want to store after going through a for is a simple string. If there
     * are any other languages being sent, this is where they get stored.
     *
     * @param  array $default value from client
     *
     * @return string
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
