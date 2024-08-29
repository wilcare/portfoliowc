<?php
namespace Aepro\Modules\PostBlocksAdv\Classes;

use Aepro\Aepro;
use Elementor\Plugin;

class Query {

	private $settings = [];

	public function __construct( $settings ) {

		$this->settings = $settings;
	}

	public function get_posts() {

		$query_args = $this->build_query();

		$settings = $this->settings;
		// Run Generic Filter
		$query_args = apply_filters( 'aepro/post-blocks-adv/custom-source-query', $query_args, $settings );

		// Run Query Filter
		/**
		 * Filter - Add Custom Source Query
		 */
		$post_type = $settings['source'];
		if ( $post_type === 'current_loop' && ! Plugin::instance()->editor->is_edit_mode() ) {
			$query_args = null;
			global $wp_query;
			$main_query = clone $wp_query;
			$posts      = $main_query;
		} else {
			if ( isset( $query_args ) ) {
				if ( ! empty( $settings['query_filter'] ) ) {
					$query_args = apply_filters( $settings['query_filter'], $query_args );
				}
			}
			$posts = new \WP_Query( $query_args );
		}

		// Return
		return $posts;
	}

	private function build_query() {

		$source     = $this->settings['source'];
		$query_args = [];

		switch ( $source ) {
			case 'current_loop':
				if ( ! Plugin::instance()->editor->is_edit_mode() ) {
											global $wp_query;
											$main_query = clone $wp_query;
											$post_items = $main_query;

				} else {
					$query_args = $this->get_preview_query();
				}
				break;

			case 'manual_selection':
				$query_args = $this->manual_selection_query();
				break;

			case 'related':
				$query_args = $this->related_query();
				break;

			case 'relation':
				$query_args = $this->relation_query();
				break;

			case 'post_object':
				$query_args = $this->post_object_query();
				break;

			default:
				$query_args = $this->post_query();
				break;
		}

		return $query_args;
	}

