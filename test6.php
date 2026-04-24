<?php
$content = '<select class="foo"><option>x</option></select><span x-show="err"></span>';
$generated = '<option>new</option>';
$new = preg_replace('/(<select[^>]*>)(.*?)(<\/select>)/is', '${1}' . $generated . '${3}', $content);
echo "New:\n" . htmlspecialchars($new);
