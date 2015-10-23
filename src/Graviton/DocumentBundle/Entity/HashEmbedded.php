<?php
/**
 * Embedded hash class file
 */

namespace Graviton\DocumentBundle\Entity;

/**
 * Special type for embedded hash fields
 *
 * These differ from Hash in that hey are intended for use as embedded fields. They should get
 * used often since embedded hashes are usually preferable to refernced hashes in most cases.
 *
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://swisscom.ch
 */
class HashEmbedded extends Hash
{
}
