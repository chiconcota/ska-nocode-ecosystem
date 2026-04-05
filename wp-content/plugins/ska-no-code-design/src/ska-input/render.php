<?php
/**
 * Render block ska-builder/input
 */

$tailwindClasses = isset($attributes['tailwindClasses']) ? $attributes['tailwindClasses'] : '';
$className = isset($attributes['className']) ? $attributes['className'] : '';
$customStyle = isset($attributes['customStyle']) ? $attributes['customStyle'] : '';
$fieldName = isset($attributes['fieldName']) ? $attributes['fieldName'] : 'my_field';
$inputType = isset($attributes['inputType']) ? $attributes['inputType'] : 'text';
$fieldValue = isset($attributes['fieldValue']) ? $attributes['fieldValue'] : '';
$placeholder = isset($attributes['placeholder']) ? $attributes['placeholder'] : '';
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

if ($isRequired) {
    $wrapper_attrs_args['required'] = 'required';
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_attrs_args);
?>
<input <?php echo $wrapper_attributes; ?> />
