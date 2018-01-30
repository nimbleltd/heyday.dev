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
	remove_action('homepage', 'storefront_recent_products', 30);
	remove_action('homepage', 'storefront_featured_products', 40);
	remove_action('homepage', 'storefront_on_sale_products', 60);
	remove_action('homepage', 'storefront_best_selling_products', 70);
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


add_action( 'init', 'jk_remove_storefront_handheld_footer_bar' );

function jk_remove_storefront_handheld_footer_bar() {
  remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
}

// Customize the footer copyright portion
add_action( 'init', 'custom_remove_footer_credit', 10 );

function custom_remove_footer_credit () {
    remove_action( 'storefront_footer', 'storefront_credit', 20 );
    add_action( 'storefront_footer', 'custom_storefront_credit', 20 );
}

function custom_storefront_credit() {
    ?>
    <div class="site-info">
        &copy;HeyDay! <?php echo get_the_date( 'Y' ); ?> <br>Built by Nimble Ltd.
    </div><!-- .site-info -->
    <?php
}

// function storefront_child_swap_homepage_sections() {
// 	remove_action('homepage', 'storefront_recent_products', 30);
// 	remove_action('homepage', 'storefront_featured_products', 40);
	
// 	add_action('homepage', 'storefront_featured_products', 30);
// 	add_action('homepage', 'storefront_recent_products', 40);

// }
// add_action('init', 'storefront_child_swap_homepage_sections')

// ADD USER ROLE – YOU CAN REMOVE CODE AFTER 1ST RUN
// add_role( 'wholesale', 'Wholesale Customer', array(
//     'read' => true
// )); 
?>