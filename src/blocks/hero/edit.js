/**
 * Editor behavior for the rtcamp/hero block.
 */
( function() {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, URLInputButton } = wp.blockEditor;
	const { PanelBody, TextControl } = wp.components;
	const { createElement: el } = wp.element;

	registerBlockType( 'rtcamp/hero', {
		edit( { attributes, setAttributes } ) {
			const title = attributes.title || '';
			const subtitle = attributes.subtitle || '';
			const buttonLabel = attributes.buttonLabel || '';
			const buttonUrl = attributes.buttonUrl || '';

			return el(
				'section',
				{ className: 'elementary-hero' },
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Hero settings' },
						el( TextControl, {
							label: 'Button URL',
							value: buttonUrl,
							onChange: ( value ) => setAttributes( { buttonUrl: value } ),
						} ),
						el( URLInputButton, {
							url: buttonUrl,
							onChange: ( value ) => setAttributes( { buttonUrl: value } ),
						} ),
					),
				),
				el(
					'div',
					{ className: 'elementary-hero__content' },
					el( RichText, {
						tagName: 'h1',
						className: 'elementary-hero__title',
						value: title,
						allowedFormats: [],
						placeholder: 'Hero title',
						onChange: ( value ) => setAttributes( { title: value } ),
					} ),
					el( RichText, {
						tagName: 'p',
						className: 'elementary-hero__subtitle',
						value: subtitle,
						allowedFormats: [],
						placeholder: 'Supporting text',
						onChange: ( value ) => setAttributes( { subtitle: value } ),
					} ),
					el( RichText, {
						tagName: 'span',
						className: 'elementary-button elementary-button--primary elementary-button--medium',
						value: buttonLabel,
						allowedFormats: [],
						placeholder: 'CTA label',
						onChange: ( value ) => setAttributes( { buttonLabel: value } ),
					} ),
				),
			);
		},
		save() {
			return null;
		},
	} );
}() );
