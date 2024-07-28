import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit() {
    return (
        <div {...useBlockProps()}>
            <p>{__('Team Schedule Block – edit settings in the sidebar', 'team-schedule-block')}</p>
        </div>
    );
}
