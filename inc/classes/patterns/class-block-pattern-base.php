<?php
/**
 * Base class for block patterns.
 *
 * @package Elementary-Theme
 */

namespace Elementary_Theme\Patterns;

/**
 * Class Block_Pattern_Base.
 * An abstract class which ensures uniform names for the content and registration functions.
 *
 * @since 1.0.0
 */
abstract class Block_Pattern_Base {

	/**
	 * Block pattern.
	 *
	 * @return array Block pattern properties.
	 */
	abstract public function block_pattern();

	/**
	 * Block pattern content.
	 *
	 * @return string Block pattern content.
	 */
	abstract public function block_pattern_content();
}
