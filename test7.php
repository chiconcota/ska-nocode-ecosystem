<?php
$block_content = '<select class="ska-choice-group">
<option value=""></option>
</select>
<span x-show="errors." class="ska-form-error"></span>';
$generated_html = '<option value="a">A</option>';
if ( strpos( $block_content, '<select' ) !== false ) {
    $block_content = preg_replace( '/(<select[^>]*>)(.*?)(<\/select>)/is', '${1}' . $generated_html . '${3}', $block_content );
}
echo htmlspecialchars($block_content);
