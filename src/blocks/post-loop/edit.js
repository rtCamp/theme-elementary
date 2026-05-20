/**
 * Editor behavior for the rtcamp/post-loop block.
 */
( function() {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } = wp.components;
	const { createElement: el } = wp.element;
	const ServerSideRender = wp.serverSideRender;

	registerBlockType( 'rtcamp/post-loop', {
		edit( { attributes, setAttributes } ) {
			const postType = attributes.postType || 'post';
			const postsPerPage = attributes.postsPerPage || 3;
			const orderBy = attributes.orderBy || 'date';
			const order = attributes.order || 'desc';
			const displayExcerpt = attributes.displayExcerpt !== false;
			const emptyMessage = attributes.emptyMessage || 'No posts found.';

			return el(
				'div',
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Query settings' },
						el( TextControl, {
							label: 'Post type',
							value: postType,
							onChange: ( value ) => setAttributes( { postType: value } ),
						} ),
						el( RangeControl, {
							label: 'Posts per page',
							value: postsPerPage,
							min: 1,
							max: 12,
							onChange: ( value ) => setAttributes( { postsPerPage: value } ),
						} ),
						el( SelectControl, {
							label: 'Order by',
							value: orderBy,
							options: [
								{ label: 'Date', value: 'date' },
								{ label: 'Title', value: 'title' },
								{ label: 'Menu order', value: 'menu_order' },
							],
							onChange: ( value ) => setAttributes( { orderBy: value } ),
						} ),
						el( SelectControl, {
							label: 'Order',
							value: order,
							options: [
								{ label: 'Descending', value: 'desc' },
								{ label: 'Ascending', value: 'asc' },
							],
							onChange: ( value ) => setAttributes( { order: value } ),
						} ),
						el( ToggleControl, {
							label: 'Show excerpts',
							checked: displayExcerpt,
							onChange: ( value ) => setAttributes( { displayExcerpt: value } ),
						} ),
						el( TextControl, {
							label: 'Empty message',
							value: emptyMessage,
							onChange: ( value ) => setAttributes( { emptyMessage: value } ),
						} ),
					),
				),
				ServerSideRender
					? el( ServerSideRender, { block: 'rtcamp/post-loop', attributes } )
					: el(
						'div',
						{ className: 'elementary-post-loop' },
						el( 'p', { className: 'elementary-post-loop__empty-message' }, emptyMessage ),
					),
			);
		},
		save() {
			return null;
		},
	} );
}() );
