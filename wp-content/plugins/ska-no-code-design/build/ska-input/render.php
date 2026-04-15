<?php
/**
 * Render block ska-builder/input
 * Tự động inject x-model để liên kết dữ liệu với Alpine skaForm Controller.
 */

$tailwindClasses = isset($attributes['tailwindClasses']) ? $attributes['tailwindClasses'] : '';
$className = isset($attributes['className']) ? $attributes['className'] : '';
$customStyle = isset($attributes['customStyle']) ? $attributes['customStyle'] : '';
$fieldName = isset($attributes['fieldName']) ? $attributes['fieldName'] : 'my_field';
$fieldId = isset($attributes['fieldId']) ? $attributes['fieldId'] : '';
$inputType = isset($attributes['inputType']) ? $attributes['inputType'] : 'text';
$fieldValue = isset($attributes['fieldValue']) ? $attributes['fieldValue'] : '';
$placeholder = isset($attributes['placeholder']) ? $attributes['placeholder'] : '';
$isChecked = isset($attributes['isChecked']) ? $attributes['isChecked'] : false;
$isRequired = isset($attributes['isRequired']) ? $attributes['isRequired'] : false;

$fullClasses = trim($tailwindClasses . ' ' . $className);

$wrapper_attrs_args = [
	'class' => "ska-input-block {$fullClasses}",
	'type' => esc_attr($inputType),
	'name' => esc_attr($fieldName)
];

if (in_array($inputType, ['text', 'email', 'number', 'password']) && !empty($placeholder)) {
	$wrapper_attrs_args['placeholder'] = esc_attr($placeholder);
}

if (!empty($fieldValue)) {
	$wrapper_attrs_args['value'] = esc_attr($fieldValue);
}

if (!empty($customStyle)) {
	$wrapper_attrs_args['style'] = $customStyle;
}

if (!empty($fieldId)) {
	$wrapper_attrs_args['id'] = esc_attr($fieldId);
}

if ($isChecked && in_array($inputType, ['checkbox', 'radio'])) {
	$wrapper_attrs_args['checked'] = 'checked';
}

if ($isRequired) {
	$wrapper_attrs_args['required'] = 'required';
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_attrs_args);

// === AUTO-INJECT x-model cho Alpine skaForm ===
// Khi Input nằm bên trong một Ska Form (Container tag=form + isSkaForm=true),
// Alpine.js sẽ tự quét x-model để liên kết dữ liệu 2 chiều.
// Cách hoạt động: render.php in ra x-model, Alpine Controller đón nhận binding.
$alpine_bind = sprintf( ' x-model="fields.%s"', esc_attr( $fieldName ) );

// Validation: Thêm @blur để kiểm tra lỗi khi user rời ô nhập
$alpine_validation = '';
if ( $isRequired ) {
	$alpine_validation = sprintf( ' @blur="validate(\'%s\')"', esc_attr( $fieldName ) );
}

// Ghép thêm hiển thị lỗi validation dưới input
$error_display = sprintf(
	'<span x-show="errors.%1$s" x-text="errors.%1$s" class="ska-form-error" style="color:#ef4444;font-size:0.75rem;display:block;margin-top:0.25rem;"></span>',
	esc_attr( $fieldName )
);
?>
<input <?php echo $wrapper_attributes . $alpine_bind . $alpine_validation; ?> />
<?php echo $error_display; ?>
