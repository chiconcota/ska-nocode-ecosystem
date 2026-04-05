<?php
/**
 * Render block ska-builder/form
 */

$tailwindClasses = isset($attributes['tailwindClasses']) ? $attributes['tailwindClasses'] : '';
$className = isset($attributes['className']) ? $attributes['className'] : '';
$customStyle = isset($attributes['customStyle']) ? $attributes['customStyle'] : '';
$actionName = isset($attributes['actionName']) ? $attributes['actionName'] : 'default_form_submit';
$formMethod = isset($attributes['formMethod']) ? $attributes['formMethod'] : 'POST';
$formAction = isset($attributes['formAction']) ? $attributes['formAction'] : '';

$fullClasses = trim($tailwindClasses . ' ' . $className);

$wrapper_attrs_args = [
    'class' => "ska-form-block {$fullClasses}",
    'data-ska-action' => esc_attr($actionName)
];

if (!empty($customStyle)) {
    $wrapper_attrs_args['style'] = $customStyle;
}

if (!empty($formAction)) {
    $wrapper_attrs_args['action'] = esc_url($formAction);
}

// Logic events often need POST intercept
$wrapper_attrs_args['method'] = esc_attr($formMethod);

$wrapper_attributes = get_block_wrapper_attributes($wrapper_attrs_args);
?>
<form <?php echo $wrapper_attributes; ?>>
    <?php echo $content; ?>
</form>
