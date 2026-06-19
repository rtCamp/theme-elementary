<?php
/**
 * Menu registration.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Registrable;

/**
 * Class Menu
 *
 * @since 1.0.0
 */
class Menu implements Registrable {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_action( 'after_setup_theme', [ $this, 'register_menus' ] );
	}

	/**
	 * Register navigation menus.
	 *
	 * @since 1.0.0
	 */
	public function register_menus(): void {
		register_nav_menus(
			[
				'primary' => esc_html__( 'Primary Menu', 'elementary-theme' ),
				'footer'  => esc_html__( 'Footer Menu', 'elementary-theme' ),
			]
		);
	}
}
