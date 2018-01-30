<?php
/**
 * Plugin Name: Storefront Add Slider
 * Plugin URI: https://atlantisthemes.com
 * Description: Lets you add any slider shortcode to your Storefront theme Frontpage.
 * Author: Atlantis Themes
 * Author URI: http://atlantisthemes.com
 * Version: 0.4
 * Text Domain: storefront-add-slider
 *
 *
 * Storefront Add Slider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Storefront Add Slider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'customize_register', 'pangolin_storefront_customize_register' );

function pangolin_storefront_customize_register( $wp_customize ) {

/**
 * Customizer Control For Pro Conversion
 */
class Custom_Subtitle extends WP_Customize_Control {

	public function render_content() { ?>

		<label>
		<?php if ( !empty( $this->label ) ) : ?>
			<span class="customize-control-title bellini-pro__title">
				<?php echo esc_html( $this->label ); ?>
			</span>
		<?php endif; ?>
		</label>

		<?php if ( !empty( $this->description ) ) : ?>
			<span class="description bellini-pro__description">
				<?php echo $this->description; ?>
			</span>
		<?php endif;
	}
}

	$wp_customize->add_setting('storefront_slider_shortcode_field', array(
			'type' 				=> 'theme_mod',
			'default'         	=> '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport' 		=> 'refresh',
	) );

			$wp_customize->add_control('storefront_slider_shortcode_field',array(
				'type' 			=>'text',
               'label'      	=> esc_html__( 'Slider Shortcode', 'storefront' ),
               'description' 	=> esc_html__( 'You can insert your Meta Slider, Smart Slider 3, Soliloquy, Revolution Slider, LayerSlider shortcode here.', 'storefront' ),
               'section'    	=> 'static_front_page',
               'settings'   	=> 'storefront_slider_shortcode_field',
			   'priority'  		=> 1,
			));

	// Show Frontpage Slider on All Pages
	$wp_customize->add_setting( 'storefront_slider_all_pages' ,
		array(
			'default' => false,
			'type' => 'theme_mod',
			'sanitize_callback' => 'sanitize_key',
			'transport' => 'refresh'
		)
	);

		$wp_customize->add_control( 'storefront_slider_all_pages',array(
				'label'      => esc_html__( 'Show Frontpage Slider on All Pages', 'storefront' ),
				'section'    => 'static_front_page',
				'settings'   => 'storefront_slider_all_pages',
			    'priority'   => 2,
			    'type'       => 'checkbox',
			)
		);


$third_party_slider_description = sprintf( __( 'Try <a target="_blank" href="%s">Storefront Design Customizer</a>, Customize Storefront without touching any code', 'storefront' ),esc_url( 'https://wordpress.org/plugins/storefront-design-customizer/' ));

	$wp_customize->add_setting( 'bellini_front_block_pro_conversion',
		array(
			'type' 				=> 'theme_mod',
			'sanitize_callback' => 'sanitize_key',
			)
	);
			$wp_customize->add_control( new Custom_Subtitle ( $wp_customize, 'bellini_front_block_pro_conversion',
				array(
					'label' => esc_html__('','storefront'),
					'description' => $third_party_slider_description,
					'section' => 'static_front_page',
					'settings'    => 'bellini_front_block_pro_conversion',
					'priority'   => 3,
			)) );

}

if (get_theme_mod('storefront_slider_all_pages') == true){
	add_action( 'storefront_before_content', 'pangolin_add_slider_storefront', 5);
}

add_action( 'homepage', 'pangolin_add_slider_storefront',      5 );

function pangolin_add_slider_storefront() { ?>
	<section class="front__slider">
	<?php
	if (get_theme_mod( 'storefront_slider_shortcode_field')){
		echo do_shortcode( html_entity_decode(get_theme_mod( 'storefront_slider_shortcode_field')) );
	}else{
		esc_html_e( 'No Slider Shortcode Found! ', 'storefront' );
	}
	?>
	</section>
<?php
}

