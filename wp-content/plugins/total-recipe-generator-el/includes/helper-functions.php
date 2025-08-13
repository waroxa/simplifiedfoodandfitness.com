<?php
/**
 * Total Recipe Generator Functions
 *
 * @package Total_Recipe_Generator_El
 * @since 1.0.0
 * @version 3.1.0
 */


/**
 * Create list item and links for items
 * selected for cuisine, course and cooking method
 *
 * @params $list_main (array), $list_other(array), $schema_prop(string), $link(boolean)
 * @return array
 */
if ( ! function_exists ( 'trg_el_create_list_items' ) ) :
	function trg_el_create_list_items( $list_main = '', $list_other = '', $schema_prop = '', $link = false, $tax_optional = '' ) {

		$rcu = $rcuo = $rcat = $rcato = $temp = array();
		$rcu_out = $rcato = $tag = $tag_link = $cat_link = $term_link = '';
		if ( is_array( $list_main ) ) {
			$rcu = $list_main;
		} else {
			$rcu = explode( ',', $list_main );
		}
		if ( '' !== $list_other ) {
			$rcuo = explode( ',', $list_other );
		}
		$temp = array_merge( $rcu, $rcuo );
		if ( is_array( $temp ) ) {
			foreach( $temp as $rcu_item ) {
				if ( '' != $rcu_item ) {
					if ( $link ) {
						$tag_link = get_term_by( 'name', $rcu_item, 'post_tag' );
						$cat_link = get_term_by( 'name', $rcu_item, 'category' );
						$term_link = get_term_by( 'name', $rcu_item, $tax_optional );

						if ( isset( $tag_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled" itemprop="%1$s"><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
								$schema_prop,
								get_term_link( $tag_link->term_id, 'post_tag' ),
								sprintf( __( 'View all recipies tagged %s', 'trg_el' ), $rcu_item ),
								$rcu_item
							);
						}

						// Check if a category is available
						elseif ( isset( $cat_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled" itemprop="%1$s"><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
								$schema_prop,
								get_term_link( $cat_link->term_id, 'category' ),
								sprintf( __( 'View all recipies in %s', 'trg_el' ), $rcu_item ),
								$rcu_item
							);
						}

						// Check if custom taxonomy link is available
						elseif ( isset( $term_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled" itemprop="%1$s"><a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
								$schema_prop,
								get_term_link( $term_link->term_id, $tax_optional ),
								sprintf( __( 'View all recipies in %s', 'trg_el' ), $rcu_item ),
								$rcu_item
							);
						}

						else {
							$rcu_out .= '<li class="cm-value" itemprop="' . $schema_prop . '">' . $rcu_item . '</li>';
						}
					}
					else {
						$rcu_out .= '<li class="cm-value" itemprop="' . $schema_prop . '">' . $rcu_item . '</li>';
					}
				}
			}
		}

		return array( 'html' => $rcu_out, 'arr' => $temp );

	}
endif;

/**
 * Create list item and links for items
 * selected for "suitable for diet"
 *
 * @params $list_main (array), $link(boolean)
 * @return array
 */
