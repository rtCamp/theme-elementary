/**
 * Editor behavior for the rtcamp/button block.
 */
( function() {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, URLInputButton } = wp.blockEditor;
	const { PanelBody, SelectControl, TextControl } = wp.components;
	const { createElement: el } = wp.element;

	registerBlockType( 'rtcamp/button', {
		edit( { attributes, setAttributes } ) {
			const label = attributes.label || '';
			const url = attributes.url || '';
			const variant = attributes.variant || 'primary';
			const size = attributes.size || 'medium';
			const className = [
				'elementary-button',
				`elementary-button--${ variant }`,
				`elementary-button--${ size }`,
				attributes.class || '',
			]
				.filter( Boolean )
				.join( ' ' );

			return el(
				'div',
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Button settings' },
						el( TextControl, {
							label: 'URL',
							value: url,
							onChange: ( value ) => setAttributes( { url: value } ),
						} ),
						el( URLInputButton, {
							url,
							onChange: ( value ) => setAttributes( { url: value } ),
						} ),
						el( SelectControl, {
							label: 'Variant',
							value: variant,
							options: [
								{ label: 'Primary', value: 'primary' },
								{ label: 'Secondary', value: 'secondary' },
								{ label: 'Text', value: 'text' },
							],
							onChange: ( value ) => setAttributes( { variant: value } ),
						} ),
						el( SelectControl, {
							label: 'Size',
							value: size,
							options: [
								{ label: 'Small', value: 'small' },
								{ label: 'Medium', value: 'medium' },
								{ label: 'Large', value: 'large' },
							],
							onChange: ( value ) => setAttributes( { size: value } ),
						} ),
					),
				),
				el( RichText, {
					tagName: 'span',
					className,
					value: label,
					allowedFormats: [],
					placeholder: 'Button label',
					onChange: ( value ) => setAttributes( { label: value } ),
				} ),
			);
		},
		save() {
			return null;
		},
	} );
}() );
