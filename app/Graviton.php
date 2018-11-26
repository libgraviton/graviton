<?php

namespace Graviton;

class Graviton {
	public const ROOT_DIR = __DIR__.'/../';

	public static function isVendorized() {
		return (strpos(self::ROOT_DIR, 'vendor/') !== false);
	}

}