if ( ! function_exists ( 'trg_el_create_diet_items' ) ) :
	function trg_el_create_diet_items( $list_main = '', $list_other = '', $link = false, $tax_optional = '' ) {

		if ( '' == $list_main && '' == $list_other ) {
			return;
		}

		$diet_labels = [
                'DiabeticDiet' => __( 'Diabetic', 'trg_el' ),
                'GlutenFreeDiet' => __( 'Gluten Free', 'trg_el' ),
                'HalalDiet' => __( 'Halal', 'trg_el' ),
                'HinduDiet' => __( 'Hindu', 'trg_el' ),
                'KosherDiet' => __( 'Kosher', 'trg_el' ),
                'LowCalorieDiet' => __( 'Low Calorie', 'trg_el' ),
                'LowFatDiet' => __( 'Low Fat', 'trg_el' ),
                'LowLactoseDiet' => __( 'Low Lactose', 'trg_el' ),
                'LowSaltDiet' => __( 'Low Salt', 'trg_el' ),
                'VeganDiet' => __( 'Vegan', 'trg_el' ),
                'VegetarianDiet' => __( 'Vegetarian', 'trg_el' )
		];

		$allowed_diet_schema = apply_filters( 'trg_allowed_diet_schema', [
                'DiabeticDiet',
                'GlutenFreeDiet',
                'HalalDiet',
                'HinduDiet',
                'KosherDiet',
                'LowCalorieDiet',
                'LowFatDiet',
                'LowLactoseDiet',
                'LowSaltDiet',
                'VeganDiet',
                'VegetarianDiet'
		] );

		$sfda = $sfdo = $links_arr = array();
		$rcu_out = '';
		$return_arr = array();


		if ( is_array( $list_main ) ) {
			$sfda = $list_main;
		} else {
			$sfda = explode( ',', $list_main );
		}
		if ( '' !== $list_other ) {
			$sfdo = explode( ',', $list_other );
		}

		$chosen_sfd = [];
		foreach ( $sfda as $key ) {
		    if ( array_key_exists( $key, $diet_labels ) ) {
		        $chosen_sfd[ $key ] = $diet_labels[ $key ];
		    }
		}

		$rcu = array_merge( $chosen_sfd, $sfdo );

		if ( is_array( $rcu ) && ! empty( $rcu ) ) {
			foreach( $rcu as $key => $val ) {
				//$sfd = str_replace( ' ', '', $val );
				//$val = trim( $val );
				$schema_prop = '';
				if ( ! is_numeric( $key ) && in_array( $key, $allowed_diet_schema ) ) {
					$schema_prop = '<link itemprop="suitableForDiet" href="http://schema.org/' . $key . '" />';
					$links_arr[] = 'http://schema.org/' . $key;
				}

				if ( $link ) {
					$tag_link = get_term_by( 'name', $val, 'post_tag' );
					$cat_link = get_term_by( 'name', $val, 'category' );
					$term_link = get_term_by( 'name', $val, $tax_optional );

					// Check if a tag is available
					if ( isset( $tag_link->term_id ) ) {
						$rcu_out .= sprintf( '<li class="cm-value link-enabled">%1$s<a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
							$schema_prop,
							get_term_link( $tag_link->term_id, 'post_tag' ),
							sprintf( __( 'View all recipies tagged %s', 'trg_el' ), $val ),
							$val
						);
					}

					// Check if a category is available
					elseif ( isset( $cat_link->term_id ) ) {
						$rcu_out .= sprintf( '<li class="cm-value link-enabled">%1$s<a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
							$schema_prop,
							get_term_link( $cat_link->term_id, 'category' ),
							sprintf( __( 'View all recipies in %s', 'trg_el' ), $val ),
							$val
						);
					}

					// Check if custom taxonomy link is available
					elseif ( isset( $term_link->term_id ) ) {
						$rcu_out .= sprintf( '<li class="cm-value link-enabled">%1$s<a href="%2$s" title="%3$s" target="_blank">%4$s</a></li>',
							$schema_prop,
							get_term_link( $term_link->term_id, $tax_optional ),
							sprintf( __( 'View all recipies in %s', 'trg_el' ), $val ),
							$val
						);
					}

					// Else no link
					else {
						$rcu_out .= sprintf( '<li class="cm-value">%1$s%2$s</li>',
							$schema_prop,
							$val
						);
					}
				}
				else {
					$rcu_out .= sprintf( '<li class="cm-value">%1$s%2$s</li>',
						$schema_prop,
						$val
					);
				}
			}
		}
		if ( '' !== $rcu_out ) {
			$return_arr['html'] = $rcu_out;
		}
		if ( '' !== $rcu ) {
			$return_arr['arr'] = $links_arr;
		}
		return $return_arr;
	}
endif;

/**
 * Create row of nutrient with nutritional value
 *
 * @params $nutrition (array)
 * @return string
 */
