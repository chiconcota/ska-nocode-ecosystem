import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { Button, TextareaControl, Placeholder } from '@wordpress/components';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: ( { clientId } ) => {
		const blockProps = useBlockProps( {
            className: 'ska-bridge-import-wrapper'
        } );
		const [ html, setHtml ] = useState( '' );

		const handleConvert = () => {
			if ( ! html ) return;

            // Call the global parser
            if ( window.ska && window.ska.bridge && window.ska.bridge.convert ) {
                // Remove the import block as it's no longer needed
                // We'll replace it with the converted blocks
                window.ska.bridge.convert( html, clientId );
            } else {
                alert( 'Ska Bridge Parser not loaded.' );
            }
		};

		return (
			<div { ...blockProps }>
                <Placeholder 
                    icon="cloud-upload" 
                    label="Ska html2tailwind"
                    instructions={__( 'Paste your HTML/Tailwind code here to automatically convert to Ska Blocks.', 'ska-no-code-design' )}
                >
                    <TextareaControl
                        label="Source HTML"
                        value={ html }
                        onChange={ ( value ) => setHtml( value ) }
                        rows={ 10 }
                    />
                    <Button 
                        isPrimary 
                        onClick={ handleConvert }
                        disabled={ ! html }
                    >
                        Chuyển đổi ngay ✨
                    </Button>
                </Placeholder>
			</div>
		);
	},
	save: () => null} );

