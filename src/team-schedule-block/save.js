import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    return (
        <div { ...useBlockProps.save() }>
            <select className="team-schedule-dropdown">
                <option value="">{ 'Please select your team' }</option>
            </select>
            <div className="team-schedule-games">
            <p className="ts--dropdown-tooltip">{ 'Choose your team from the dropdown above' }</p>
            </div>
        </div>
    );
}