if ( ! function_exists ( 'trg_el_nutrient_items' ) ) :
	function trg_el_nutrient_items( $nutrition = array() ) {

		$nu_out = '';
		$schema_prop = '';

		if ( is_array( $nutrition ) ) {
			foreach( $nutrition as $nu ) {
				$nu_out .= '<li><span class="label">' . esc_attr( $nu['nutrient_label'] ) . '</span><span itemprop="' . esc_attr( $nu['nutrient'] ) . '">' . esc_attr( $nu['amount'] ). '</span></li>';
			}
		}
		return $nu_out;
	}
endif;

/**
 * Convert time in minutes into hour
 *
 * @params $time_in_min (string|int)
 * @return array
 */

if ( ! function_exists ( 'trg_el_time_convert' ) ) :
	function trg_el_time_convert( $time_in_min = '' ) {
		$hr = $min = 0;
		$arr = array( 'schema' => '', 'readable' => '' );
		$readable = $out = '';
		if ( isset( $time_in_min ) ) {
			if ( (int)$time_in_min >= 60 ) {
				$hr = floor( $time_in_min / 60 );
				$min = $time_in_min % 60;
			}

			else {
				$min = $time_in_min % 60;
			}

			if ( (int)$hr > 0 && (int)$min <= 0 ) {
				$out = $hr . 'H';
				$readable = sprintf( _x( '%s hr', 'xx hours', 'trg_el' ), number_format_i18n( $hr ) );
			}

			elseif ( (int)$hr <= 0 && (int)$min > 0 ) {
				$out = $min . 'M';
				$readable = sprintf( _x( '%s min', 'xx minutes', 'trg_el' ), number_format_i18n( $min ) );
			}

			elseif ( (int)$hr > 0 && (int)$min > 0 ) {
				$out = $hr . 'H' . $min . 'M';
				$readable = sprintf( _x( '%1$s hr %2$s min', 'xx hour yy minutes', 'trg_el' ), number_format_i18n( $hr ), number_format_i18n( $min ) );
			}

			$arr[ 'schema' ] = 'PT' . $out;
			$arr[ 'readable' ] = $readable;
		}
		return $arr;
	}
endif;


/**
 * Add OG meta tags in head section
 * Required for social sharing feature
 */
function trg_el_add_og_site_tag() {
	if ( apply_filters( 'trg_add_og_tags', true ) ) {
		if ( is_single() || is_page() ) {
			global $post;
			setup_postdata( $post );
			$image = '';
			if ( has_post_thumbnail( $post->ID ) ) {
				$image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
			} else {
				$image = trg_get_first_image();
			}
			?>

			<!-- OG tags for social sharing -->
			<meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>"/>
			<meta property="og:type" content="article"/>
			<meta property="og:image" content="<?php echo esc_url( get_post_meta( $post->ID, 'trg_share_image', true ) ); ?>"/>
			<meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>"/>
			<meta property="og:description" content="<?php echo strip_tags( get_post_meta( $post->ID, 'trg_share_desc', true ) ); ?>"/>
			<meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>
			<?php wp_reset_postdata();
		}
	}
}
add_action( 'wp_head', 'trg_el_add_og_site_tag', 99 );

/**
 * Social Sharing feature for recipe post
 */
