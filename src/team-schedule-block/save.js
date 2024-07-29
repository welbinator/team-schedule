import { useBlockProps } from '@wordpress/block-editor';

export default function save() {
    return (
        <div { ...useBlockProps.save() }>
            <select className="team-schedule-dropdown">
                <option value="">{ 'Please select your team' }</option>
            </select>
            <div className="team-schedule-games">
                { 'Team Schedule Block – view on the front end!' }
            </div>
        </div>
    );
}
