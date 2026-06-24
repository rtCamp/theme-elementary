<?php
/**
 * Theme assets registration.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\AssetLoader;
use rtCamp\WPFramework\Contracts\Interfaces\Registrable;
use rtCamp\WPFramework\Contracts\Interfaces\Shareable;

/**
 * Class Assets
 *
 * The theme's asset loader: extends the framework AssetLoader and registers
 * the theme's own scripts and styles on the relevant hooks. Shared so the
 * component loader registers component assets through the same instance.
 *
 * @since 1.0.0
 */
class Assets extends AssetLoader implements Registrable, Shareable {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			ELEMENTARY_THEME_PATH,
			get_template_directory_uri(),
			'assets/build'
		);
	}

	/**
	 * Whether Tailwind CSS is enabled for this theme.
	 *
	 * Off by default. The scaffold's Tailwind feature flips
	 * ELEMENTARY_THEME_ENABLE_TAILWIND to true in functions.php when enabled.
	 *
	 * Resolved at enqueue time (not in the constructor) so the
	 * elementary_theme_tailwind_enabled filter can be added by child themes or
	 * plugins that load after this one.
	 *
	 * To force-enable or disable before the theme loads, define the constant in
	 * wp-config.php or a must-use plugin:
	 *
	 *   define( 'ELEMENTARY_THEME_ENABLE_TAILWIND', true );
	 *   define( 'ELEMENTARY_THEME_ENABLE_TAILWIND', false );
	 *
	 * To override at runtime (e.g. from a child theme or plugin):
	 *
	 *   add_filter( 'elementary_theme_tailwind_enabled', '__return_true' );
	 *   add_filter( 'elementary_theme_tailwind_enabled', '__return_false' );
	 *
	 * @return bool
	 */
	private function is_tailwind_enabled(): bool {
		return (bool) apply_filters(
			'elementary_theme_tailwind_enabled',
			ELEMENTARY_THEME_ENABLE_TAILWIND
		);
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_browser_sync' ] );
		add_filter( 'render_block', [ $this, 'enqueue_block_specific_assets' ], 10, 2 );
	}

	/**
	 * Register assets.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function register_assets(): void {
		$this->register_script( 'core-navigation', 'js/frontend/core-navigation' );
		$this->register_style( 'core-navigation', 'css/frontend/core-navigation' );
		$this->register_style( 'elementary-theme-styles', 'css/frontend/styles' );

		if ( $this->is_tailwind_enabled() ) {
			$this->register_style( 'elementary-theme-tailwind', 'css/frontend/tailwind' );
		}
	}

	/**
	 * Enqueue block specific assets.
	 *
	 * @param string               $markup Markup of the block.
	 * @param array<string, mixed> $block  Array with block information.
	 *
	 * @return string Updated markup.
	 *
	 * @since 1.0.0
	 *
	 * @action render_block
	 */
	public function enqueue_block_specific_assets( string $markup, array $block ): string {
		if ( ! empty( $block['blockName'] ) && 'core/navigation' === $block['blockName'] ) {
			wp_enqueue_script( 'core-navigation' );
			wp_enqueue_style( 'core-navigation' );
		}

		return $markup;
	}

	/**
	 * Enqueue JS and CSS in frontend.
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_assets(): void {
		wp_enqueue_style( 'elementary-theme-styles' );

		if ( $this->is_tailwind_enabled() ) {
			wp_enqueue_style( 'elementary-theme-tailwind' );
		}
	}

	/**
	 * Enqueue the BrowserSync client script for local live reload.
	 *
	 * Only runs in the `local` environment and when not disabled via DISABLE_BS
	 * in .env.local. The client URL is derived from the site URL and the
	 * BrowserSync port (BS_PORT in .env.local, default 3001), or taken verbatim
	 * from the ELEMENTARY_THEME_BROWSER_SYNC_URL constant when defined (for
	 * custom ports or remote/proxied setups).
	 *
	 * @since 1.0.0
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_browser_sync(): void {
		if ( 'local' !== wp_get_environment_type() || ! $this->is_hmr_enabled() || $this->is_browser_sync_disabled() ) {
			return;
		}

		if ( defined( 'ELEMENTARY_THEME_BROWSER_SYNC_URL' ) ) {
			$bs_url = ELEMENTARY_THEME_BROWSER_SYNC_URL;
		} else {
			$scheme = is_ssl() ? 'https' : 'http';
			$host   = wp_parse_url( home_url(), PHP_URL_HOST );
			$host   = $host ? $host : 'localhost';
			$port   = $this->get_browser_sync_port();
			$bs_url = "{$scheme}://{$host}:{$port}/browser-sync/browser-sync-client.js";
		}

		wp_enqueue_script( 'elementary-browser-sync', $bs_url, [], ELEMENTARY_THEME_VERSION, true );
	}

	/**
	 * Read the BrowserSync port from .env.local (BS_PORT), defaulting to 3001.
	 *
	 * Keeps the enqueued client URL in sync with the port webpack/BrowserSync
	 * actually bind to, which is read from the same .env.local on the build side.
	 * Falls back to the default when BS_PORT is absent or not a valid TCP port
	 * (1–65535).
	 *
	 * THIS METHOD IS INTENDED FOR LOCAL DEVELOPMENT ENVIRONMENTS ONLY.
	 *
	 * @return int BrowserSync port.
	 */
	private function get_browser_sync_port(): int {
		$default = 3001;
		$value   = $this->get_env_value( 'BS_PORT' );

		if ( null !== $value && preg_match( '/^\d+$/', $value ) ) {
			$port = (int) $value;

			if ( $port >= 1 && $port <= 65535 ) {
				return $port;
			}
		}

		return $default;
	}

	/**
	 * Whether HMR (BrowserSync live reload) is enabled via ENABLE_HMR in .env.local.
	 *
	 * Master switch for both sides: webpack only starts the BrowserSync server,
	 * and PHP only enqueues its client, when this is on. Defaults ON when the key
	 * is absent. Off values are `0`, `false`, `no`, and `off` (case-insensitive).
	 * DISABLE_BS still works as a finer client-only override. Toggle it from
	 * `npm run init` (manage mode) or by editing .env.local directly.
	 *
	 * THIS METHOD IS INTENDED FOR LOCAL DEVELOPMENT ENVIRONMENTS ONLY.
	 *
	 * @return bool True when HMR is enabled.
	 */
	private function is_hmr_enabled(): bool {
		$value = $this->get_env_value( 'ENABLE_HMR' );

		if ( null === $value ) {
			return true;
		}

		return ! in_array( strtolower( $value ), [ '0', 'false', 'no', 'off' ], true );
	}

	/**
	 * Whether BrowserSync is disabled via DISABLE_BS in .env.local.
	 *
	 * Disabling prevents PHP from enqueuing the BrowserSync client script. The
	 * BrowserSync server still starts (webpack still runs it), but the browser
	 * won't connect to it. Truthy values are `1`, `true`, `yes`, and `on`
	 * (case-insensitive); anything else (or an absent key) keeps it enabled.
	 *
	 * THIS METHOD IS INTENDED FOR LOCAL DEVELOPMENT ENVIRONMENTS ONLY.
	 *
	 * @return bool True when BrowserSync should be disabled.
	 */
	private function is_browser_sync_disabled(): bool {
		$value = $this->get_env_value( 'DISABLE_BS' );

		if ( null === $value ) {
			return false;
		}

		return in_array( strtolower( $value ), [ '1', 'true', 'yes', 'on' ], true );
	}

	/**
	 * Read a single key's value from .env.local.
	 *
	 * Returns the trimmed value (without surrounding single/double quotes) for
	 * the given key, or null when the file is unreadable or the key is absent.
	 *
	 * THIS METHOD IS INTENDED FOR LOCAL DEVELOPMENT ENVIRONMENTS ONLY.
	 *
	 * @param string $key Environment variable name to read.
	 *
	 * @return string|null The value, or null when not found.
	 */
	private function get_env_value( string $key ): ?string {
		$env_file = $this->env_file_path();

		if ( ! is_readable( $env_file ) ) {
			return null;
		}

		// phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown -- Local dev only; reading a small project file, not remote.
		$contents = file_get_contents( $env_file );

		if ( false === $contents ) {
			return null;
		}

		if ( preg_match( '/^\s*' . preg_quote( $key, '/' ) . '\s*=\s*(.*)$/m', $contents, $matches ) ) {
			return trim( $matches[1], " \t\"'" );
		}

		return null;
	}

	/**
	 * Absolute path to the .env.local file read for local-dev flags.
	 *
	 * Isolated into its own method so tests can point the env lookup at a
	 * throwaway temp file (via a subclass) instead of touching the developer's
	 * real .env.local.
	 *
	 * @return string Absolute path to .env.local in the theme root.
	 */
	protected function env_file_path(): string {
		return $this->base_dir . '.env.local';
	}
}
