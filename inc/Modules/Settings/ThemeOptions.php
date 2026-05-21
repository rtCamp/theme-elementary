<?php
/**
 * Example Settings Page: Theme Options.
 *
 * Demonstrates how to use AbstractSettingsPage from wp-framework.
 * Registers a settings page under Settings → Elementary with a few options.
 *
 * @package rtCamp\Theme\Elementary\Modules\Settings
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Modules\Settings;

use Override;
use rtCamp\WPFramework\Contracts\Abstracts\AbstractSettingsPage;

/**
 * Class ThemeOptions
 */
class ThemeOptions extends AbstractSettingsPage {

	/**
	 * Option key prefix.
	 */
	private const PREFIX = 'elementary_';

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return 'elementary-settings';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_page_title(): string {
		return __( 'Elementary Theme Settings', 'elementary-theme' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_menu_title(): string {
		return __( 'Elementary', 'elementary-theme' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_settings(): array {
		return [
			self::PREFIX . 'enable_portfolio' => [
				'type'              => 'boolean',
				'default'           => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'show_in_rest'      => true,
			],
			self::PREFIX . 'footer_text'      => [
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => true,
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	

	/**
	 * {@inheritDoc}
	 */
	public function render(): void {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( $this->get_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( static::get_slug() );
				do_settings_sections( static::get_slug() );
				?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable Portfolio', 'elementary-theme' ); ?></th>
						<td>
							<input
								type="checkbox"
								name="<?php echo esc_attr( self::PREFIX . 'enable_portfolio' ); ?>"
								value="1"
								<?php checked( get_option( self::PREFIX . 'enable_portfolio', true ) ); ?>
							/>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Footer Text', 'elementary-theme' ); ?></th>
						<td>
							<input
								type="text"
								name="<?php echo esc_attr( self::PREFIX . 'footer_text' ); ?>"
								value="<?php echo esc_attr( (string) get_option( self::PREFIX . 'footer_text', '' ) ); ?>"
								class="regular-text"
							/>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
