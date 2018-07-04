<?php
/**
 * a way to load dynamically added resources files to Translator
 */

namespace Graviton\I18nBundle\Translator;

use Graviton\I18nBundle\Service\I18nCacheUtils;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class TranslatorFactory
{

    /**
     * factory method, creates a translator
     *
     * @param TranslatorInterface $translator translator
     * @param I18nCacheUtils      $cacheUtils cache utils
     *
     * @return TranslatorInterface translator
     */
    public static function createTranslator(TranslatorInterface $translator, I18nCacheUtils $cacheUtils)
    {
        return self::addResourceFiles($translator, $cacheUtils->getResources([]));
    }

    /**
     * as we cannot add resources files directly (the function from where i lifted this is private),
     * we copied this here and add it as Translator would (and does)
     *
     * @param TranslatorInterface $translator translator
     * @param array               $resources  added resources
     *
     * @return TranslatorInterface translator
     */
    private static function addResourceFiles(TranslatorInterface $translator, array $resources)
    {
        foreach ($resources as $locale => $files) {
            foreach ($files as $key => $file) {
                // filename is domain.locale.format
                list($domain, $locale, $format) = explode('.', basename($file), 3);
                $translator->addResource($format, $file, $locale, $domain);
            }
        }

        return $translator;
    }
}
