<?php
/**
 * Base class for block patterns.
 *
 * @package Elementary
 */

namespace Elementary\Patterns;

/**
 * Class Block_Pattern_Base
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
