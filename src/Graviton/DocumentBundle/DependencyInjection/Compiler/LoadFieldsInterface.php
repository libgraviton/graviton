<?php
/**
 * load fields from config
 */

namespace Graviton\DocumentBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
interface LoadFieldsInterface
{
    /**
     * @param array     $map      map to add entries to
     * @param \DOMXPath $xpath    xpath access to doctrine config dom
     * @param string    $ns       namespace
     * @param string    $bundle   bundle name
     * @param string    $doc      document name
     * @param boolean   $embedded is this an embedded doc, further args are only for embeddeds
     * @param string    $name     name prefix of document the embedded field belongs to
     * @param string    $prefix   prefix to add to embedded field name
     *
     * @return void
     */
    public function loadFieldsFromDOM(
        array &$map,
        \DOMXPath $xpath,
        $ns,
        $bundle,
        $doc,
        $embedded,
        $name = '',
        $prefix = ''
    );
}
