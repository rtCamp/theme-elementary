/**
 * Custom module script required for the media text interactive pattern.
 */

/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

store( 'elementary/media-text', {
	actions: {
		/**
		 * Update the video play state.
		 *
		 * @return {void}
		 */
		play() {
			const context = getContext();
			context.isPlaying = true;
		},
	},
	callbacks: {
		/**
		 * Play the video.
		 *
		 * @return {void}
		 */
		playVideo() {
			const context = getContext();
			const { ref } = getElement();
			if ( context.isPlaying ) {
				ref.querySelector( 'video' )?.play();
				context.isPlaying = false;
			}
		},
	},
} );