if ( ! function_exists( 'trg_el_social_sharing' ) ) :
	function trg_el_social_sharing( $sharing_buttons, $social_sticky = '' ) {
		global $post;
		setup_postdata( $post );

		// Set variables
		$out = '';
		$list = '';
		$share_image = '';
		$protocol = is_ssl() ? 'https' : 'http';

		if ( has_post_thumbnail( $post->ID ) ) {
			$share_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ), 'full' );
		} else {
			$share_image = trg_get_first_image();
		}

		$share_content = strip_tags( get_the_excerpt() );
		$btn_count = count( $sharing_buttons );

		if ( in_array( 'whatsapp', $sharing_buttons ) ) {
			if ( ! wp_is_mobile() ) {
				$btn_count--;
			}
		}

		$out .= sprintf( '<div id="trg-social-sharing" class="trg-sharing-container%s btns-%s">',
			'true' == $social_sticky ? ' trg-social-sticky' : '',
			$btn_count
		);

		$out .= '<ul class="trg-sharing clearfix">';

		foreach ( $sharing_buttons as $button ) {

			switch( $button ) {

				case 'twitter':
					$list .= sprintf( '<li class="trg-twitter"><a href="%s://twitter.com/home?status=%s" target="_blank" title="%s"><i class="fa-brands fa-square-x-twitter"></i><span class="sr-only">twitter x</span></a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on X (twitter)', 'trg_el' ) );
				break;

				case 'facebook':
					$list .= sprintf( '<li class="trg-facebook"><a href="%s://www.facebook.com/sharer/sharer.php?u=%s" target="_blank" title="%s"><i aria-hidden="true" class="fa-brands fa-facebook"></i><span class="sr-only">facebook</span></a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on facebook', 'trg_el' ) );
				break;

				case 'whatsapp':
					if ( wp_is_mobile() ) {
						$list .= sprintf( '<li class="trg-whatsapp"><a href="whatsapp://send?text=%s" title="%s" data-action="share/whatsapp/share"><i aria-hidden="true" class="fab fa-whatsapp"></i><span class="sr-only">whatsapp</span></a></li>', urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Whatsapp', 'trg_el' ) );
					}
				break;

				case 'linkedin':
					$list .= sprintf( '<li class="trg-linkedin"><a href="%s://www.linkedin.com/shareArticle?mini=true&amp;url=%s" target="_blank" title="%s"><i aria-hidden="true" class="fa-brands fa-linkedin"></i><span class="sr-only">linkedin</span></a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share on LinkedIn', 'trg_el' ) );
				break;

				case 'pinterest':
					$list .= sprintf( '<li class="trg-pint"><a href="%s://pinterest.com/pin/create/button/?url=%s&amp;media=%s" target="_blank" title="%s"><i aria-hidden="true" class="fa-brands fa-pinterest"></i><span class="sr-only">pinterest</span></a></li>',
						esc_attr( $protocol ),
						urlencode( esc_url( get_permalink() ) ),
						esc_url( $share_image ),
						esc_attr__( 'Pin it', 'trg_el' )
					);
				break;

				case 'vkontakte':
					$list .= sprintf( '<li class="trg-vk"><a href="%s://vkontakte.ru/share.php?url=%s" target="_blank" title="%s"><i aria-hidden="true" class="fa-brands fa-vk"></i><span class="sr-only">vkontakte</span></a></li>', esc_attr( $protocol ), urlencode( esc_url( get_permalink() ) ), esc_attr__( 'Share via VK', 'trg_el' ) );
				break;

				case 'email':
					$list .= sprintf( '<li class="trg-mail"><a href="mailto:someone@example.com?Subject=%s" title="%s"><i aria-hidden="true" class="fa-solid fa-envelope"></i><span class="sr-only">email</span></a></li>', urlencode( esc_attr( get_the_title() ) ), esc_attr__( 'Email this', 'trg_el' ) );

				break;

				case 'print':
					$list .= sprintf( '<li class="trg-print"><a id="trg-print-btn" href="#" title="%s"><i aria-hidden="true" class="fa-solid fa-print"></i><span class="sr-only">print</span></a></li>', esc_attr__( 'Print', 'trg_el' ) );
				break;

				case 'reddit':
					$list .= sprintf( '<li class="trg-reddit"><a href="//www.reddit.com/submit" onclick="window.location = \'//www.reddit.com/submit?url=\' + encodeURIComponent(window.location); return false" title="%s"><i aria-hidden="true" class="fa-brands fa-reddit-alien"></i><span class="sr-only">reddit</span><span class="sr-only">reddit</span></a></li>', esc_attr__( 'Reddit', 'trg_el' ) );
				break;
			} // switch

		} // foreach

		// Support extra meta items via action hook
		ob_start();
		do_action( 'trg_sharing_buttons_li' );
		$out .= ob_get_contents();
		ob_end_clean();

		$out .= $list . '</ul></div>';

		return $out;
	}
