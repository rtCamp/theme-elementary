<?php
/**
 * General-purpose theme utility helpers.
 *
 * Stateless utility class — pure functions wrapped in a namespace.
 * Final + private constructor: must be used statically, never instantiated.
 *
 * The service accessors (logger / encryption / templates) return the theme's
 * shared framework service from the container, so callers use the framework
 * API directly with no extra wrapping:
 *
 *   Util::logger()->info( 'Cache warmed', [ 'items' => 42 ] );
 *   $cipher = Util::encryption()->encrypt( $secret );
 *   Util::templates()->render( 'content', 'card', [ 'title' => 'Hi' ] );
 *
 * The component / template / encrypt wrappers below stay for the common cases.
 * Add a new shared service by adding the Core\<Service> class (implementing
 * Shareable) to Main::CLASSES and a one-line accessor here.
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
use rtCamp\Theme\Elementary\Core\Logger;
use rtCamp\Theme\Elementary\Core\Templates;
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
	 * The theme's shared Logger. Silent unless WP_DEBUG.
	 *
	 * @return Logger Shared logger.
	 */
	public static function logger(): Logger {
		return self::shared( Logger::class );
	}

	/**
	 * The theme's shared Encryptor.
	 *
	 * @return Encryption Shared encryptor.
	 */
	public static function encryption(): Encryption {
		return self::shared( Encryption::class );
	}

	/**
	 * The theme's shared template loader (child theme > parent theme).
	 *
	 * @return Templates Shared template loader.
	 */
	public static function templates(): Templates {
		return self::shared( Templates::class );
	}

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
		self::components()->render( $name, $args, $options );
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
		return self::components()->get( $name, $args, $options );
	}

	/**
	 * The theme's shared component loader.
	 *
	 * @return Components Shared component loader.
	 */
	public static function components(): Components {
		return self::shared( Components::class );
	}

	/**
	 * Render a theme template part, echoing its output.
	 *
	 * @param string               $slug Template slug.
	 * @param string|null          $name Optional. Template variation name.
	 * @param array<string, mixed> $args Optional. Data passed to the template.
	 *
	 * @return void
	 */
	public static function render_template( string $slug, ?string $name = null, array $args = [] ): void {
		self::templates()->render( $slug, $name, $args );
	}

	/**
	 * Get a rendered theme template part as a string.
	 *
	 * @param string               $slug Template slug.
	 * @param string|null          $name Optional. Template variation name.
	 * @param array<string, mixed> $args Optional. Data passed to the template.
	 *
	 * @return string Rendered template output, or '' if not found.
	 */
	public static function get_template( string $slug, ?string $name = null, array $args = [] ): string {
		return self::templates()->get( $slug, $name, $args );
	}

	/**
	 * Encrypt a value with the theme's shared Encryptor.
	 *
	 * @param string $value Plaintext to encrypt.
	 *
	 * @return string|false Encrypted value, or false on failure.
	 *
	 * @throws \RuntimeException If ELEMENTARY_ENCRYPTION_KEY is not configured.
	 */
	public static function encrypt( string $value ): string|false {
		return self::encryption()->encrypt( $value );
	}

	/**
	 * Decrypt a value produced by Util::encrypt().
	 *
	 * @param string $value Encrypted value.
	 *
	 * @return string|false Decrypted value, or false on failure/tampering.
	 *
	 * @throws \RuntimeException If ELEMENTARY_ENCRYPTION_KEY is not configured.
	 */
	public static function decrypt( string $value ): string|false {
		return self::encryption()->decrypt( $value );
	}

	/**
	 * Resolve a shared service from the container.
	 *
	 * @template T of object
	 *
	 * @param string $service Service class name.
	 *
	 * @phpstan-param class-string<T> $service
	 *
	 * @return object Shared instance.
	 *
	 * @phpstan-return T
	 */
	private static function shared( string $service ): object {
		return Main::get_instance()->get_shared( $service );
	}
}
