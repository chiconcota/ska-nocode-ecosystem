<?php
require 'wp-load.php';
$ref = new ReflectionClass('Ska\Builder\Design\Ska_Template_Router');
echo $ref->getFileName();
