import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<Placeholder
                icon="update"
                label="Ska Query Loop"
                instructions="Giao diện React (Frontend) sẽ được xây dựng ở Phase tiếp theo. Hiện tại Backend Core đã sẵn sàng render."
            />
		</div>
	);
}
