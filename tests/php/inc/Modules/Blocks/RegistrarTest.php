<?php
/**
 * Test the Registrar module.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

namespace rtCamp\Theme\Elementary\Tests\Modules\Blocks;

use rtCamp\Theme\Elementary\Modules\Blocks\Registrar;
use rtCamp\Theme\Elementary\Tests\TestCase;
use WP_Block_Type_Registry;

/**
 * Class RegistrarTest
 *
 * @since 1.0.0
 */
class RegistrarTest extends TestCase {

	/**
	 * Test that all trial blocks are registered.
	 */
	public function test_blocks_are_registered(): void {
		$registry = WP_Block_Type_Registry::get_instance();

		$this->assertTrue( $registry->is_registered( 'rtcamp/button' ), 'rtcamp/button should be registered.' );
		$this->assertTrue( $registry->is_registered( 'rtcamp/card' ), 'rtcamp/card should be registered.' );
		$this->assertTrue( $registry->is_registered( 'rtcamp/hero' ), 'rtcamp/hero should be registered.' );
		$this->assertTrue( $registry->is_registered( 'rtcamp/navigation' ), 'rtcamp/navigation should be registered.' );
		$this->assertTrue( $registry->is_registered( 'rtcamp/post-loop' ), 'rtcamp/post-loop should be registered.' );
	}

	/**
	 * Test that the legacy block paths filter remains supported.
	 */
	public function test_legacy_block_paths_filter_registers_extra_blocks(): void {
		$registry     = WP_Block_Type_Registry::get_instance();
		$library_root = sys_get_temp_dir() . '/elementary-legacy-blocks-' . wp_generate_uuid4();
		$blocks_root  = $library_root . '/blocks';
		$block_root   = $blocks_root . '/legacy-filter-probe';

		wp_mkdir_p( $block_root );

		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$block_root . '/block.json',
			wp_json_encode(
				[
					'$schema'    => 'https://schemas.wp.org/trunk/block.json',
					'apiVersion' => 3,
					'name'       => 'rtcamp/legacy-filter-probe',
					'title'      => 'Legacy Filter Probe',
					'render'     => 'file:./render.php',
				]
			)
		);
		file_put_contents( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$block_root . '/render.php',
			'<?php echo "<div class=\"legacy-filter-probe\"></div>";'
		);

		$paths_callback = static function ( array $paths ) use ( $blocks_root ): array {
			$paths[] = $blocks_root;

			return $paths;
		};

		add_filter( 'elementary_theme_block_paths', $paths_callback );

		try {
			Registrar::get_instance()->register_blocks();

			$this->assertTrue( $registry->is_registered( 'rtcamp/legacy-filter-probe' ) );
		} finally {
			remove_filter( 'elementary_theme_block_paths', $paths_callback );
			unregister_block_type( 'rtcamp/legacy-filter-probe' );
			unlink( $block_root . '/render.php' );
			unlink( $block_root . '/block.json' );
			rmdir( $block_root );
			rmdir( $blocks_root );
			rmdir( $library_root );
		}
	}

}
