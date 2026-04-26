<?php
/**
 * Render block ska-builder/select
 */

$tailwindClasses = isset($attributes['tailwindClasses']) ? $attributes['tailwindClasses'] : '';
$className = isset($attributes['className']) ? $attributes['className'] : '';
$customStyle = isset($attributes['customStyle']) ? $attributes['customStyle'] : '';
$fieldName = !empty($attributes['fieldName']) ? $attributes['fieldName'] : '';
$optionsText = isset($attributes['optionsText']) ? $attributes['optionsText'] : '';
$isRequired = isset($attributes['isRequired']) ? $attributes['isRequired'] : false;

$displayStyle = isset($attributes['displayStyle']) ? $attributes['displayStyle'] : 'dropdown';
$isMultiple = isset($attributes['isMultiple']) ? $attributes['isMultiple'] : false;

$fullClasses = trim($tailwindClasses . ' ' . $className);

// Ensure name has [] if multiple
$outputName = $fieldName;
if (!empty($outputName) && $isMultiple && substr($outputName, -2) !== '[]') {
    $outputName .= '[]';
}

$wrapper_attrs_args = [
    'class' => "ska-choice-group {$fullClasses}"
];

if (!empty($customStyle)) {
    $wrapper_attrs_args['style'] = $customStyle;
}

if ($displayStyle === 'dropdown') {
	if (!empty($outputName)) {
    	$wrapper_attrs_args['name'] = esc_attr($outputName);
	}
    if ($isMultiple) {
        $wrapper_attrs_args['multiple'] = 'multiple';
    }
    if ($isRequired) {
        $wrapper_attrs_args['required'] = 'required';
    }
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_attrs_args);

// Parse options
$lines = array_filter(array_map('trim', explode("\n", $optionsText)));
$optionsList = [];
foreach ($lines as $line) {
    $parts = explode(':', $line, 2);
    $label = trim($parts[0]);
    $value = isset($parts[1]) ? trim($parts[1]) : $label;
    $optionsList[] = ['label' => $label, 'value' => $value];
}

// === AUTO-INJECT x-model cho Alpine skaForm ===
$alpine_bind = '';
$alpine_validation = '';
$error_display = '';

if (!empty($fieldName)) {
	$alpine_bind = sprintf(' x-model="fields.%s"', esc_attr($fieldName));
	if ($isRequired) {
		$alpine_validation = sprintf(' @change="validate(\'%s\')"', esc_attr($fieldName));
	}
	$error_display = sprintf(
		'<span x-show="errors.%1$s" x-text="errors.%1$s" class="ska-form-error" style="color:#ef4444;font-size:0.75rem;display:block;margin-top:0.25rem;"></span>',
		esc_attr($fieldName)
	);
}

?>
<?php if ($displayStyle === 'dropdown'): ?>
    <select <?php echo $wrapper_attributes . $alpine_bind . $alpine_validation; ?>>
        <?php foreach ($optionsList as $opt): ?>
            <option value="<?php echo esc_attr($opt['value']); ?>">
                <?php echo esc_html($opt['label']); ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php else: ?>
    <div <?php echo $wrapper_attributes; ?>>
        <?php
        $inputType = ($displayStyle === 'radio') ? 'radio' : 'checkbox';
        foreach ($optionsList as $opt):
            ?>
            <label class="ska-choice-item flex items-center gap-2 cursor-pointer">
                <input type="<?php echo $inputType; ?>" <?php if (!empty($outputName)) { echo 'name="' . esc_attr($outputName) . '"'; } ?>
                    value="<?php echo esc_attr($opt['value']); ?>" <?php echo $isRequired ? 'required' : ''; ?>         <?php echo $alpine_bind . $alpine_validation; ?> />
                <span><?php echo esc_html($opt['label']); ?></span>
            </label>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php echo $error_display; ?>