	private function post_query() {

		$settings = $this->settings;
		$paged    = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : '';

		$paged                     = $this->get_current_page_num();
		$query_args['post_status'] = 'publish'; // Hide drafts/private posts for admins
		$query_args['post_type']   = $settings['source'];

		// Include Author
		if ( is_array( $settings['include_author_ids'] ) && count( $settings['include_author_ids'] ) ) {
			$query_args['author'] = implode( ',', $settings['include_author_ids'] );
		}

		// Taxonomy Parameters (Taxonomy Query )
		$selected_terms = [];
		$post_type      = $settings['source'];
		$selected_tax   = $settings[ $post_type . '_tax_ids' ];

		/* Filter Bar */
		// When there is Term Id in $_POST
		if ( isset( $settings['filter_taxonomy'] ) && ( isset( $_POST['term_id'] ) && $_POST['term_id'] >= 0 ) ) {

			if ( $_POST['term_id'] != 0 ) {
				$selected_terms['relation'] = 'AND';
				$selected_terms[]           = [
					'taxonomy' => $settings['filter_taxonomy'],
					'field'    => 'term_id',
					'terms'    => $_POST['term_id'],
				];
			}
			// When there is default term selected
		} elseif ( isset( $settings[ $settings['filter_taxonomy'] . '_filter_default_term' ] ) && $settings[ $settings['filter_taxonomy'] . '_filter_default_term' ] !== '' ) {
			$selected_terms['relation'] = 'AND';
			$selected_terms[]           = [
				'taxonomy' => $settings['filter_taxonomy'],
				'field'    => 'term_id',
				'terms'    => $settings[ $settings['filter_taxonomy'] . '_filter_default_term' ],
			];
			// When there is 'All' tab is disabled
		} elseif ( isset( $settings['show_all'] ) && $settings['show_all'] !== 'yes' ) {
			$filter_terms               = Aepro::$_helper->get_filter_bar_filters( $settings );
			$selected_terms['relation'] = 'AND';
			$selected_terms[]           = [
				'taxonomy' => $settings['filter_taxonomy'],
				'field'    => 'term_id',
				'terms'    => $filter_terms[0]->term_id,
			];
			// When there is 'All' tab is disabled
		}
		/* Filter Bar End */

		if ( is_array( $selected_tax ) && count( $selected_tax ) ) {
			$selected_tax_terms = [];
			foreach ( $selected_tax as $tax ) {
				//Include by Terms
				$terms         = $settings[ $tax . '_' . $post_type . '_include_term_ids' ];
				$tax_relation  = $settings[ $post_type . '_tax_relation' ];
				$term_operator = $settings[ $tax . '_' . $post_type . '_term_operator' ];
				if ( is_array( $terms ) && count( $terms ) ) {
					$selected_tax_terms['relation'] = $tax_relation;
					$selected_tax_terms[]           = [
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => $term_operator,
					];
				}

				//Exclude by Terms
				$terms = $settings[ $tax . '_' . $post_type . '_exclude_term_ids' ];
				if ( is_array( $terms ) && count( $terms ) ) {
					$selected_tax_terms[] = [
						'taxonomy' => $tax,
						'field'    => 'term_id',
						'terms'    => $terms,
						'operator' => 'NOT IN',
					];
				}
			}
			if ( is_array( $selected_tax_terms ) && count( $selected_tax_terms ) ) {
				$selected_terms[] = $selected_tax_terms;
			}
		}

		if ( is_array( $selected_terms ) && count( $selected_terms ) ) {
			$query_args['tax_query'] = $selected_terms;
		}

		//Exclude by current post.
		if ( $settings['current_post'] === 'yes' && is_single() ) {
			$post_data                  = Aepro::$_helper->get_demo_post_data();
			$query_args['post__not_in'] = [ $post_data->ID ];
		}

		//Exclude By Author
		if ( is_array( $settings['exclude_author_ids'] ) && count( $settings['exclude_author_ids'] ) ) {
			$query_args['author__not_in'] = $settings['exclude_author_ids'];
		}

		//Exclude by offset
		$query_args['offset'] = $settings['offset'];

		// Meta Query

		// Date Query

		$select_date = $settings['select_date'];
		if ( $select_date !== 'anytime' ) {
			$date_query = [];
			switch ( $select_date ) {
				case 'today':
					$date_query['after'] = '-1 day';
					break;
				case 'week':
					$date_query['after'] = '-1 week';
					break;
				case 'month':
					$date_query['after'] = '-1 month';
					break;
				case 'quarter':
					$date_query['after'] = '-3 month';
					break;
				case 'year':
					$date_query['after'] = '-1 year';
					break;
				case 'exact':
					if ( ! empty( $settings['post_status'] ) ) {
						$query_args['post_status'] = $settings['post_status'];
					}
					$after_date = $settings['date_after'];
					if ( ! empty( $after_date ) ) {
						$date_query['after'] = $after_date;
					}
					$before_date = $settings['date_before'];
					if ( ! empty( $before_date ) ) {
						$date_query['before'] = $before_date;
					}
					$date_query['inclusive'] = true;
					break;
			}
			$query_args['date_query'] = $date_query;
		}

		// Ordering Parameters
		$query_args['orderby'] = $settings['orderby'];
		$query_args['order']   = $settings['order'];

		if ( $settings['orderby'] === 'meta_value' || $settings['orderby'] === 'meta_value_num' ) {
			$query_args['meta_key'] = $settings['orderby_metakey'];
		}

		// Post Count
		if ( $settings['posts_per_page'] ) {
			$query_args['posts_per_page'] = $settings['posts_per_page'];
		}

		// Pagination Parameters
		if ( isset( $_POST['page_num'] ) || $paged > 1 ) {
			$query_args['offset'] = $this->calculate_offset( $settings, $query_args, $paged );
		}

		/** WooCommerce */
		if ( class_exists( 'WooCommerce' ) ) {
			// Out of Stock Product
			if( $settings['hide_out_of_stock'] === 'yes' ) {
				$query_args['meta_query'] = array(
					array(
						'key'       => '_stock_status',
						'value'     => 'outofstock',
						'compare'   => 'NOT IN'
					)
				);
			}

			// Featured Products
			if( $settings['show_featured'] === 'yes' ) {
				$featured_product_tax_query[] = array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'featured',
					'operator' => 'IN', // or 'NOT IN' to exclude feature products
				);

				if ( is_array( $featured_product_tax_query ) && count( $featured_product_tax_query ) ) {
					$selected_terms[] = $featured_product_tax_query;
				}

				if ( is_array( $selected_terms ) && count( $selected_terms ) ) {
					$query_args['tax_query'] = $selected_terms;
				}
			}
		}

