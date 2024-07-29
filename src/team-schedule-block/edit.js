import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div { ...useBlockProps() }>
            <p>{ 'Team Schedule Block – hello from the editor!' }</p>
        </div>
    );
}
