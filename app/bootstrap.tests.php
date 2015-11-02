<?php
/**
 * tests bootstrap file making sure the 'test' cache gets cleared before we execute tests.
 */
if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    $fs = new \Symfony\Component\Filesystem\Filesystem();
    $cacheDir = __DIR__.'/cache/'.$_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'];
    if ($fs->exists($cacheDir)) {
        $fs->remove($cacheDir);
    }
}

require __DIR__.'/bootstrap.php.cache';
