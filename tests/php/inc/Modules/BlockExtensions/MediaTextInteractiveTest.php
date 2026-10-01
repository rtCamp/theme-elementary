<?php
/**
 * Test MediaTextInteractive block extension.
 *
 * Removed together with the `block-extension` example during init.
 *
 * @package rtCamp\Theme\Elementary
 */

declare( strict_types = 1 );

use rtCamp\Theme\Elementary\Core\FeatureRegistry;
use rtCamp\Theme\Elementary\Main;
use rtCamp\Theme\Elementary\Modules\BlockExtensions\MediaTextInteractive;
use rtCamp\Theme\Elementary\Tests\TestCase;
use rtCamp\WPPrimitives\Contracts\Interfaces\ConditionallyRegistrable;

/**
 * Class MediaTextInteractiveTest
 *
 * @since 1.0.0
 */
class MediaTextInteractiveTest extends TestCase {

	/**
	 * Class name that opts a block into the interactive behaviour.
	 */
	private const CLASS_NAME = 'elementary-media-text-interactive';

	/**
	 * Extension under test.
	 *
	 * Built without its constructor: the constructor registers the feature flag,
	 * which Main already did at boot, and a second registration is reported as a
	 * _doing_it_wrong() collision. The render filters do not depend on it.
	 *
	 * @var MediaTextInteractive
	 */
	private MediaTextInteractive $extension;

	/**
	 * Build the extension under test.
	 */
	public function set_up(): void {
		parent::set_up();

		$this->extension = ( new ReflectionClass( MediaTextInteractive::class ) )->newInstanceWithoutConstructor();
	}

	/**
	 * Drop the script module a columns render enqueues.
	 */
	public function tear_down(): void {
		wp_dequeue_script_module( '@elementary/media-text' );

		parent::tear_down();
	}

	/**
	 * MediaTextInteractive implements ConditionallyRegistrable.
	 */
	public function test_implements_conditionally_registrable(): void {
		$this->assertTrue( is_a( MediaTextInteractive::class, ConditionallyRegistrable::class, true ) );
	}

	/**
	 * The block render filters are attached when the feature is loaded.
	 */
	public function test_registers_block_render_filters(): void {
		$this->assertNotFalse( has_filter( 'render_block_core/button' ) );
		$this->assertNotFalse( has_filter( 'render_block_core/columns' ) );
		$this->assertNotFalse( has_filter( 'render_block_core/video' ) );
	}

	/**
	 * The register_hooks() method attaches each renderer to its block.
	 */
	public function test_register_hooks_attaches_each_renderer(): void {
		$this->extension->register_hooks();

		$this->assertSame( 10, has_filter( 'render_block_core/button', [ $this->extension, 'render_block_core_button' ] ) );
		$this->assertSame( 10, has_filter( 'render_block_core/columns', [ $this->extension, 'render_block_core_columns' ] ) );
		$this->assertSame( 10, has_filter( 'render_block_core/video', [ $this->extension, 'render_block_core_video' ] ) );
	}

	/**
	 * The feature self-registers its flag with a description for Settings → Features.
	 */
	public function test_feature_flag_is_registered_and_described(): void {
		$registry = Main::get_instance()->get_shared( FeatureRegistry::class );
		$features = $registry->get_features();

		$this->assertArrayHasKey( 'media-text-interactive', $features );
		$this->assertStringContainsString( 'Media & Text', $features['media-text-interactive']['description'] );
		$this->assertSame( 'ELEMENTARY_FEATURE_MEDIA_TEXT_INTERACTIVE', $registry->constant_name( 'media-text-interactive' ) );
	}

	/**
	 * Blocks without the opt-in class are returned untouched.
	 */
	public function test_blocks_without_the_class_are_untouched(): void {
		$markup = '<div class="wp-block-button"><a class="wp-block-button__link">Play</a></div>';

		$this->assertSame( $markup, $this->extension->render_block_core_button( $markup, [ 'attrs' => [] ] ) );
		$this->assertSame( $markup, $this->extension->render_block_core_button( $markup, [ 'attrs' => [ 'className' => 'is-style-outline' ] ] ) );
		$this->assertSame( $markup, $this->extension->render_block_core_columns( $markup, [] ) );
		$this->assertSame( $markup, $this->extension->render_block_core_video( $markup, [ 'attrs' => [ 'className' => 'other' ] ] ) );
	}

	/**
	 * The opted-in button triggers the play action on click.
	 */
	public function test_button_gets_the_play_action(): void {
		$html = $this->extension->render_block_core_button(
			'<div class="wp-block-button elementary-media-text-interactive"><a class="wp-block-button__link">Play</a></div>',
			$this->block()
		);

		$this->assertStringContainsString( 'data-wp-on--click="actions.play"', $html );
		$this->assertSame( 1, substr_count( $html, 'data-wp-on--click' ), 'Only the first tag is annotated.' );
	}

	/**
	 * The opted-in columns become the interactive region and load the store module.
	 */
	public function test_columns_become_the_interactive_region(): void {
		$html = $this->extension->render_block_core_columns(
			'<div class="wp-block-columns elementary-media-text-interactive"><div class="wp-block-column"></div></div>',
			$this->block()
		);

		$processor = new WP_HTML_Tag_Processor( $html );
		$processor->next_tag();

		$this->assertSame( '{ "namespace": "elementary/media-text" }', $processor->get_attribute( 'data-wp-interactive' ) );
		$this->assertSame( '{ "isPlaying": false }', $processor->get_attribute( 'data-wp-context' ) );

		ob_start();
		wp_script_modules()->print_enqueued_script_modules();
		$printed = (string) ob_get_clean();

		$this->assertStringContainsString( '/js/modules/media-text.js', $printed );
	}

	/**
	 * The opted-in video watches the play state.
	 */
	public function test_video_watches_the_play_state(): void {
		$html = $this->extension->render_block_core_video(
			'<figure class="wp-block-video elementary-media-text-interactive"><video src="clip.mp4"></video></figure>',
			$this->block()
		);

		$this->assertStringContainsString( 'data-wp-watch="callbacks.playVideo"', $html );
	}

	/**
	 * A parsed block carrying the opt-in class among others.
	 *
	 * @return array<string, mixed> Block.
	 */
	private function block(): array {
		return [ 'attrs' => [ 'className' => 'alignwide ' . self::CLASS_NAME ] ];
	}
}
