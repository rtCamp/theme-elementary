/**
 * Editor behavior for the rtcamp/card block.
 */
( function() {
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, RichText, URLInputButton, MediaUpload, MediaUploadCheck } = wp.blockEditor;
	const { Button, PanelBody, TextControl } = wp.components;
	const { createElement: el } = wp.element;

	registerBlockType( 'rtcamp/card', {
		edit( { attributes, setAttributes } ) {
			const title = attributes.title || '';
			const description = attributes.description || '';
			const imageUrl = attributes.imageUrl || '';
			const imageAlt = attributes.imageAlt || '';
			const url = attributes.url || '';
			const buttonLabel = attributes.buttonLabel || '';

			return el(
				'article',
				{ className: 'elementary-card' },
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: 'Card settings' },
						el( TextControl, {
							label: 'Card URL',
							value: url,
							onChange: ( value ) => setAttributes( { url: value } ),
						} ),
						el( URLInputButton, {
							url,
							onChange: ( value ) => setAttributes( { url: value } ),
						} ),
						el( TextControl, {
							label: 'Image alt text',
							value: imageAlt,
							onChange: ( value ) => setAttributes( { imageAlt: value } ),
						} ),
					),
				),
				el(
					MediaUploadCheck,
					{},
					el( MediaUpload, {
						allowedTypes: [ 'image' ],
						value: imageUrl,
						onSelect: ( media ) =>
							setAttributes( {
								imageUrl: media.url,
								imageAlt: media.alt || imageAlt,
							} ),
						render: ( { open } ) =>
							imageUrl
								? el( 'div', { className: 'elementary-card__image' }, [
									el( 'img', { key: 'image', src: imageUrl, alt: imageAlt } ),
									el(
										Button,
										{ key: 'button', variant: 'secondary', onClick: open },
										'Replace image',
									),
								] )
								: el( Button, { variant: 'secondary', onClick: open }, 'Select image' ),
					} ),
				),
				el(
					'div',
					{ className: 'elementary-card__content' },
					el( RichText, {
						tagName: 'h3',
						className: 'elementary-card__title',
						value: title,
						allowedFormats: [],
						placeholder: 'Card title',
						onChange: ( value ) => setAttributes( { title: value } ),
					} ),
					el( RichText, {
						tagName: 'p',
						className: 'elementary-card__description',
						value: description,
						allowedFormats: [],
						placeholder: 'Card description',
						onChange: ( value ) => setAttributes( { description: value } ),
					} ),
					el( RichText, {
						tagName: 'span',
						className: 'elementary-button elementary-button--secondary elementary-button--medium',
						value: buttonLabel,
						allowedFormats: [],
						placeholder: 'Button label',
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
