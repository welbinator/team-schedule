import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
    return (
        <div { ...useBlockProps() }>
            <p>{ 'This block will display a dropdown allowing your users to select the team schedule they want to view' }</p>
        </div>
    );
}
