<?php
/**
 * General-purpose theme utility helpers.
 *
 * Stateless utility class — pure functions wrapped in a namespace.
 * Final + private constructor: must be used statically, never instantiated.
 *
 * Future helper classes (string, cache, url, …) should be siblings of this
 * one under `inc/Helpers/`. Keep `Util` for cross-cutting bits that don't
 * earn their own dedicated class.
 *
 * @package rtCamp\Theme\Elementary\Helpers
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Helpers;

use rtCamp\Theme\Elementary\Core\Components;
use rtCamp\Theme\Elementary\Core\Encryption;
use rtCamp\Theme\Elementary\Main;

/**
 * Class - Util
 *
 * Cross-cutting theme helpers. Drop theme-wide bits here as the need arises.
 */
final class Util {

	/**
	 * Disallow instantiation — this class only exposes static helpers.
	 */
	private function __construct() {}

	/**
	 * Render a component by name.
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options Optional. Resolution options. See ComponentLoader::render().
	 *
	 * @return void
	 */
	public static function component( string $name, array $args = [], array $options = [] ): void {
		self::component_loader()->render( $name, $args, $options );
	}

	/**
	 * Get the rendered HTML of a component as a string.
	 *
	 * @param string               $name    Component name (e.g. 'Button', 'Card').
	 * @param array<string, mixed> $args    Arguments to pass to the component.
	 * @param array<string, mixed> $options Optional. Resolution options. See ComponentLoader::get().
	 *
	 * @return string Rendered component HTML.
	 */
	public static function get_component( string $name, array $args = [], array $options = [] ): string {
		return self::component_loader()->get( $name, $args, $options );
	}

	/**
	 * Get the shared theme component loader.
	 *
	 * @return Components Shared component loader.
	 */
	private static function component_loader(): Components {
		/**
		 * Shared component loader.
		 *
		 * @var Components $loader
		 */
		$loader = Main::get_instance()->get_shared( Components::class );

		return $loader;
	}

	/**
	 * Encrypt a value with the theme's shared Encryptor.
	 *
	 * @param string $value Plaintext to encrypt.
	 *
	 * @return string|false Encrypted value, or false on failure.
	 */
	public static function encrypt( string $value ): string|false {
		return self::encryptor()->encrypt( $value );
	}

	/**
	 * Decrypt a value produced by Util::encrypt().
	 *
	 * @param string $value Encrypted value.
	 *
	 * @return string|false Decrypted value, or false on failure/tampering.
	 */
	public static function decrypt( string $value ): string|false {
		return self::encryptor()->decrypt( $value );
	}

	/**
	 * Get the theme's shared Encryptor.
	 *
	 * @return Encryption Shared encryptor.
	 */
	private static function encryptor(): Encryption {
		/**
		 * Shared encryptor.
		 *
		 * @var Encryption $encryptor
		 */
		$encryptor = Main::get_instance()->get_shared( Encryption::class );

		return $encryptor;
	}
}
