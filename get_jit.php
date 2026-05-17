<?php
$html = file_get_contents('out.html');
if (preg_match("/<style id='ska-jit-styles'>(.*?)<\/style>/s", $html, $matches)) {
    echo $matches[1];
} else {
    echo "NOT FOUND";
}
