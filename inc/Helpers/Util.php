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

/**
 * Class - Util
 *
 * No static methods yet — drop your theme-wide helpers here as the need arises.
 */
final class Util {

	/**
	 * Disallow instantiation — this class only exposes static helpers.
	 */
	private function __construct() {}
}
