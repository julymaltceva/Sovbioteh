<?php
/**
 * The7 theme.
 * @package The7
 * @since   1.0.0
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set the content width based on the theme's design and stylesheet.
 * @since 1.0.0
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1200; /* pixels */
}

/**
 * Initialize theme.
 * @since 1.0.0
 */
require( trailingslashit( get_template_directory() ) . 'inc/init.php' );

function presscore_get_image_with_srcset( $regular, $retina, $default, $custom = '', $class = '' ) {
		$srcset = array();

		foreach ( array( $regular, $retina ) as $img ) {
			if ( $img ) {
				$srcset[] = "{$img[0]} {$img[1]}w";
			}
		}

		$output = '<img class="' . esc_attr( $class . ' preload-me' ) . '" src="' . esc_attr( $default[0] ) . '" ' . image_hwstring( $default[1], $default[2] ) . ' ' . $custom . ' />';

		return $output;
	}

	/**
 * Отключаем srcset и sizes для картинок в WordPress
 */

// Отменяем srcset
// выходим на раннем этапе, этот фильтр лучше чем 'wp_calculate_image_srcset'
add_filter('wp_calculate_image_srcset_meta', '__return_null' );

// Отменяем sizes - это поздний фильтр, но раннего как для srcset пока нет...
add_filter('wp_calculate_image_sizes', '__return_false',  99 );

// Удаляем фильтр, который добавляет srcset ко всем картинкам в тексте записи
remove_filter('the_content', 'wp_make_content_images_responsive' );

// Очищаем атрибуты из wp_get_attachment_image(), если по каким-то причинам они там остались...
add_filter('wp_get_attachment_image_attributes', 'unset_attach_srcset_attr', 99 );
function unset_attach_srcset_attr( $attr ){
	foreach( array('sizes','srcset') as $key )
		if( isset($attr[ $key ]) )    unset($attr[ $key ]);
	return $attr;
}

/**
 * Disable responsive image support (test!)
 */

// Clean the up the image from wp_get_attachment_image()
add_filter( 'wp_get_attachment_image_attributes', function( $attr )
{
    if( isset( $attr['sizes'] ) )
        unset( $attr['sizes'] );

    if( isset( $attr['srcset'] ) )
        unset( $attr['srcset'] );

    return $attr;

 }, PHP_INT_MAX );

// Override the calculated image sizes
add_filter( 'wp_calculate_image_sizes', '__return_empty_array',  PHP_INT_MAX );

// Override the calculated image sources
add_filter( 'wp_calculate_image_srcset', '__return_empty_array', PHP_INT_MAX );

// Remove the reponsive stuff from the content
remove_filter( 'the_content', 'wp_make_content_images_responsive' );