		return $query_args;
	}

	private function manual_selection_query() {
		$settings                = $this->settings;
		$query_args['post_type'] = 'any';
		$ae_post_ids             = $settings['select_post_ids'];
		$query_args['post__in']  = $ae_post_ids;
		$query_args['orderby']   = $settings['orderby'];
		$query_args['order']     = $settings['order'];
		if ( empty( $query_args['post__in'] ) ) {
			// If no selection - return an empty query
			$query_args['post__in'] = [ -1 ];
		}

		return $query_args;
	}

	public function related_query() {
		$settings = $this->settings;

		if ( isset( $_POST['fetch_mode'] ) ) {
			$cpost_id = $_POST['cpid'];
			$cpost    = get_post( $cpost_id );
		} else {
			$cpost    = Aepro::$_helper->get_demo_post_data();
			$cpost_id = $cpost->ID;
		}

		$query_args = [
			'orderby'             => $settings['orderby'],
			'order'               => $settings['order'],
			'ignore_sticky_posts' => 1,
			'post_status'         => 'publish', // Hide drafts/private posts for admins
			'offset'              => $settings['offset'],
			'posts_per_page'      => $settings['posts_per_page'],
			'post__not_in'        => [ $cpost_id ],
			'post_type'           => 'any',
		];

		if ( $settings['orderby'] === 'meta_value' || $settings['orderby'] === 'meta_value_num' ) {
			$query_args['meta_key'] = $settings['orderby_metakey_name'];
		}

		if ( isset( $_POST['page_num'] ) ) {
			$query_args['offset'] = ( $query_args['posts_per_page'] * ( $_POST['page_num'] - 1 ) ) + $query_args['offset'];
		}

		$taxonomies = $settings['related_by'];

		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {

				$terms = get_the_terms( $cpost_id, $taxonomy );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$term_ids[] = $term->term_id;
					}

					if ( $settings['related_match_with'] === 'OR' ) {
						$operator = 'IN';
					} else {
						$operator = 'AND';
					}

					$query_args['tax_query'][] = [
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_ids,
						'operator' => $operator,
					];
				}
			}
		}

		return $query_args;
	}

	private function relation_query() {
		$settings   = $this->settings;
		$query_args = [];
        $selected_terms = array();
		$field      = $settings['acf_relation_field'];
		
		$paged      = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : '';

		if ( isset( $_POST['fetch_mode'] ) ) {
			$cpost_id = $_POST['cpid'];
			$cpost    = get_post( $cpost_id );
		} else {
			$cpost    = Aepro::$_helper->get_demo_post_data();
			$cpost_id = $cpost->ID;
		}

		if ( class_exists( 'acf' ) && is_plugin_active( 'pods/init.php' ) ) {
			if ( $settings['relationship_type'] === 'pods' ) {
				$pods = get_post_meta( $cpost_id, $field );
				if ( count( $pods ) === count( $pods, COUNT_RECURSIVE ) ) {
					$pods = [ $pods ];
				}

				foreach ( $pods as $pod ) {
					$post_items[] = $pod['ID'];
				}
			} else {
				$post_items = get_field( $field, $cpost_id );
			}
		} elseif ( is_plugin_active( 'pods/init.php' ) ) {
			$pods = get_post_meta( $cpost_id, $field );
			if ( count( $pods ) === count( $pods, COUNT_RECURSIVE ) ) {
				$pods = [ $pods ];
			}
			foreach ( $pods as $pod ) {
				$post_items[] = $pod['ID'];
			}
		} else {
			$post_items = get_field( $field, $cpost_id );
		}

		$repeater = Aepro::$_helper->is_repeater_block_layout();
		if ( $repeater['is_repeater'] ) {
			if ( isset( $repeater['field'] ) ) {
				$repeater_field = get_field( $repeater['field'], $cpost_id );
				$post_items     = $repeater_field[0][ $settings['acf_relation_field'] ];
			} else {
				$post_items = get_sub_field( $settings['acf_relation_field'] );
			}
		}

		$flexible = Aepro::$_helper->is_flexible_block_layout();

		if ( $flexible['is_flexible'] ) {
			if ( isset( $flexible['field'] ) ) {
				$flexible_field = get_field( $flexible['field'], $cpost_id );
				foreach($flexible_field as $flex_layout){
					if(array_key_exists($settings['acf_relation_field'],$flex_layout)){
						$post_items = $flex_layout[$settings['acf_relation_field']];
					}
				}
			} else {
				$post_items = get_sub_field( $settings['acf_relation_field'] );
			}
		} 
		// else {
		// 	$post_items = get_sub_field( $settings['acf_post_field'] );
		// }

		//if ( $post_items ) {
			$query_args = [
				'orderby'             => $settings['orderby'],
				'order'               => $settings['order'],
				'ignore_sticky_posts' => 1,
				'post_status'         => 'publish', // Hide drafts/private posts for admins
				'offset'              => $settings['offset'],
				'posts_per_page'      => $settings['posts_per_page'],
				'post_type'           => get_post_types( [ 'public' => true ], 'names' ),
				//'post__in'            => $post_items,
				'post__not_in'        => [ $cpost_id ],
			];

            if ( $settings['reverse_relation'] === 'yes' ) {
                $query_args['meta_query'] = array(
                    array(
                        'key' => $settings['acf_relation_field'],
                        'value' => $cpost_id,
                        'compare' => 'LIKE'
                    )
                );
            } else {
                $query_args['post__in'] = $post_items;
            }

			if ( $settings['orderby'] === 'meta_value' ) {
				$query_args['meta_key'] = $settings['orderby_metakey_name'];
			}

			if ( isset( $_POST['page_num'] ) || $paged > 1 ) {
				$query_args['offset'] = $this->calculate_offset( $settings, $query_args, $paged );
			}

            /* Filter Bar */
            // When there is Term Id in $_POST
            if(isset($settings['filter_taxonomy']) && (isset($_POST['term_id']) && $_POST['term_id'] >= 0)) {
                if($_POST['term_id'] != 0){
                    $selected_terms['relation'] = 'AND';
                    $selected_terms[] = array(
                        'taxonomy' => $settings['filter_taxonomy'],
                        'field' => 'term_id',
                        'terms' => $_POST['term_id'],
                    );
                }
            // When there is default term selected
            }elseif (isset($settings[$settings['filter_taxonomy'] . '_filter_default_term']) && $settings[$settings['filter_taxonomy'] . '_filter_default_term'] != 0 ){
                $selected_terms['relation'] = 'AND';
                $selected_terms[] = [
                    'taxonomy' => $settings['filter_taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => $settings[$settings['filter_taxonomy'] . '_filter_default_term'],
                ];
            // When there is 'All' tab is disabled
            }elseif(isset($settings['show_all']) && $settings['show_all'] != 'yes'){
                $filter_terms = Aepro::$_helper->get_filter_bar_filters($settings);
                $selected_terms['relation'] = 'AND';
                $selected_terms[] = [
                    'taxonomy' => $settings['filter_taxonomy'],
                    'field'    => 'term_id',
                    'terms'    => $filter_terms[0]->term_id,
                ];
            // When there is 'All' tab is disabled
            }
            /* Filter Bar End */
            if (is_array($selected_terms) && count($selected_terms)) {
                $query_args['tax_query'] = $selected_terms;
            }
		//}

		return $query_args;
	}

	private function post_object_query() {
		$settings   = $this->settings;
		$query_args = [];
		$paged      = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : '';
		$field      = $settings['acf_post_field'];

		if ( isset( $_POST['fetch_mode'] ) ) {
			$cpost_id = $_POST['cpid'];
			$cpost    = get_post( $cpost_id );
		} else {
			$cpost    = Aepro::$_helper->get_demo_post_data();
			$cpost_id = $cpost->ID;
		}

		$post_items = get_field( $field, $cpost_id );

		$repeater = Aepro::$_helper->is_repeater_block_layout();
		if ( $repeater['is_repeater'] ) {
			if ( isset( $repeater['field'] ) ) {
				$repeater_field = get_field( $repeater['field'], $cpost_id );
				$post_items     = $repeater_field[0][ $settings['acf_post_field'] ];
			} else {
				$post_items = get_sub_field( $settings['acf_post_field'] );
			}
		}

		$flexible = Aepro::$_helper->is_flexible_block_layout();
		if ( $flexible['is_flexible'] ) {
			if ( isset( $flexible['field'] ) ) {
				$flexible_field = get_field( $flexible['field'], $cpost_id );
				foreach($flexible_field as $flex_layout){
					if(array_key_exists($settings['acf_post_field'],$flex_layout)){
						$post_items = $flex_layout[$settings['acf_post_field']];
					}
				}
			} else {
				$post_items = get_sub_field( $settings['acf_post_field'] );
			}
		} else {
			$post_items = get_sub_field( $settings['acf_post_field'] );
		}

		if ( ! is_array( $post_items ) ) {
			$post_items = [ $post_items ];
		}

		if ( $post_items ) {
			$query_args = [
				'orderby'             => $settings['orderby'],
				'order'               => $settings['order'],
				'ignore_sticky_posts' => 1,
				'post_status'         => 'publish', // Hide drafts/private posts for admins
				'offset'              => $settings['offset'],
				'posts_per_page'      => $settings['posts_per_page'],
				'post_type'           => get_post_types( [ 'public' => true ], 'names' ),
				'post__in'            => $post_items,
				'post__not_in'        => [ $cpost_id ],
			];

			if ( $settings['orderby'] === 'meta_value' ) {
				$query_args['meta_key'] = $settings['orderby_metakey_name'];
			}

			if ( isset( $_POST['page_num'] ) || $paged > 1 ) {
				$query_args['offset'] = $this->calculate_offset( $settings, $query_args, $paged );
			}
		}

		return $query_args;
	}

	public function calculate_offset( $settings, $query_args, $paged ) {
		if ( $settings[ $settings['_skin'] . '_show_pagination' ] === 'no' ) {
			return 0;
		}

		if ( $settings[ $settings['_skin'] . '_disable_ajax' ] === 'yes' && $paged > 1 ) {
			$offset = ( $query_args['posts_per_page'] * ( $paged - 1 ) );

		} else {
			$offset = $query_args['posts_per_page'] * ( $this->get_current_page_num() - 1 );
		}

		if ( is_numeric( $query_args['offset'] ) ) {
			$offset += $query_args['offset'];
		}

		return $offset;
	}

	public function get_current_page_num() {
		$current = 1;

		if ( isset( $_POST['page_num'] ) ) {
			$current = $_POST['page_num'];
			return $current;
		}

		if ( is_front_page() && ! is_home() ) {
			$current = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
		} else {
			$current = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		return $current;
	}

	public function get_preview_query() {
		$current_post_id = get_the_ID();
		$render_mode     = get_post_meta( $current_post_id, 'ae_render_mode', true );

		$post_type = 'post';

		switch ( $render_mode ) {

			case 'author_template':
				$author_data                                     = Aepro::$_helper->get_preview_author_data();
										$query_args['author']    = $author_data['prev_author_id'];
										$query_args['post_type'] = 'any';
				break;

			case 'post_type_archive_template':
												$post_type               = get_post_meta( $current_post_id, 'ae_rule_post_type_archive', true );
												$query_args['post_type'] = $post_type;
				break;

			case 'archive_template':
				$term_data                                       = Aepro::$_helper->get_preview_term_data();
										$query_args['tax_query'] = [
											[
												'taxonomy' => $term_data['taxonomy'],
												'field'    => 'term_id',
												'terms'    => $term_data['prev_term_id'],
											],
										];
										$query_args['post_type'] = 'any';
				break;

			case 'date_template':
				$query_args['post_type'] = $post_type;
				break;

			default:
				$query_args['post_type'] = $post_type;
		}

		return $query_args;
	}
}
