/**
 * Tests for the media-text interactive store.
 *
 * Removed together with the `block-extension` example during init.
 */

/**
 * WordPress dependencies
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

jest.mock(
	'@wordpress/interactivity',
	() => ({
		store: jest.fn(),
		getContext: jest.fn(),
		getElement: jest.fn(),
	}),
	{ virtual: true }
);

/**
 * Load the module and return the definition it handed to store().
 *
 * @return {Object} Store definition.
 */
const loadStore = () => {
	jest.isolateModules(() => {
		require('../../src/js/frontend/modules/media-text');
	});

	const [namespace, definition] = store.mock.calls.at(-1);
	expect(namespace).toBe('elementary/media-text');

	return definition;
};

describe('media-text interactive store', () => {
	beforeEach(() => {
		jest.clearAllMocks();
	});

	it('registers the elementary/media-text store', () => {
		loadStore();

		expect(store).toHaveBeenCalledTimes(1);
	});

	it('play() marks the context as playing', () => {
		const context = { isPlaying: false };
		getContext.mockReturnValue(context);

		loadStore().actions.play();

		expect(context.isPlaying).toBe(true);
	});

	it('playVideo() plays the video once and resets the flag', () => {
		const play = jest.fn();
		const context = { isPlaying: true };
		getContext.mockReturnValue(context);
		getElement.mockReturnValue({
			ref: {
				querySelector: (selector) => selector === 'video' && { play },
			},
		});

		loadStore().callbacks.playVideo();

		expect(play).toHaveBeenCalledTimes(1);
		expect(context.isPlaying).toBe(false);
	});

	it('playVideo() does nothing while not playing', () => {
		const play = jest.fn();
		getContext.mockReturnValue({ isPlaying: false });
		getElement.mockReturnValue({
			ref: { querySelector: () => ({ play }) },
		});

		loadStore().callbacks.playVideo();

		expect(play).not.toHaveBeenCalled();
	});

	it('playVideo() tolerates a region without a video element', () => {
		const context = { isPlaying: true };
		getContext.mockReturnValue(context);
		getElement.mockReturnValue({ ref: { querySelector: () => null } });

		expect(() => loadStore().callbacks.playVideo()).not.toThrow();
		expect(context.isPlaying).toBe(false);
	});
});
