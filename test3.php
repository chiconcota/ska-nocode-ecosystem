<?php
require 'wp-load.php';
$script = "{{#foreach ska_data_doctors.qualifications}}\n<option value=\"{{.}}\">{{.}}</option>\n{{/foreach}}";
try {
    $result = \Ska\Logic\SkaFX\SkaFX_Engine::execute($script, []);
    var_dump($result);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
} catch (Throwable $e) {
    echo "Throwable: " . $e->getMessage();
}
