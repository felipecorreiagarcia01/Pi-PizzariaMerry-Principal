<?php
function rasam_css() {
	$parent_style = 'appetizer-parent-style';
	wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'rasam-style', get_stylesheet_uri(), array( $parent_style ));
	
	wp_enqueue_style('rasam-responsive',get_stylesheet_directory_uri().'/assets/css/responsive.css');
	wp_dequeue_style('appetizer-responsive');

}
add_action( 'wp_enqueue_scripts', 'rasam_css',999);
   	
	
if ( ! function_exists( 'rasam_setup' ) ) :
function rasam_setup() {	
	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary_menu2' => esc_html__( 'Primary Menu 2', 'rasam' ),
	) );
}
endif;
add_action( 'after_setup_theme', 'rasam_setup' );


if ( ! function_exists( 'rasam_header_menu_navigation2' ) ) {
	function rasam_header_menu_navigation2() {
			wp_nav_menu( 
				array(  
					'theme_location' => 'primary_menu2',
					'container'  => '',
					'menu_class' => 'main-menu',
					'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
					'walker' => new WP_Bootstrap_Navwalker()
					 ) 
				);
	}
}

/**
 * Import Options From Parent Theme
 *
 */
function rasam_parent_theme_options() {
	$appetizer_mods = get_option( 'theme_mods_appetizer' );
	if ( ! empty( $appetizer_mods ) ) {
		foreach ( $appetizer_mods as $appetizer_mod_k => $appetizer_mod_v ) {
			set_theme_mod( $appetizer_mod_k, $appetizer_mod_v );
		}
	}
}
add_action( 'after_switch_theme', 'rasam_parent_theme_options' );

require( get_stylesheet_directory() . '/inc/customizer/customizer-pro/class-customize.php');