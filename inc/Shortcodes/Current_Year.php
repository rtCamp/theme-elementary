<?php
/**
 * Example Shortcode: Current Year.
 *
 * Demonstrates how to use Abstract_Shortcode from wp-framework.
 * Usage: [current_year] or [current_year format="y"]
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Shortcodes;

use rtCamp\WPFramework\Contracts\Abstracts\Abstract_Shortcode;

/**
 * Class Current_Year
 */
class Current_Year extends Abstract_Shortcode {

	/**
	 * {@inheritDoc}
	 */
	public static function get_tag(): string {
		return 'current_year';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function default_atts(): array {
		return [
			'format' => 'Y',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function render( array $atts, ?string $content ): string {
		return esc_html( gmdate( $atts['format'] ) );
	}
}
