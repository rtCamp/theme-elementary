/**
 * Editor behavior for the rtcamp/navigation block.
 */
( function() {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls } = wp.blockEditor;
	const { Button, CheckboxControl, PanelBody, TextControl } = wp.components;
	const { createElement: el } = wp.element;

	const updateItem = ( items, index, nextItem ) =>
		items.map( ( item, itemIndex ) => ( itemIndex === index ? { ...item, ...nextItem } : item ) );

	registerBlockType( 'rtcamp/navigation', {
		edit( { attributes, setAttributes } ) {
			const label = attributes.label || 'Primary navigation';
			const items = Array.isArray( attributes.items ) ? attributes.items : [];

			return el(
				'nav',
				{ className: 'elementary-navigation', 'aria-label': label },
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Navigation settings' },
						el( TextControl, {
							label: 'Landmark label',
							value: label,
							onChange: ( value ) => setAttributes( { label: value } ),
						} ),
						items.map( ( item, index ) =>
							el(
								'div',
								{ key: index, className: 'elementary-navigation-editor__item' },
								el( TextControl, {
									label: `Item ${ index + 1 } label`,
									value: item.label || '',
									onChange: ( value ) =>
										setAttributes( { items: updateItem( items, index, { label: value } ) } ),
								} ),
								el( TextControl, {
									label: `Item ${ index + 1 } URL`,
									value: item.url || '',
									onChange: ( value ) =>
										setAttributes( { items: updateItem( items, index, { url: value } ) } ),
								} ),
								el( CheckboxControl, {
									label: 'Current page',
									checked: !! item.current,
									onChange: ( value ) =>
										setAttributes( { items: updateItem( items, index, { current: value } ) } ),
								} ),
								el(
									Button,
									{
										variant: 'link',
										isDestructive: true,
										onClick: () =>
											setAttributes( {
												items: items.filter( ( _item, itemIndex ) => itemIndex !== index ),
											} ),
									},
									'Remove item',
								),
							),
						),
						el(
							Button,
							{
								variant: 'secondary',
								onClick: () =>
									setAttributes( {
										items: [ ...items, { label: 'New item', url: '/', current: false } ],
									} ),
							},
							'Add item',
						),
					),
				),
				el(
					'ul',
					{ className: 'elementary-navigation__list' },
					items.map( ( item, index ) =>
						el(
							'li',
							{ key: index, className: 'elementary-navigation__item' },
							el(
								'span',
								{ className: 'elementary-navigation__link' },
								item.label || 'Navigation item',
							),
						),
					),
				),
			);
		},
		save() {
			return null;
		},
	} );
}() );
