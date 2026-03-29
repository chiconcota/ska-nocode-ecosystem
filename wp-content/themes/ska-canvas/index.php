<?php
/**
 * Ska Canvas Theme - Fallback Template
 * 
 * @package SkaCanvas
 * @version 1.0.0
 * 
 * ⚠️ RULES OF SKA CANVAS:
 * This file is intentionally barren. It acts as a blank skeleton.
 * Future Header and Footer designs will hook into `ska_theme_header` 
 * and `ska_theme_footer` dynamically via the Ska App Builder Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'ska-builder ska-canvas' ); ?>>

<?php wp_body_open(); ?>

	<?php
	// Template Hook: Plugin (Theme Builder) will inject the Global Header here.
	do_action( 'ska_theme_header' );
	?>

	<main id="primary" class="ska-main-content">
		
		<?php do_action( 'ska_theme_content_before' ); ?>

		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
		else :
			// Minimal fallback for 404 or empty content
			echo '<p style="text-align: center; padding: 4rem;">Không tìm thấy nội dung.</p>';
		endif;
		?>

		<?php do_action( 'ska_theme_content_after' ); ?>

	</main>

	<?php
	// Template Hook: Plugin (Theme Builder) will inject the Global Footer here.
	do_action( 'ska_theme_footer' );
	?>

<?php wp_footer(); ?>

</body>
</html>
