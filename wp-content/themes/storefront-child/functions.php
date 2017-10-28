<?php
function storefront_child_style_scripts() {

    $parent_style = 'storefront'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'storefront_child_style_scripts' );

function storefront_child_remove_homepage_content(){
	remove_action('homepage', 'storefront_product_categories', 20);
	// remove_action('homepage', 'storefront_homepage_content', 10);
}
add_action('init', 'storefront_child_remove_homepage_content');

function storefront_child_header_content() { ?>
	<div style="clear: both; text-align: right;">
		Have questions about our products? <em>Give us a call:</em> <strong>0800 123 456</strong>
	</div>
	<?php
}
add_action( 'storefront_child_header_content', 'jk_storefront_header_content', 40 );



// function storefront_child_swap_homepage_sections() {
// 	remove_action('homepage', 'storefront_recent_products', 30);
// 	remove_action('homepage', 'storefront_featured_products', 40);
	
// 	add_action('homepage', 'storefront_featured_products', 30);
// 	add_action('homepage', 'storefront_recent_products', 40);

// }
// add_action('init', 'storefront_child_swap_homepage_sections')
?>