endif;

if ( ! function_exists( 'trg_get_first_image' ) ) {
	function trg_get_first_image() {
		global $post;
		$first_img = '';
		ob_start();
		ob_end_clean();
		$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
		if ( isset( $matches ) && is_array( $matches ) && isset( $matches[1][0] ) ) {
			$first_img = $matches[1][0];
		}
		return $first_img;
	}
}
if ( ! function_exists( 'trg_el_recipe_image' ) ) {
	function trg_el_recipe_image( $args = array() ) {
		$defaults = array(
			'imgsize'			=> 'custom',
			'img_src'           => 'featured', //media_lib, ext
			'img_lib'           => '',
			'imgwidth'          => '',
			'imgheight'         => '',
			'imgcrop'           => '',
			'imgquality'        => '',
			'img_align'         => 'none'
		);
		shortcode_atts( $defaults, $args );
		$img_obj = $image = '';

		// Custom image resize
		if ( 'custom' == $args['imgsize'] ) {
			$img_args = array( intval( $args['imgwidth'] ), intval( $args['imgheight'] ) );
			if ( $args['imgquality'] || $args['imgcrop'] ) {
				$img_args['bfi_thumb'] = true;
				$img_args['crop'] = $args['imgcrop'];
				$img_args['quality'] = $args['imgquality'];
			}
		}
	 	if ( 'featured' == $args['img_src'] && has_post_thumbnail() ) {
			printf( '<div class="%srecipe-image%s">%s%s</div>',
				'' !== get_the_post_thumbnail_caption() ? 'wp-caption ' : '',
				'hide' == $args['img_src'] ? ' print-only' : '',
				get_the_post_thumbnail(
					get_the_id(),
					'custom' == $args['imgsize'] ? $img_args : $args['imgsize'],
					array( 'itemprop' => 'image', 'class' => 'trg-image' )
				),
				'' !== get_the_post_thumbnail_caption() ? '<p class="wp-caption-text">' . get_the_post_thumbnail_caption() . '</p>' : ''
			);
	 	}

	 	elseif ( 'media_lib' == $args['img_src'] && isset( $args['img_lib']['id'] ) ) {
	 		$image_alt = get_post_meta( $args['img_lib']['id'], '_wp_attachment_image_alt', true );
	 		$src_size = ( 'custom' == $args['imgsize'] ) ? array( intval( $args['imgwidth'] ), intval( $args['imgheight'] ) ) : $args['imgsize'];
			$srcset = wp_get_attachment_image_srcset( $args['img_lib']['id'], $src_size );
			$caption = trg_el_post_thumbnail_caption( $args['img_lib']['id'] );
			$image = wp_get_attachment_image_src( $args['img_lib']['id'], $src_size );
			$image = $image[0];
			if ( 'custom' == $args['imgsize'] && ( $args['imgquality'] || $args['imgcrop'] ) ) {
				$image = wp_get_attachment_image_src( $args['img_lib']['id'], $img_args );
				$image = $image[0];
			}

			$caption_flag = ( isset( $caption ) && '' !== $caption ) ? 'true' : false;

			printf( '<div class="%srecipe-image%s">%s%s</div>',
				$caption_flag ? 'wp-caption ' : '',
				'hide' == $args['img_src'] ? ' print-only' : '',
				sprintf( '<img%s%s class="trg-image wp-post-image" src="%s"%s%s itemprop="image"/>',
					$args['imgwidth'] ? ' width="' . $args['imgwidth'] . '"' : '',
					$args['imgheight'] ? ' height="' . $args['imgheight'] . '"' : '',
					$image,
					! ( 'custom' == $args['imgsize'] && ( $args['imgquality'] || $args['imgcrop'] ) ) ? ' srcset="' . $srcset . '"' : '',
					isset( $image_alt ) && '' !== $image_alt ? ' alt="' . $image_alt . '"' : ''
				),
				$caption_flag ? '<p class="wp-caption-text">' . $caption . '</p>' : ''
			);
		}
	}
}

/**
 * Get post thumbnail caption
 */

