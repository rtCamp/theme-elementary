import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';

import './editor.scss';
import './style.scss';

registerBlockType(metadata.name, {
	edit: Edit,
	save,
});
