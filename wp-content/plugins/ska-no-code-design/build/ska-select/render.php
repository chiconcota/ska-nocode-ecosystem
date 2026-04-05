<?php
/**
 * Render block ska-builder/select
 */

$tailwindClasses = isset($attributes['tailwindClasses']) ? $attributes['tailwindClasses'] : '';
$className = isset($attributes['className']) ? $attributes['className'] : '';
$customStyle = isset($attributes['customStyle']) ? $attributes['customStyle'] : '';
$fieldName = isset($attributes['fieldName']) ? $attributes['fieldName'] : 'my_select';
$optionsText = isset($attributes['optionsText']) ? $attributes['optionsText'] : '';
$isRequired = isset($attributes['isRequired']) ? $attributes['isRequired'] : false;

$fullClasses = trim($tailwindClasses . ' ' . $className);

$wrapper_attrs_args = [
    'class' => "ska-select-block {$fullClasses}",
    'name' => esc_attr($fieldName)
];

if (!empty($customStyle)) {
    $wrapper_attrs_args['style'] = $customStyle;
}
if ($isRequired) {
    $wrapper_attrs_args['required'] = 'required';
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_attrs_args);

// Parse options
$lines = array_filter(array_map('trim', explode("\n", $optionsText)));
$optionsList = [];
foreach ($lines as $line) {
    $parts = explode(':', $line, 2);
    $label = trim($parts[0]);
    // If no colon, value is same as label
    $value = isset($parts[1]) ? trim($parts[1]) : $label;
    $optionsList[] = ['label' => $label, 'value' => $value];
}
?>
<select <?php echo $wrapper_attributes; ?>>
    <?php foreach ($optionsList as $opt): ?>
        <option value="<?php echo esc_attr($opt['value']); ?>">
            <?php echo esc_html($opt['label']); ?>
        </option>
    <?php endforeach; ?>
</select>
