<?php
/**
 * Provides the base class for block extensions.
 *
 * @package Elementary-Theme
 */

declare( strict_types=1 );

namespace Elementary_Theme\Kernel\Abstracts;

/**
 * Class Abstract_Block_Extension
 *
 * @since 1.0.0
 */
abstract class Abstract_Block_Extension {

	/**
	 * Constructor.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup hooks for the block extension.
	 *
	 * @since 1.0.0
	 */
	abstract public function setup_hooks(): void;
}