if ( ! function_exists( 'trg_el_post_thumbnail_caption' ) ) {
	function trg_el_post_thumbnail_caption( $attachmet_id ) {
	  $thumbnail_image = get_posts( array( 'p' => $attachmet_id, 'post_type' => 'attachment' ) );

	  if ( $thumbnail_image && isset( $thumbnail_image[0] ) && ! empty( $thumbnail_image[0]->post_excerpt ) ) {
		return $thumbnail_image[0]->post_excerpt;
	  }
	}
}


/**
 * Create attributes list from user defined values
 *
 * @since 3.1.0
 */
if ( ! function_exists ( 'trg_el_create_atts' ) ) :
	function trg_el_create_atts( $list_other = '', $link = false ) {

		$rcu = $rcuo = $rcat = $rcato = $temp = array();
		$rcu_out = $rcato = $tag = $tag_link = '';
		if ( '' !== $list_other ) {
			$temp = explode( ',', $list_other );
		}
		if ( isset( $temp ) && is_array( $temp ) ) {
			foreach( $temp as $rcu_item ) {
				if ( '' != $rcu_item ) {
					if ( $link ) {
						$tag_link = get_term_by( 'name', $rcu_item, 'post_tag' );
						$cat_link = get_term_by( 'name', $rcu_item, 'category' );

						if ( isset( $tag_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled"><a href="%1$s" title="%2$s" target="_blank">%3$s</a></li>',
								get_term_link( $tag_link->term_id, 'post_tag' ),
								sprintf( __( 'View all recipies tagged %s', 'trg_el' ), $rcu_item ),
								$rcu_item
							);
						}

						// Check if a category is available
						elseif ( isset( $cat_link->term_id ) ) {
							$rcu_out .= sprintf( '<li class="cm-value link-enabled"><a href="%1$s" title="%2$s" target="_blank">%3$s</a></li>',
									get_term_link( $cat_link->term_id, 'category' ),
								sprintf( __( 'View all recipies in %s', 'trg_el' ), $rcu_item ),
								$rcu_item
							);
						}

						else {
							$rcu_out .= '<li class="cm-value">' . $rcu_item . '</li>';
						}
					}
					else {
						$rcu_out .= '<li class="cm-value">' . $rcu_item . '</li>';
					}
				}
			}
		}

		return array( 'html' => $rcu_out, 'arr' => $temp );

	}
endif;


function trg_el_get_all_image_sizes() {
	global $_wp_additional_image_sizes;
	$image_sizes = array();
	$default_image_sizes = get_intermediate_image_sizes();
	foreach ( $default_image_sizes as $size ) {
	    $image_sizes[$size] = array(
	        'width'  => intval( get_option( "{$size}_size_w" ) ),
	        'height' => intval( get_option( "{$size}_size_h" ) ),
	        'crop'   => get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false,
	    );
	}
	if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
	    $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
	}

	$size_options = array( 'custom' => __( 'Custom', 'trg_el' ), 'full' => __( 'Full', 'trg_el' ) );
	foreach( $image_sizes as $key => $val ) {
		$size_options[ $key ] = $key . ' - ' . $val['width'] . 'x' . $val['height'];
	}
	return $size_options;
}

function trg_social_placement( $social ) {
	if ( is_singular() && ( ! empty( $social['social_buttons'] ) ) ) {
		$social_sticky = isset( $social['social_sticky'] ) && 'on' == $social['social_sticky'] ? 'true' : '';
		if ( '' !== $social['social_heading'] ) {
			printf( '<h3 class="trg-social-heading%s">%s</h3>',
				$social_sticky ? ' hide-on-mobile' : '',
				'' !== $social['social_heading'] ? esc_attr( $social['social_heading'] ) : ''
			);
		}

		if ( is_array( $social['social_buttons'] ) && ! empty( $social['social_buttons'] ) ) {
			echo '<div class="trg-share-buttons">';
			echo trg_el_social_sharing( $social['social_buttons'], $social_sticky );
			echo '</div>';
		}
	}
}