<?php
/**
 * Theme encryption service.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\Utils\Encryptor;

/**
 * Class Encryption
 *
 * The theme's shared Encryptor. Extends the framework Encryptor and sources the
 * key via the key() seam from the ELEMENTARY_ENCRYPTION_KEY constant — define it
 * in wp-config.php before using encryption. There is deliberately no salt
 * fallback: an auth salt should not double as an encryption key, and rotating
 * WordPress salts must not invalidate encrypted data. Shared through the
 * container; call it through Helpers\Util (Util::encrypt() / Util::decrypt()).
 *
 * @since 1.0.0
 */
final class Encryption extends Encryptor implements Shareable {

	/**
	 * Resolve the theme's encryption key.
	 *
	 * @return string The encryption key.
	 *
	 * @throws \RuntimeException If ELEMENTARY_ENCRYPTION_KEY is not defined or is empty.
	 */
	protected function key(): string {
		if ( defined( 'ELEMENTARY_ENCRYPTION_KEY' ) && '' !== ELEMENTARY_ENCRYPTION_KEY ) {
			return (string) ELEMENTARY_ENCRYPTION_KEY;
		}

		return parent::key();
	}
}
