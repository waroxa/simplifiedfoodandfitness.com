<?php
/**
 * Template part for the output of recipe shortcode
 *
 * You can override this template by
 * copying the file to /wp-content/theme_folder/trg-templates/recipe.php
 *
 * @package Total_Recipe_Generator_El
 * @since 1.0.0
 * @version 3.1.0
 */

$meta = '';
$rp_json = array( '@context' => 'http://schema.org', '@type' => 'Recipe' );
$display = get_option( 'trg_display' );

if ( ! $show_nutrition_only ) :
/**
 * Add post meta only if doesn't exist
 */
if ( ! in_array( 'trg_share_image', get_post_custom_keys( get_the_ID() ) ) ) {
	add_post_meta( get_the_ID(), 'trg_share_image', '' );
}

if ( ! in_array( 'trg_share_desc', get_post_custom_keys( get_the_ID() ) ) ) {
	add_post_meta( get_the_ID(), 'trg_share_desc', '' );
}

echo '<div class="trg-recipe elementor-widget-heading" itemscope itemtype="http://schema.org/Recipe">';

    $name 			= ( 'custom' == $name_src && '' != $name_txt ) ? $name_txt : get_the_title();
	$author 		= ( 'custom' == $author_src && '' != $author_name ) ? $author_name : get_the_author();
	$author_url 	= ( '' !== $author_url ) ? $author_url : get_author_posts_url( get_the_author_meta( 'ID' ) );

	$img_obj = $image = '';
	if ( has_post_thumbnail() ) {
		$img_obj = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		$image = $img_obj[0];
	}

	if ( 'media_lib' == $img_src && '' != $img_lib ) {
		$image = $img_lib['url'];
	}

	/**
	 * Update post meta
	 * @meta_key 'trg_share_image'
	 *
	 * Used to set og:image tag in head
	 * Used in trg_el_add_og_site_tag() function
	 */
	if ( '' != $image ) {
		update_post_meta( get_the_ID(), 'trg_share_image', $image );
	}

	// Add to JSON
	$rp_json['name'] = esc_attr( $name );
	$rp_json['author'] = array( '@type' => 'Organization', 'name' => $author, 'url' => $author_url );
	$rp_json['image'] = esc_url( $image );
	$rp_json['datePublished'] = esc_attr( get_the_date( 'c' ) );
	$rp_json['url'] = esc_url( get_permalink() );

	// Output URL Schema
	echo '<meta itemprop="url" content="' . esc_url( get_permalink() ) . '" />';

	// Social position 1 -- Before Title
	if ( isset( $social['social_pos'] ) && '1' == $social['social_pos'] ) {
		trg_social_placement( $social );
	}

	// Recipe heading
	if ( 'hide' == $name_src ) {
		printf( '<meta itemprop="name" content="%s" />',
			esc_attr( $name )
		);
	} else {
		printf( '<%1$s class="entry-title recipe-title elementor-heading-title" itemprop="name">%2$s</%1$s>',
			$recipe_title_tag,
			esc_attr( $name )
		);
	}

	// Author and date meta
	if ( '' == $show_date ) {
		printf( '<meta itemprop="datePublished" content="%s" />',
			esc_attr( get_the_time( get_option( 'date_format' ) ) )
		);
	}
	
	if ( '' == $show_author ) {
		printf( '<meta itemscope itemprop="author" itemtype="http://schema.org/Organization"  content="%s" />',
			esc_attr( $author )
		);
	}

	if ( $show_author || $show_date ) {
		echo '<ul class="recipe-meta">';
		if ( $show_date ) {
			printf( '<li itemprop="datePublished" class="post-date">%s</li>',
				esc_attr( get_the_time( get_option( 'date_format' ) ) )
			);
		}
		if ( $show_author ) {
			printf( '<li itemprop="author" itemscope itemtype="http://schema.org/Organization" class="post-author"><a itemprop="url" href="%s"><span itemprop="name">%s</span></a></li>',
				esc_url( $author_url ),
				esc_attr( $author )
			);
		}
		echo '</ul>';
	}

	// Social position 2 -- Before Image
	if ( isset( $social['social_pos'] ) && '2' == $social['social_pos'] ) {
		trg_social_placement( $social );
	}

	// Recipe image here
	trg_el_recipe_image( array(
		'imgsize'			=> $imgsize,
		'img_src'           => $img_src,
		'img_lib'           => $img_lib,
		'imgwidth'          => intval( $imgwidth ),
		'imgheight'         => intval( $imgheight ),
		'imgcrop'           => $imgcrop,
		'imgquality'        => intval( $imgquality )
	) );

	// Social position 3 -- After Image
	if ( isset( $social['social_pos'] ) && '3' == $social['social_pos'] ) {
		trg_social_placement( $social );
	}

	// Recipe summary
	if ( '' == $show_summary) {
		printf( '<meta itemprop="description" content="%s" />',
			wp_strip_all_tags( $this->parse_text_editor( $summary ) )
		);
	} else {
		printf( '<div class="recipe-summary" itemprop="description">%s</div>',
			$this->parse_text_editor( $summary )
		);
	}

	/**
	 * Update post meta
	 * @meta_key 'trg_share_desc'
	 *
	 * Used to set og:description tag in head
	 * Used in trg_el_add_og_site_tag() function
	 */
	if ( '' != $summary ) {
		update_post_meta( get_the_ID(), 'trg_share_desc', wp_strip_all_tags( $this->parse_text_editor( $summary ) ) );
	}

	$rp_json['description'] = ( '' != $summary ) ? wp_strip_all_tags( $this->parse_text_editor( $summary ) ) : '';


 	// Prep and cooking time meta
	if ( '' != $prep_time && '' != $cook_time ) {
		$perform_time = ( '' != $perform_time ) ? $perform_time : 0;
		$total_time = trg_el_time_convert( (int)$prep_time + (int)$cook_time + (int)$perform_time );
	}
	else {
		$total_time = trg_el_time_convert( (int)$total_time );
	}
	$prep_time = trg_el_time_convert( (int)$prep_time );
	$cook_time = trg_el_time_convert( (int)$cook_time );
	$perform_time = trg_el_time_convert( (int)$perform_time );

	$rp_json['prepTime'] = esc_attr( $prep_time[ 'schema' ] );
	$rp_json['cookTime'] = esc_attr( $cook_time[ 'schema' ] );
	$rp_json['performTime'] = esc_attr( $perform_time[ 'schema' ] );
	$rp_json['totalTime'] = esc_attr( $total_time[ 'schema' ] );

	if ( '' !== $recipe_yield ) {
		$r_yield_int = (int) filter_var( $recipe_yield, FILTER_SANITIZE_NUMBER_INT );
		$rp_json['recipeYield'] = ( isset( $r_yield_int ) && '' != $r_yield_int ) ? $r_yield_int : 1;
	}

	if ( '' !== $prep_time[ 'readable' ] || '' !== $cook_time[ 'readable' ] || '' !== $perform_time[ 'readable' ] || '' !== $total_time[ 'readable' ] || '' !== $recipe_yield  || '' !== $serving_size || '' !== $calories ) {
		echo '<ul class="info-board">';

			if ( '' !== $prep_time[ 'readable' ] ) {
				echo sprintf( '<li class="prep-time"><meta itemprop="prepTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					esc_attr( $prep_time[ 'schema' ] ),
					isset( $display[ 'prep_time_label' ] ) && '' != $display[ 'prep_time_label' ] ? esc_attr( $display[ 'prep_time_label' ] ) : __( 'Prep Time', 'trg_el' ),
					esc_attr( $prep_time[ 'readable' ] )
				);
			}

			if ( '' !== $cook_time[ 'readable' ] ) {
				echo sprintf( '<li class="cook-time"><meta itemprop="cookTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					esc_attr( $cook_time[ 'schema' ] ),
					isset( $display[ 'cook_time_label' ] ) && '' != $display[ 'cook_time_label' ] ? esc_attr( $display[ 'cook_time_label' ] ) : __( 'Cook Time', 'trg_el' ),
					esc_attr( $cook_time[ 'readable' ] )
				);
			}

			if ( '' !== $perform_time[ 'readable' ] ) {
				echo sprintf( '<li class="perform-time"><meta itemprop="performTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					esc_attr( $perform_time[ 'schema' ] ),
					isset( $display[ 'perform_time_label' ] ) && '' != $display[ 'perform_time_label' ] ? esc_attr( $display[ 'perform_time_label' ] ) : __( 'Perform Time', 'trg_el' ),
					esc_attr( $perform_time[ 'readable' ] )
				);
			}

			if ( '' !== $total_time[ 'readable' ] ) {
				echo sprintf( '<li class="total-time"><meta itemprop="totalTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					esc_attr( $total_time[ 'schema' ] ),
					isset( $display[ 'total_time_label' ] ) && '' != $display[ 'total_time_label' ] ? esc_attr( $display[ 'total_time_label' ] ) : __( 'Total Time', 'trg_el' ),
					esc_attr( $total_time[ 'readable' ] )
				);
			}

			if ( '' !== $ready_in ) {
				echo sprintf( '<li class="ready-in"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					isset( $display[ 'ready_in_label' ] ) && '' != $display[ 'ready_in_label' ] ? esc_attr( $display[ 'ready_in_label' ] ) : _x( 'Ready in', 'Label for ready in time', 'trg_el' ),
					esc_attr( $ready_in )
				);
			}

			if ( '' !== $recipe_yield ) {
				echo sprintf( '<li class="recipe-yield"><span class="ib-label">%s</span><span class="ib-value">%s</span><meta itemprop="recipeYield" content="%s"/></li>',
					isset( $display[ 'yield_label' ] ) && '' != $display[ 'yield_label' ] ? esc_attr( $display[ 'yield_label' ] ) : _x( 'Yield', 'Recipe yield or outcome', 'trg_el' ),
					esc_attr( $recipe_yield ),
					isset( $r_yield_int ) && '' != $r_yield_int ? esc_attr( $r_yield_int ) : 1
				);
			}

			if ( '' !== $serving_size ) {
				echo sprintf( '<li class="serving-size"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					isset( $display[ 'serving_size_label' ] ) && '' != $display[ 'serving_size_label' ] ? esc_attr( $display[ 'serving_size_label' ] ) : __( 'Serving Size', 'trg_el' ),
					esc_attr( $serving_size )
				);
			}

			if ( '' !== $calories ) {
				echo sprintf( '<li class="recipe-cal"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					isset( $display[ 'energy_label' ] ) && '' != $display[ 'energy_label' ] ? esc_attr( $display[ 'energy_label' ] ) : _x( 'Energy', 'Label for recipe calories', 'trg_el' ),
					sprintf( _x( '%s cal', 'xx calories', 'trg_el' ), number_format_i18n( (int)$calories ) )
				);
			}

			if ( '' !== $total_cost ) {
				echo sprintf( '<li class="total-cost"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					isset( $display[ 'total_cost_label' ] ) && '' != $display[ 'total_cost_label' ] ? esc_attr( $display[ 'total_cost_label' ] ) : _x( 'Total Cost', 'Label for total cost', 'trg_el' ),
					esc_attr( $total_cost )
				);
			}

			if ( '' !== $cost_per_serving ) {
				echo sprintf( '<li class="cost-per-serving"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
					isset( $display[ 'cost_per_serving_label' ] ) && '' != $display[ 'cost_per_serving_label' ] ? esc_attr( $display[ 'cost_per_serving_label' ] ) : _x( 'Cost per Serving', 'Label for cost per serving', 'trg_el' ),
					esc_attr( $cost_per_serving )
				);
			}

			/**
			 * User defined custom meta
			 * @since 3.1.0
			 */
			if ( isset( $cust_meta ) && is_array( $cust_meta ) ) {
				foreach ( $cust_meta as $meta ) {
					if ( isset( $meta[ 'title' ] ) && isset( $meta[ 'value' ] ) ) {
						if ( '' != $meta[ 'title' ] || '' != $meta[ 'value' ] ) {
							printf( '<li class="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
								'' != $meta[ 'title' ] ? esc_attr( str_replace( ' ', '-', strtolower( $meta[ 'title' ] ) ) ) : '',
								'' != $meta[ 'title' ] ? esc_attr( $meta[ 'title' ] ) : '&nbsp;',
								'' != $meta[ 'value' ] ? esc_attr( $meta[ 'value' ] ) : '&nbsp;'
							);
						}
					}
				}
			}

		echo '</ul>';

	}

 	// Cuisine meta
 	$tax_optional = isset( $display['tax_optional'] ) ? $display['tax_optional'] : '';
	$rcu 		= trg_el_create_list_items( $recipe_cuisine, $recipe_cuisine_other, 'recipeCuisine', true, $tax_optional );
	$rcat 		= trg_el_create_list_items( $recipe_category, $recipe_category_other, 'recipeCategory', true, $tax_optional );
	$rmethod 	= trg_el_create_list_items( $cooking_method, '', 'cookingMethod', true, $tax_optional );
	$sfd 		= trg_el_create_diet_items( $suitable_for_diet, $suitable_for_diet_other, true, $tax_optional );

	$rp_json['recipeCuisine'] 	= $rcu['arr'];
	$rp_json['recipeCategory'] 	= $rcat['arr'];
	$rp_json['cookingMethod'] 	= $rmethod['arr'];
	$rp_json['suitableForDiet'] = $sfd['arr'];

	if ( '' !== $rcu || '' !== $rcat || '' !== $rmethod || '' !== $sfd ) {
		echo '<ul class="cuisine-meta">';

			if ( '' !== $rcu ['html']) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					isset( $display[ 'cuisine_label' ] ) && '' != $display[ 'cuisine_label' ] ? esc_attr( $display[ 'cuisine_label' ] ) : __( 'Cuisine', 'trg_el' ),
					$rcu['html']
				);
			}

			if ( '' !== $rcat['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					isset( $display[ 'course_label' ] ) && '' != $display[ 'course_label' ] ? esc_attr( $display[ 'course_label' ] ) : __( 'Course', 'trg_el' ),
					$rcat['html']
				);
			}

			if ( '' !== $rmethod['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					isset( $display[ 'cooking_method_label' ] ) && '' != $display[ 'cooking_method_label' ] ? esc_attr( $display[ 'cooking_method_label' ] ) : __( 'Cooking Method', 'trg_el' ),
					$rmethod['html']
				);
			}

			if ( isset( $sfd['html'] ) && '' !== $sfd['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					isset( $display[ 'sfd_label' ] ) && '' != $display[ 'sfd_label' ] ? esc_attr( $display[ 'sfd_label' ] ) : __( 'Suitable for Diet', 'trg_el' ),
					$sfd['html']
				);
			}

			/**
			 * User defined custom recipe attributes
			 * @since 3.1.0
			 */
			if ( isset( $cust_attr ) && is_array( $cust_attr ) ) {
				foreach ( $cust_attr as $attr ) {
					if ( isset( $attr[ 'title' ] ) && isset( $attr[ 'value' ] ) ) {
						if ( '' != $attr[ 'title' ] || '' != $attr[ 'value' ] ) {
							$cust_atts = trg_el_create_atts( $attr[ 'value' ], true );
							printf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
								'' != $attr[ 'title' ] ? esc_attr( $attr[ 'title' ] ) : '&nbsp;',
								$cust_atts['html']
							);
						}
					}
				}
			}

			// Tags Meta
			if ( $show_tags && get_the_tags() ) {
				echo sprintf( '<li><span class="cm-label">%s</span>%s</li>',
					isset( $labels[ 'label_kw' ] ) ? $labels[ 'label_kw' ] : __( 'Tags', 'trg' ),
					get_the_tag_list('<ul class="cm-items recipe-tags"><li class="cm-value link-enabled">','</li><li class="cm-value link-enabled">','</li></ul>' )
				);
			}

		echo '</ul>';

		// Keywords for Schema
		if ( isset( $recipe_keywords ) && '' != $recipe_keywords ) {
			echo '<meta itemprop="keywords" content="' . $recipe_keywords . '" />';
			$rp_json['keywords'] = explode( ',', $recipe_keywords );
		}
	}

	// Ad Spot 1
	if ( isset( $ad_spot_1 ) && '' != $ad_spot_1 ) {
		echo '<div class="trg-ad-spot ad-spot-1">' . $ad_spot_1 . '</div>';
	} elseif ( isset( $ad_spots['ad_spot_1'] ) && '' !== $ad_spots['ad_spot_1'] ) {
		echo '<div class="trg-ad-spot ad-spot-1">' . $ad_spots['ad_spot_1'] . '</div>';
	}

	?>

    <div class="trg-row">
    <div class="ingredients-section trg-col trg-col-40 float-right">
		<?php
        // Ingredients
        $ing_list = $ing_title = '';
		$ing_json = array();

		// Global ingredients heading
		if ( isset( $display['ing_heading'] ) && '' !== $display['ing_heading'] ) {
			$ing_title =  $display['ing_heading'];
		}

		// Per module ingredients heading
		if ( isset( $ing_heading ) && '' !== $ing_heading ) {
			$ing_title = $ing_heading;
		}

        if ( $ing_title ) {
			echo '<h3 class="recipe-heading ing-title elementor-heading-title"><i class="trg-icon ' . $ing_icon['value'] . '" aria-hidden="true"></i>' . esc_html( $ing_title ) . '</h3>';
		}

        if ( isset( $ingredients ) && is_array( $ingredients ) ) {
			foreach ( $ingredients as $ing ) {

				$ing_content = preg_replace( '#<p>|</p>#', '', $this->parse_text_editor( $ing['list'] ) );
				$ing_content = preg_replace( '#<br \/>#', "\n", $ing_content );

				$ing_list = explode( "\n", $ing_content );

				if ( isset( $ing['title'] ) && '' != $ing['title'] ) {
					echo '<p class="list-subhead elementor-heading-title">' . $ing['title'] . '</p>';
				}

				if ( ! empty( $ing_list ) && is_array( $ing_list ) ) {
					echo '<ul class="ing-list">';
					foreach ( $ing_list as $list_item ) {
						$stripped = wp_strip_all_tags( $list_item );
						echo '<li><i class="trg-icon ' . $ing_list_icon['value'] . '" aria-hidden="true"></i>' . $list_item . '<meta  itemprop="recipeIngredient" content="' . $stripped . '"></li>';
						$ing_json[] = $stripped;
					}
					echo '</ul>';
				}
			}
		}

		$rp_json['recipeIngredient'] = $ing_json;

		// Ad Spot 2
		if ( isset( $ad_spot_2 ) && '' != $ad_spot_2 ) {
			echo '<div class="trg-ad-spot ad-spot-2">' . $ad_spot_2 . '</div>';
		} elseif ( isset( $ad_spots['ad_spot_2'] ) && '' !== $ad_spots['ad_spot_2'] ) {
			echo '<div class="trg-ad-spot ad-spot-2">' . $ad_spots['ad_spot_2'] . '</div>';
		}
        ?>
    </div><!-- /.ingredients-section -->

    <?php
	if ( isset( $methods ) && is_array( $methods ) ) {
	?>
        <div class="method-section trg-col trg-col-60">
            <?php
            // Method (Instructions)
            $num_class = '';
            $rp_json['recipeInstructions'] = [];
            $ins_json = ['@type' => 'HowToStep', 'text' => ''];
            $step_count = 1;
            $method_title = '';

			// Global method heading
			if ( isset( $display['method_heading'] ) && '' !== $display['method_heading'] ) {
				$method_title =  $display['method_heading'];
			}

			// Per module method heading
			if ( isset( $method_heading ) && '' !== $method_heading ) {
				$method_title = $method_heading;
			}

            if ( $enable_numbering ) {
				$num_class = ' number-enabled';
            }

           	if ( '' !== $method_title ) {
				echo '<h3 class="method-heading ins-title elementor-heading-title"><i class="trg-icon ' . $method_icon['value'] . '" aria-hidden="true"></i>' . esc_html( $method_title ) . '</h3>';
			}

			echo '<div class="recipe-instructions' . $num_class . '">';
			foreach( $methods as $method ) {

				if ( '' !== $method['method_title'] ) {
					echo '<p class="inst-subhead elementor-heading-title">' . esc_attr( $method['method_title'] ) . '</p>';
					if ( $reset_count ) {
						$step_count = 1;
					}
				}

				printf( '<div id="recipe_step_%s" class="recipe-instruction">%s<div itemprop="recipeInstructions" itemscope itemtype="http://schema.org/HowToStep" class="step-content"><div itemprop="text">%s</div></div></div>',
						$step_count,
						sprintf( '<div class="step-num step-%1$s">%1$s</div>', number_format_i18n( $step_count ) ),
						$this->parse_text_editor( $method['method_content'] )
					);

				// Add method step to JSON LD
				$ins_json['text'] = strip_tags( $this->parse_text_editor( $method['method_content'] ) );
				$rp_json['recipeInstructions'][] = $ins_json;
				$step_count++;
			}

			echo '</div>';

			// Add instructions to JSON
			$rp_json['recipeInstructions'] = $ins_json;
            ?>
        </div><!-- /.method-section -->
    <?php
	}
	?>
    </div><!-- /.row -->
    <?php

	// Other notes
	if ( '' !== $other_notes ) {
		echo '<div class="recipe-other-notes">' . $this->parse_text_editor( $other_notes ) . '</div>';
	}

	// Social position 4 -- Before Nutrition
	if ( isset( $social['social_pos'] ) && '4' == $social['social_pos'] ) {
		trg_social_placement( $social );
	}

 endif; // ! show_nutrition_only

	if ( 'true' == $show_nutrition ) {
	 	$nutri_json = array( '@type' => 'NutritionInformation', 'calories' => sprintf( __( '%s calories', 'trg_el' ), $calories ) );
	 	if ( '' !== $serving_size ){
	 		$nutri_json['servingSize'] = $serving_size;
	 	}
		
		$nutrition_facts = apply_filters( 'trg_nutrition_facts_list', array(
		array(
			'id'			=> 'total_fat',
			'label'			=> __( 'Total Fat', 'trg_el' ),
			'schema'		=> 'fatContent',
			'liclass'		=> false,
			'labelclass'	=> 'font-bold',
			'sv'			=> apply_filters( 'total_fat_sv', 72 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'saturated_fat',
			'label'			=> __( 'Saturated Fat', 'trg_el' ),
			'schema'		=> 'saturatedFatContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'saturated_fat_sv', 20 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'trans_fat',
			'label'			=> __( 'Trans Fat', 'trg_el' ),
			'schema'		=> 'transFatContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> false,
			'sv'			=> false,
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'polyunsat_fat',
			'label'			=> __( 'Polyunsaturated Fat', 'trg_el' ),
			'schema'		=> 'unsaturatedFatContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> false,
			'sv'			=> false,
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'monounsat_fat',
			'label'			=> __( 'Monounsaturated Fat', 'trg_el' ),
			'schema'		=> 'unsaturatedFatContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> false,
			'sv'			=> false,
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'cholesterol',
			'label'			=> __( 'Cholesterol', 'trg_el' ),
			'schema'		=> 'cholesterolContent',
			'liclass'		=> '',
			'labelclass'	=> 'font-bold',
			'sv'			=> apply_filters( 'cholesterol_sv', 300 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'sodium',
			'label'			=> __( 'Sodium', 'trg_el' ),
			'schema'		=> 'sodiumContent',
			'liclass'		=> '',
			'labelclass'	=> 'font-bold',
			'sv'			=> apply_filters( 'sodium_sv', 2300 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'carbohydrate',
			'label'			=> __( 'Total Carbohydrate', 'trg_el' ),
			'schema'		=> 'carbohydrateContent',
			'liclass'		=> '',
			'labelclass'	=> 'font-bold',
			'sv'			=> apply_filters( 'carbohydrate_sv', 275 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'fiber',
			'label'			=> __( 'Dietary Fiber', 'trg_el' ),
			'schema'		=> 'fiberContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> '',
			'sv'			=> apply_filters( 'fiber_sv', 28 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'sugar',
			'label'			=> __( 'Total Sugars', 'trg_el' ),
			'schema'		=> 'sugarContent',
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> '',
			'sv'			=> false,
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'added_sugar',
			'label'			=> __( 'Added Sugars', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> 'nt-sublevel-2',
			'labelclass'	=> '',
			'sv'			=> apply_filters( 'added_sugar_sv', 50 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'sugar_alcohal',
			'label'			=> __( 'Sugar Alcohal', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> 'nt-sublevel-1',
			'labelclass'	=> '',
			'sv'			=> false,
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'protein',
			'label'			=> __( 'Protein', 'trg_el' ),
			'schema'		=> 'proteinContent',
			'liclass'		=> 'nt-sep sep-12',
			'labelclass'	=> 'font-bold',
			'sv'			=> apply_filters( 'protein_sv', 50 ),
			'unit'			=> __( 'g', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_d',
			'label'			=> __( 'Vitamin D (Cholecalciferol)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_d_sv', 800 ),
			'unit'			=> __( 'IU', 'trg_el' )
		),
		array(
			'id'			=> 'calcium',
			'label'			=> __( 'Calcium', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'calcium_sv', 1300 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'iron',
			'label'			=> __( 'Iron', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'iron_sv', 18 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'potassium',
			'label'			=> __( 'Potassium', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'potassium_sv', 4700 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_a',
			'label'			=> __( 'Vitamin A', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_a_sv', 900 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_c',
			'label'			=> __( 'Vitamin C (Ascorbic Acid)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_c_sv', 90 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_e',
			'label'			=> __( 'Vitamin E (Tocopherol)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_e_sv', 33 ),
			'unit'			=> __( 'IU', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_k',
			'label'			=> __( 'Vitamin K', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_k_sv', 120 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b1',
			'label'			=> __( 'Vitamin B1 (Thiamin)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b1_sv', 1.2 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b2',
			'label'			=> __( 'Vitamin B2 (Riboflavin)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b2_sv', 1.3 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b3',
			'label'			=> __( 'Vitamin B3 (Niacin)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b3_sv', 16 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b6',
			'label'			=> __( 'Vitamin B6 (Pyridoxine)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b6_sv', 1.7 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'folate',
			'label'			=> __( 'Folate', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'folate_sv', 400 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b12',
			'label'			=> __( 'Vitamin B12 (Cobalamine)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b12_sv', 2.4 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'biotin',
			'label'			=> __( 'Biotin', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'biotin_sv', 30 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'choline',
			'label'			=> __( 'Choline', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'choline_sv', 550 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'vitamin_b5',
			'label'			=> __( 'Vitamin B5 (Pantothenic acid)', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'vitamin_b5_sv', 5 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'phosphorus',
			'label'			=> __( 'Phosphorus', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'phosphorus_sv', 1250 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'iodine',
			'label'			=> __( 'Iodine', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'iodine_sv', 150 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'magnesium',
			'label'			=> __( 'Magnesium', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'magnesium_sv', 420 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'zinc',
			'label'			=> __( 'Zinc', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'zinc_sv', 11 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'selenium',
			'label'			=> __( 'Selenium', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'selenium_sv', 55 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'copper',
			'label'			=> __( 'Copper', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'copper_sv', .9 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'manganese',
			'label'			=> __( 'Manganese', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'manganese_sv', 2.3 ),
			'unit'			=> __( 'mg', 'trg_el' )
		),
		array(
			'id'			=> 'chromium',
			'label'			=> __( 'Chromium', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'chromium_sv', 35 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'molybdenum',
			'label'			=> __( 'Molybdenum', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> false,
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'molybdenum_sv', 45 ),
			'unit'			=> __( 'mcg', 'trg_el' )
		),
		array(
			'id'			=> 'chloride',
			'label'			=> __( 'Chloride', 'trg_el' ),
			'schema'		=> false,
			'liclass'		=> 'nt-sep',
			'labelclass'	=> false,
			'sv'			=> apply_filters( 'chloride_sv', 2300 ),
			'unit'			=> __( 'mg', 'trg_el' )
		)
	) );

	/** Start Nutrition table output */
	printf( '<div class="trg nutrition-section%s">',
		$display_style !== 'classic' ? ' ' . esc_attr( $display_style ) : ''
	);
	?>

		<ul id="myTable" class="nutrition-table" itemprop="nutrition" itemscope itemtype="http://schema.org/NutritionInformation">
			<?php
			if ( isset( $display['nutri_heading'] ) && '' !== $display['nutri_heading'] ) {
				echo '<li class="nt-header b-0"><h2 class="nt-title">' . esc_html( $display['nutri_heading'] ) . '</h2></li>';
			}

			if ( '' !== $serving_per_cont ) {
				printf( '<li class="nt-row b-0 serving-per-cont"><span class="nt-label col-%s">%s</span></li>',
					'std' == $display_style ? '100' : ( $show_dv ? '80' : '70' ),
					sprintf( __( '%s servings per container', 'trg_el' ), esc_attr( $serving_per_cont ) ),
					'std' == $display_style ? '50' : ( $show_dv ? '20' : '30' )
				);

			}

			if ( '' !== $serving_size ) {
				printf( '<li class="nt-row sep-12 serving-size"><span class="nt-label col-%s">%s</span><span class="nt-value col-%s" itemprop="servingSize">%s</span></li>',
					'std' == $display_style ? '50' : ( $show_dv ? '80' : '70' ),
					__( 'Serving Size', 'trg_el' ),
					'std' == $display_style ? '50' : ( $show_dv ? '20' : '30' ),
					esc_attr( $serving_size )
				);
			}

			printf( '<li class="nt-row b-0 amount-per-serving"><span class="nt-label col-100">%s</span></li>',
				__( 'Amount per serving', 'trg_el' )
			);

			printf( '<li class="nt-row calories sep-6"><span class="nt-label col-%s">%s</span><span class="nt-value col-%s">%s</span><meta itemprop="calories" content="%s"></li>',
				$show_dv ? '80' : '70',
				__( 'Calories', 'trg_el' ),
				$show_dv ? '20' : '30',
				number_format_i18n( (int)$calories ),
				sprintf( __( '%s calories', 'trg_el'), number_format_i18n( (int)$calories ) )
			);

			if ( $show_dv ) {
				printf( '<li class="nt-row nt-head with-sdv"><span class="nt-label pdv-label col-20">%s</span><span class="sdv-label col-20">%s</span></li>',
					__( '% Daily Value*', 'trg_el' ),
					__( 'Standard DV', 'trg_el' )
				);
			}
			else {
				printf( '<li class="nt-head"><span class="pdv-label col-100 text-right">%s</span></li>',
					__( '% Daily Value*', 'trg_el' )
				);
			}

			foreach( $nutrition_facts as $nf ) {

				if ( isset( ${ $nf['id'] } ) && '' !== ${ $nf['id'] } ) {

					// Json LD data
					if ( $json_ld ) {
						if ( $nf['schema'] ) {
							$nutri_json[ $nf['schema'] ] = ${ $nf['id'] } . ' ' . $nf['unit'];
						}
					}
					if ( ! empty( $nf['sv'] ) ) {
						$dv = round( (float)${ $nf['id'] } * 100 / $nf['sv'], 2 );
					}
					if ( $show_dv ) {
						$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-20"%4$s>%5$s</span>%6$s%7$s</li>';
					}
					else {
						$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-30"%4$s>%5$s</span>%7$s</li>';
					}

					printf( $format,
						$nf['liclass'] ? ' class="' . esc_attr( $nf['liclass'] ) . '"' : '',
						$nf['labelclass'] ? ' ' . esc_attr( $nf['labelclass']  ) : '',
						esc_attr( $nf['label'] ),
						$nf['schema']  ?  ' itemprop="' . esc_attr( $nf['schema'] ) . '"' : '',
						${ $nf['id'] } . ' ' . $nf['unit'],
						! empty( $nf['sv'] ) ? sprintf( '<span class="nt-sdv col-20">%s</span>', $nf['sv'] . ' ' . $nf['unit'] ) : '',
						! empty( $nf['sv'] ) ? sprintf( '<span class="nt-value col-%s">%s</span>',
							$show_dv ? '20' : '30',
							(int)$dv <= 100 ? $dv . '%' : '<b>' . $dv . '%</b>'
						) : ''
					);
				}
			}

			// Custom Nutrients

			if ( isset( $custom_nutrients ) && is_array( $custom_nutrients ) ) {
				foreach( $custom_nutrients as $cn ) {

					if ( isset( $cn['name'] ) && '' !== $cn['name'] ) {
						if ( ! empty( $cn['sv'] ) ) {
							$dv = round( (float)$cn['amt'] * 100 / $cn['sv'], 2 );
						}

						if ( $show_dv ) {
							$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-20">%4$s</span>%5$s%6$s</li>';
						}

						else {
							$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-30">%4$s</span>%6$s</li>';
						}

						printf( $format,
							$cn['level'] !== '0' ? ' class="nt-sublevel-' . esc_attr( $cn['level'] ) . '"' : '',
							isset( $cn['text_style'] ) && 'bold' == $cn['text_style'] ? ' font-bold' : '',
							esc_attr( $cn['name'] ),
							$cn['amt'] . ' ' . $cn['unit'],
							! empty( $cn['sv'] ) ? sprintf( '<span class="nt-sdv col-20">%s</span>', $cn['sv'] . ' ' . $cn['unit'] ) : '',
							! empty( $cn['sv'] ) ? sprintf( '<span class="nt-value col-%s">%s</span>',
								$show_dv ? '20' : '30',
								(int)$dv <= 100 ? $dv . '%' : '<b>' . $dv . '%</b>'
							) : ''
						);
					}
				}
			}

			if ( '' !== $extra_notes ) {
				printf( '<li class="nt-footer b-0">%s</li>', $extra_notes );
			}

		?>
		</ul><!-- /.nutrition-table -->
	</div><!-- /.nutrition-section -->
	<?php
    } // Show nutrition

    else { // Include microdata for nutrition
		printf( '<span class="hidden" itemprop="nutrition" itemscope itemtype="http://schema.org/NutritionInformation">%s%s</span>',
		'' !== $serving_size ? '<meta itemprop="servingSize" content="' . $serving_size . '">' : '',
		'' !== $calories ? '<meta itemprop="calories" content="' . $calories . '">' : ''
		);
    }

    // Add to JSON LD
    if ( $show_nutrition_only || $show_nutrition ) {
	    $rp_json['nutrition'] = $nutri_json;
	}

    if ( ! $show_nutrition_only ) :

	// Ad Spot 3
	if ( isset( $ad_spot_3 ) && '' != $ad_spot_3 ) {
		echo '<div class="trg-ad-spot ad-spot-3">' . $ad_spot_3 . '</div>';
	} elseif ( isset( $ad_spots['ad_spot_3'] ) && '' !== $ad_spots['ad_spot_3'] ) {
		echo '<div class="trg-ad-spot ad-spot-3">' . $ad_spots['ad_spot_3'] . '</div>';
	}

	// Comments Schema
	$comment_num = get_comments_number();
	$rp_json['interactionStatistic'] = array( '@type' => 'InteractionCounter', 'interactionType' => 'http://schema.org/Comment', 'userInteractionCount' => $comment_num );
	?>
    <div itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
        <meta itemprop="interactionType" content="http://schema.org/CommentAction" />
        <meta itemprop="userInteractionCount" content="<?php echo esc_attr( $comment_num ); ?>" />
    </div>

    <?php
	/**
	 * User rating Schema
	 * Requires WP Review plugin or Rate my Post Plugin
	 *
	 */
	$review_count = '';
	$rating_value = '';
	if ( 'mts' == $rating_src && function_exists( 'mts_get_post_reviews' ) ) {
		$rating_arr = mts_get_post_reviews( get_the_id() );
		if ( isset( $rating_arr ) && is_array( $rating_arr ) ) {
			if ( $rating_arr['count'] > 0 && $rating_arr['rating'] > 0 ) {
				$review_count = $rating_arr['count'];
				$rating_value = $rating_arr['rating'];
			}
		}
	} elseif ( 'rmp' == $rating_src && function_exists( 'rmp_get_avg_rating' ) && function_exists( 'rmp_get_vote_count' ) ) {
		$review_count = rmp_get_vote_count( get_the_id() );
		$rating_value = rmp_get_avg_rating( get_the_id() );
	}

	if ( $review_count && $rating_value ) {
	?>
		<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
			<meta itemprop="ratingValue" content="<?php echo $rating_value; ?>" />
			<meta itemprop="reviewCount" content="<?php echo $review_count; ?>" />
		</div>
		<?php
		$rp_json['aggregateRating'] = array( '@type' => 'AggregateRating', 'ratingValue' => $rating_value, 'reviewCount' => $review_count );
	}

	/**
	 * Add Video Schema
	 * @since 3.1.0
	 */

	if ( isset( $vid_url ) && '' != $vid_url ) {
		$thumb_url = isset( $vid_thumb_url ) && is_array( $vid_thumb_url ) ? $vid_thumb_url['url'] : '';
		echo '<div itemprop="video" itemscope itemtype="http://schema.org/VideoObject" class="publisher-schema">';
			echo '<meta itemprop="name" content="' . esc_attr( $vid_name ) .'">';
			echo '<meta itemprop="duration" content="' . esc_attr( $vid_duration ) .'">';
			echo '<meta itemprop="thumbnailUrl" content="' . esc_url( $thumb_url ) .'">';
			echo '<meta itemprop="contentUrl" content="' . esc_url( $vid_url ) .'">';
			echo '<meta itemprop="uploadDate" content="' . esc_attr( $vid_date ) .'">';
			echo '<meta itemprop="description" content="' . esc_attr( $vid_description ) .'">';
		echo '</div>';

		// Add to JSON
		$rp_json['video'] = array(
			'@type' => 'VideoObject',
			'name' => esc_attr( $vid_name ),
			'duration' => esc_attr( $vid_duration ),
			'thumbnailUrl' => esc_url( $thumb_url ),
			'contentUrl' => esc_url( $vid_url ),
			'uploadDate' => esc_attr( $vid_date ),
			'description' => esc_attr( $vid_description )
		);
	}

	endif; // ! show_nutrition_only

	/**
	 * Output JSON LD data as script
	 * Websites like pinterest detect json data better as compared to inline microdata
	 */

	if ( $json_ld ) {
		echo '<script type="application/ld+json">' . json_encode( $rp_json ) . '</script>';
	}

	if ( $website_schema && ! $show_nutrition_only ) {
		$website_json = array( '@context' => 'http://schema.org', '@type' => 'Website', 'name' => get_bloginfo( 'name' ), 'alternateName' => get_bloginfo( 'description' ), 'url' => esc_url( home_url( '/' ) ) );
		echo '<script type="application/ld+json">' . json_encode( $website_json ) . '</script>';
	}

	// Social position 5 -- After Nutrition
	if ( isset( $social['social_pos'] ) && '5' == $social['social_pos'] && ! $show_nutrition_only ) {
		trg_social_placement( $social );
	}
?>
</div><!-- /.trg-recipe -->