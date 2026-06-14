/**
 * Custom module script required for the media text interactive pattern.
 */

/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

/**
 * Shape of the per-element context (set via `data-wp-context` in the markup).
 */
interface MediaTextContext {
	isPlaying: boolean;
}

store( 'elementary/media-text', {
	actions: {
		/**
		 * Update the video play state.
		 */
		play(): void {
			const context = getContext< MediaTextContext >();
			context.isPlaying = true;
		},
	},
	callbacks: {
		/**
		 * Play the video.
		 */
		playVideo(): void {
			const context = getContext< MediaTextContext >();
			const { ref } = getElement();
			if ( context.isPlaying ) {
				ref?.querySelector( 'video' )?.play();
				context.isPlaying = false;
			}
		},
	},
} );
