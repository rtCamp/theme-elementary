<?php
/**
 * Theme template loader.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Core;

use rtCamp\WPFramework\Contracts\Interfaces\Shareable;
use rtCamp\WPFramework\TemplateLoader;

/**
 * Class Templates
 *
 * The theme's template loader: ships PHP template parts a child theme can
 * override (child theme > parent theme). Extends the framework TemplateLoader
 * and is shared via the container, mirroring the Assets / Components loaders.
 * Because the theme's own parts already sit in the (parent) theme, the package
 * layer collapses into the theme override layer automatically. Render wrappers
 * live in Helpers\Util (Util::render_template() / Util::get_template()).
 *
 * @since 1.0.0
 */
final class Templates extends TemplateLoader implements Shareable {

	/**
	 * Hook prefix for this loader's filters and actions.
	 */
	private const HOOK_PREFIX = 'elementary';

	/**
	 * Template-parts directory, under both the theme root and child-theme override.
	 */
	private const TEMPLATE_DIR = 'template-parts';

	/**
	 * Point the loader at the theme's template-parts directory.
	 */
	public function __construct() {
		parent::__construct(
			self::HOOK_PREFIX,
			ELEMENTARY_THEME_PATH . '/' . self::TEMPLATE_DIR,
			self::TEMPLATE_DIR
		);
	}
}
