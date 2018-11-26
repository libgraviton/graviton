<?php
/** this class helps with all path work; doing the 'vendor' check and returning absolute paths */

namespace Graviton;

/**
 * @author   List of contributors <https://github.com/libgraviton/graviton/graphs/contributors>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     http://swisscom.ch
 */
class Graviton
{
    public const ROOT_DIR = __DIR__ . '/../';

    /**
     * returns if we are inside a vendor dir, so we are a dependency
     *
     * @return bool true if yes, false otherwise
     */
    public static function isVendorized()
    {
        return (strpos(self::ROOT_DIR, 'vendor/') !== false);
    }

    /**
     * returns the path for all who want to 'scan' (like for a Finder) in all *bundles* that are
     * available.
     *
     * @return string path
     */
    public static function getBundleScanDir()
    {
        if (self::isVendorized()) {
            return self::ROOT_DIR . '../../../';
        }
        return self::ROOT_DIR;
    }
}
