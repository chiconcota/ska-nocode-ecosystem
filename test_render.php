<?php
require_once __DIR__ . '/wp-load.php';

$html = '<!-- wp:ska-builder/container {"tailwindClasses":"hidden sm:mb-8 sm:flex sm:justify-center"} -->
<!-- wp:ska-builder/container {"tailwindClasses":"relative rounded-full px-3 py-1 text-sm/6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20"} -->
<!-- wp:ska-builder/text {"content":"Announcing our next round of funding.","tagName":"span"} /-->

<!-- wp:ska-builder/button {"text":"Read more \u2192","tailwindClasses":"font-semibold text-indigo-600"} /-->
<!-- /wp:ska-builder/container -->
<!-- /wp:ska-builder/container -->';

echo do_blocks( $html );
