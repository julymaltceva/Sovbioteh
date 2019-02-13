<?php
function dt_get_thumb_img( $opts = array() ) {
	global $post;

	$default_image = presscore_get_default_image();

	$defaults = array(
		'wrap' => '<a %HREF% %CLASS% %TITLE% %CUSTOM%><img %SRC% %IMG_CLASS% %SIZE% %ALT% %IMG_TITLE% /></a>',
		'class' => '',
		'alt' => '',
		'title' => '',
		'custom' => '',
		'img_class' => '',
		'img_title' => '',
		'img_description' => '',
		'img_caption' => '',
		'href' => '',
		'img_meta' => array(),
		'img_id' => 0,
		'options' => array(),
		'default_img' => $default_image,
		'prop' => false,
		'lazy_loading' => false,
		'lazy_class'    => 'lazy-load',
		'lazy_bg_class' => 'layzr-bg',
		'echo' => true,
	);
	$opts = wp_parse_args( $opts, $defaults );
	$opts = apply_filters('dt_get_thumb_img-args', $opts);

	$original_image = null;
	if ( $opts['img_meta'] ) {
		$original_image = $opts['img_meta'];
	} elseif ( $opts['img_id'] ) {
		$original_image = wp_get_attachment_image_src( $opts['img_id'], 'full' );
	}

	if ( !$original_image ) {
		$original_image = $opts['default_img'];
	}

	// proportion
	if ( $original_image && !empty($opts['prop']) && ( empty($opts['options']['h']) || empty($opts['options']['w']) ) ) {
		$_prop = $opts['prop'];
		$_img_meta = $original_image;

		if ( $_prop > 1 ) {
			$h = (int) floor((int) $_img_meta[1] / $_prop);
			$w = (int) floor($_prop * $h );
		} else if ( $_prop < 1 ) {
			$w = (int) floor($_prop * $_img_meta[2]);
			$h = (int) floor($w / $_prop );
		} else {
			$w = $h = min($_img_meta[1], $_img_meta[2]);
		}

		if ( !empty($opts['options']['w']) && $w ) {
			$__prop = $h / $w;
			$w = intval($opts['options']['w']);
			$h = intval(floor($__prop * $w));
		} else if ( !empty($opts['options']['h']) && $h ) {
			$__prop = $w / $h;
			$h = intval($opts['options']['h']);
			$w = intval(floor($__prop * $h));
		}

		$opts['options']['w'] = $w;
		$opts['options']['h'] = $h;
	}

	$src = '';
	$hd_src = '';
	$resized_image = $resized_image_hd = array();

	if ( $opts['options'] ) {

		$resized_image = dt_get_resized_img( $original_image, $opts['options'], true, false );
		$resized_image_hd = dt_get_resized_img( $original_image, $opts['options'], true, true );

		$hd_src = $resized_image_hd[0];
		$src = $resized_image[0];

		if ( $resized_image_hd[0] === $resized_image[0] ) {
			$resized_image_hd = array();
		}

	} else {
		$resized_image = $original_image;
		$src = $resized_image[0];
	}

	if ( $img_id = absint( $opts['img_id'] ) ) {

		if ( '' === $opts['alt'] ) {
			$opts['alt'] = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		}

		if ( '' === $opts['img_title'] ) {
			$opts['img_title'] = get_the_title( $img_id );
		}
	}

	$href = $opts['href'];
	if ( !$href ) {
		$href = $original_image[0];
	}

	$_width = $resized_image[1];
	$_height = $resized_image[2];

	if ( empty($resized_image[3]) || !is_string($resized_image[3]) ) {
		$size = image_hwstring( $_width, $_height );
	} else {
		$size = $resized_image[3];
	}

	$lazy_loading_src = "data:image/svg+xml,%3Csvg%20xmlns%3D&#39;http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg&#39;%20viewBox%3D&#39;0%200%20{$_width}%20{$_height}&#39;%2F%3E";

	$lazy_loading = ! empty( $opts['lazy_loading'] );
	$srcset_tpl = '%s %dw';

	if ( $lazy_loading ) {
		$src = str_replace( array(' '), array('%20'), $src );
		$hd_src = str_replace( array(' '), array('%20'), $hd_src );

		$esc_src = esc_attr( $src );
		$src_att = sprintf( $srcset_tpl, $esc_src, $resized_image[1] );
		if ( $resized_image_hd ) {
			$src_att .= ', ' . sprintf( $srcset_tpl, esc_attr( $hd_src ), $resized_image_hd[1] );
		}
		$src_att = 'src="' . $lazy_loading_src . '" data-src="' . $esc_src . '" data-srcset="' . $src_att . '"';
		$opts['img_class'] .= ' ' . $opts['lazy_class'];
		$opts['class'] .= ' ' . $opts['lazy_bg_class'];
	} else {
		$src_att = sprintf( $srcset_tpl, $src, $resized_image[1] );
		if ( $resized_image_hd ) {
			$src_att .= ', ' . sprintf( $srcset_tpl, $hd_src, $resized_image_hd[1] );
		}
		$src_sizes = $resized_image[1] . 'px';
		/*$src_att = 'src="' . esc_attr( $src ) . '" srcset="' . esc_attr( $src_att ) . '" sizes="' . esc_attr( $src_sizes ) . '"';*/
		$src_att = 'src="' . esc_attr( $src ) . '" ';
	}

	$class = empty( $opts['class'] ) ? '' : 'class="' . esc_attr( trim($opts['class']) ) . '"';
	$title = empty( $opts['title'] ) ? '' : 'title="' . esc_attr( trim($opts['title']) ) . '"';
	$img_title = empty( $opts['img_title'] ) ? '' : 'title="' . esc_attr( trim($opts['img_title']) ) . '"';
	$img_class = empty( $opts['img_class'] ) ? '' : 'class="' . esc_attr( trim($opts['img_class']) ) . '"';

	$output = str_replace(
		array(
			'%HREF%',
			'%CLASS%',
			'%TITLE%',
			'%CUSTOM%',
			'%SRC%',
			'%IMG_CLASS%',
			'%SIZE%',
			'%ALT%',
			'%IMG_TITLE%',
			'%RAW_TITLE%',
			'%RAW_ALT%',
			'%RAW_IMG_TITLE%',
			'%RAW_IMG_DESCRIPTION%',
			'%RAW_IMG_CAPTION%'
		),
		array(
			'href="' . esc_url( $href ) . '"',
			$class,
			$title,
			strip_tags( $opts['custom'] ),
			$src_att,
			$img_class,
			$size,
			'alt="' . esc_attr( $opts['alt'] ) . '"',
			$img_title,
			esc_attr( $opts['title'] ),
			esc_attr( $opts['alt'] ),
			esc_attr( $opts['img_title'] ),
			esc_attr( $opts['img_description'] ),
			esc_attr( $opts['img_caption'] )
		),
		$opts['wrap']
	);

	$output = apply_filters( 'dt_get_thumb_img-output', $output, $opts );

	if ( $opts['echo'] ) {
		echo $output;
		return '';
	}

	return $output;
}