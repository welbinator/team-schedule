import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
    return (
        <div {...useBlockProps.save()} data-team={attributes.team || ''}>
            <select className="team-schedule-select"></select>
            <ul className="team-schedule-list"></ul>
        </div>
    );
}
