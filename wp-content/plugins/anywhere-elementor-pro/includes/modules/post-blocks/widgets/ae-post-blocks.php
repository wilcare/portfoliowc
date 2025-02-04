<?php

namespace Aepro\Modules\PostBlocks\Widgets;

use Aepro\Aepro;
use Aepro\Frontend;
use Aepro\Post_Helper;
use Aepro\Base\Widget_Base;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Utils;
use Elementor\Plugin;
use ElementorPro\Modules\Woocommerce\Widgets\Elements;
use Elementor\Core\Files\CSS\Post as Post_CSS;
use WP_Query;


class AePostBlocks extends Widget_Base {
	public function get_name() {
		return 'ae-post-blocks';
	}

	public function get_title() {
		return __( 'AE - Post Blocks', 'ae-pro' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_keywords()
    {
        return [
            'posts',
			'cpt',
			'item',
			'blocks',
			'loop',
			'query',
			'grid',
			'carousel', 
			'custom post type'
        ];
    }

	public function get_script_depends() {

		// load all scripts in editor and preview mode
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {

			return [ 'jquery-masonry', 'ae-infinite-scroll', 'swiper' ];

		}

		$scripts  = [];
		$settings = $this->get_settings_for_display();

		if ( $settings['masonry_grid'] === 'yes' ) {
			$scripts[] = 'jquery-masonry';
		}

		if ( isset( $settings['show_infinite_scroll'] ) && $settings['show_infinite_scroll'] === 'yes' ) {
			$scripts[] = 'ae-infinite-scroll';
		}

		if ( $settings['layout_mode'] === 'carousel' ) {
			$scripts[] = 'swiper';
		}

		return $scripts;
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls() {

		$ae_post_types                         = Aepro::$_helper->get_rule_post_types();
		$ae_post_types_options                 = $ae_post_types;
		$ae_post_types_options['current_loop'] = __( 'Current Archive', 'ae-pro' );
		$ae_post_types_options['ae_by_id']     = __( 'Manual Selection', 'ae-pro' );
		$ae_post_types_options['related']      = __( 'Related Posts', 'ae-pro' );

		if ( class_exists( 'acf' ) || is_plugin_active( 'pods/init.php' ) ) {
			$ae_post_types_options['relation']    = __( 'Relationship', 'ae-pro' );
			$ae_post_types_options['post_object'] = __( 'Post (ACF)', 'ae-pro' );
		}

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'ae-pro' ),
			]
		);

		$this->add_control(
			'ae_post_block_adv_mention',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae-alert ae-alert-warning',
				'raw'             => __( 'Try <b>Post Blocks Advanced</b> - a new and enhanced version of this widget.', 'ae-pro' ),
				'separator'       => 'after',
			]
		);

		/**
		 *  Add new custom source
		 */
		$ae_post_types_options = apply_filters( 'aepro/post-blocks/custom-source', $ae_post_types_options );

		$this->add_control(
			'ae_post_type',
			[
				'label'   => __( 'Source', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $ae_post_types_options,
				'default' => key( $ae_post_types ),
			]
		);

		$this->add_control(
			'ae_post_type_relation_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_pro_alert',
				'raw'             => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Post Block Relationship', 'https://wpvibes.link/go/feature-post-block-relationship' ), 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'ae_post_type' => 'relation',
				],
			]
		);

		$this->add_control(
			'ae_post_type_related_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_pro_alert',
				'raw'             => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Post Block Related', 'https://wpvibes.link/go/feature-post-block-related' ), 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'ae_post_type' => 'related',
				],
			]
		);

		$block_layouts[''] = 'Select Block Layout';
		$block_layouts     = $block_layouts + Aepro::$_helper->ae_block_layouts();

		$this->add_control(
			'template',
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Block Layouts', 'https://wpvibes.link/go/feature-creating-block-layout/' ), 'ae-pro' ),
			]
		);

		/**
		 *  Add new controls for custom source
		 */

		do_action( 'aepro/post-blocks/custom-source-fields', $this );

		if ( class_exists( 'acf' ) && is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'relationship_type',
				[
					'label'     => __( 'Relationship Type', 'ae-pro' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'acf',
					'options'   => [
						'acf'  => __( 'ACF', 'ae-pro' ),
						'pods' => __( 'Pods', 'ae-pro' ),
					],
					'condition' => [
						'ae_post_type' => 'relation',
					],
				]
			);
		}

		$this->add_control(
			'ae_post_ids',
			[
				'label'       => __( 'Posts', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __( 'Selects Posts', 'ae-pro' ),
				'default'     => __( '', 'ae-pro' ),
				'condition'   => [
					'ae_post_type' => 'ae_by_id',
				],
			]
		);

		$this->add_control(
			'related_by',
			[
				'label'       => __( 'Related By', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __( 'Select Taxonomies', 'ae-pro' ),
				'default'     => '',
				'options'     => Aepro::$_helper->get_rules_taxonomies(),
				'condition'   => [
					'ae_post_type' => 'related',
				],
			]
		);
		$this->add_control(
			'related_match_with',
			[
				'label'     => __( 'Match With', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'OR',
				'options'   => [
					'OR'  => __( 'Anyone Term', 'ae-pro' ),
					'AND' => __( 'All Terms', 'ae-pro' ),
				],
				'condition' => [
					'ae_post_type' => 'related',
				],
			]
		);

		if ( class_exists( 'acf' ) || is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'acf_relation_field',
				[
					'label'       => __( 'Relationship Field', 'ae-pro' ),
					'tyoe'        => Controls_Manager::TEXT,
					'description' => __( 'Key of ACF / Pods Relationship Field', 'ae-pro' ),
					'condition'   => [
						'ae_post_type' => 'relation',
					],
				]
			);
		}

		if ( class_exists( 'acf' ) || is_plugin_active( 'pods/init.php' ) ) {
			$this->add_control(
				'acf_post_field',
				[
					'label'       => __( 'Post Field', 'ae-pro' ),
					'tyoe'        => Controls_Manager::TEXT,
					'description' => __( 'Key of ACF Post Field', 'ae-pro' ),
					'condition'   => [
						'ae_post_type' => 'post_object',
					],
				]
			);
		}

		$this->add_control(
			'author_ae_ids',
			[
				'label'       => 'Authors',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'placeholder' => __( 'Enter Author ID Separated by Comma', 'ae-pro' ),
				'options'     => Post_Helper::instance()->get_authors(),
				'conditions'  => [
					'relation' => 'and',
					'terms'    => [
						[
							'name'     => 'ae_post_type',
							'operator' => '!==',
							'value'    => 'ae_by_id',
						],
						[
							'name'     => 'ae_post_type',
							'operator' => '!==',
							'value'    => 'current_loop',
						],
						[
							'name'     => 'ae_post_type',
							'operator' => '!==',
							'value'    => 'related',
						],
						[
							'name'     => 'ae_post_type',
							'operator' => '!==',
							'value'    => 'relation',
						],
						[
							'name'     => 'ae_post_type',
							'operator' => '!==',
							'value'    => 'post_object',
						],
					],
				],
			]
		);

		$this->add_control(
			'taxonomy_heading',
			[
				'label'     => __( 'Taxonomy', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'after',
				'condition' => [
					'ae_post_type' => 'post',
				],
			]
		);

		$ae_taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];

		$ae_taxonomies = get_taxonomies( $ae_taxonomy_filter_args, 'objects' );

		foreach ( $ae_taxonomies as $ae_taxonomy => $object ) {
			$this->add_control(
				$ae_taxonomy . '_ae_ids',
				[
					'label'       => $object->label,
					'type'        => Controls_Manager::SELECT2,
					'multiple'    => true,
					'label_block' => true,
					'placeholder' => __( 'Enter ' . $object->label . ' ID Separated by Comma', 'ae-pro' ),
					'object_type' => $ae_taxonomy,
					'options'     => Post_Helper::instance()->get_taxonomy_terms( $ae_taxonomy ),
					'condition'   => [
						'ae_post_type' => $object->object_type,
					],
				]
			);
		}

		$this->add_control(
			'tax_relation',
			[
				'label'     => __( 'Taxonomy Relation', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'AND',
				'options'   => [
					'OR'  => __( 'OR', 'ae-pro' ),
					'AND' => __( 'AND', 'ae-pro' ),
				],
				'condition' => [
					'ae_post_type!' => [ 'current_loop', 'ae_by_id', 'related', 'relation', 'post_object' ],
				],
			]
		);

		$this->add_control(
			'taxonomy_divider',
			[
				'type' => Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'current_post',
			[
				'label'        => __( 'Exclude Current Post', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'ae_post_type!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'advanced',
			[
				'label'     => __( 'Advanced', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'ae_post_type!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'           => __( 'Order By', 'ae-pro' ),
				'type'            => Controls_Manager::SELECT,
				'content_classes' => 'ae_conditional_fields',
				'default'         => 'post_date',
				'options'         => [
					'post_date'      => __( 'Date', 'ae-pro' ),
					'post_title'     => __( 'Title', 'ae-pro' ),
					'menu_order'     => __( 'Menu Order', 'ae-pro' ),
					'rand'           => __( 'Random', 'ae-pro' ),
					'post__in'       => __( 'Manual', 'ae-pro' ),
					'meta_value'     => __( 'Custom Field', 'ae-pro' ),
					'meta_value_num' => __( 'Custom Field (Numeric)', 'ae-pro' ),
				],
				'condition'       => [
					'ae_post_type!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'orderby_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_order_by_alert',
				'raw'             => __( "<div class='elementor-control-field-description'>Note: Order By 'Manual' is only applicable when Source is 'Manual Selection' and 'Relationship' </div>", 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'orderby' => 'post__in',
				],
			]
		);

		$this->add_control(
			'orderby_metakey_name',
			[
				'label'       => __( 'Meta Key Name', 'ae-pro' ),
				'tyoe'        => Controls_Manager::TEXT,
				'description' => __( 'Custom Field Key', 'ae-pro' ),
				'condition'   => [
					'ae_post_type!' => 'current_loop',
					'orderby'       => [ 'meta_value', 'meta_value_num' ],
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label'     => __( 'Order', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'desc',
				'options'   => [
					'asc'  => __( 'ASC', 'ae-pro' ),
					'desc' => __( 'DESC', 'ae-pro' ),
				],
				'condition' => [
					'ae_post_type!' => 'current_loop',
					'orderby!'      => 'post__in',
				],
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'     => __( 'Posts Count', 'ae-pro' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'condition' => [
					'ae_post_type!' => 'current_loop',
				],
			]
		);

		$this->add_control(
			'offset',
			[
				'label'       => __( 'Offset', 'ae-pro' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => 0,
				'condition'   => [
					'ae_post_type!' => [ 'current_loop', 'ae_by_id' ],
				],
				'description' => __( 'Use this setting to skip over posts (e.g. \'2\' to skip over 2 posts).', 'ae-pro' ),
			]
		);

		$this->add_control(
			'query_filter',
			[
				'label'       => __( 'Query Filter', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( Aepro::$_helper->get_widget_admin_note_html( '<span style="color:red">Danger Ahead!!</span> It is a developer oriented feature. Only use if you know how exaclty WordPress queries and filters works.', 'https://wpvibes.link/go/feature-post-blocks-query-filter', 'Read Instructions' ), 'ae-pro' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'ae-pro' ),
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'        => __( 'Layout Mode', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'list'          => __( 'List', 'ae-pro' ),
					'grid'          => __( 'Grid', 'ae-pro' ),
					'carousel'      => __( 'Carousel', 'ae-pro' ),
					'smart-grid'    => __( 'Smart Grid', 'ae-pro' ),
					'checker-board' => __( 'Checker Board', 'ae-pro' ),
				],
				'default'      => 'grid',
				'prefix_class' => 'ae-post-layout-',
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'layout_mode_alert',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'content_classes' => 'ae_layout_mode_alert',
				'raw'             => __( Aepro::$_helper->get_widget_admin_note_html( 'Know more about Post Block Carousel', 'https://wpvibes.link/go/feature-post-block-carousel' ), 'ae-pro' ),
				'separator'       => 'none',
				'condition'       => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'sg_layout',
			[
				'label'        => __( 'Choose Grid Layout', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => $this->smart_grid_layouts(),
				'label_block'  => true,
				'prefix_class' => 'ae-sg-',
				'render_type'  => 'template',
				'default'      => 'layout1',
				'toggle'       => false,
				'condition'    => [
					'layout_mode' => 'smart-grid',
				],
			]
		);

		$this->add_control(
			'alt_template',
			[
				'label'       => __( 'Alternate Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( 'Know more about Block Layouts <a href="http://aedocs.webtechstreet.com/article/9-creating-block-layout-in-anywhere-elementor-pro" target="_blank">Click Here</a>', 'ae-pro' ),
				'condition'   => [
					'layout_mode' => [ 'smart-grid', 'checker-board' ],
				],
			]
		);

		$this->add_control(
			'tablet_cols',
			[
				'label'           => __( 'Tablet Cols', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '2',
				'min'             => 1,
				'max'             => 12,
				'condition'       => [
					'layout_mode' => 'smart-grid',
				],
				'selectors'       => [
					'(tablet){{WRAPPER}} .ae-post-list-wrapper' => 'grid-template-columns:repeat( {{VALUE}} , 1fr )',
					'(tablet){{WRAPPER}} .ae-post-list-wrapper .ae-post-list-item' => 'grid-row:unset; grid-column:unset;',
				],
			]
		);

		$this->add_control(
			'mobile_cols',
			[
				'label'           => __( 'Mobile Cols', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '1',
				'min'             => 1,
				'max'             => 12,
				'condition'       => [
					'layout_mode' => 'smart-grid',
				],
				'selectors'       => [
					'(mobile){{WRAPPER}} .ae-post-list-wrapper' => 'grid-template-columns:repeat( {{VALUE}} , 1fr )',
					'(mobile){{WRAPPER}} .ae-post-list-wrapper .ae-post-list-item' => 'grid-row:unset; grid-column:unset;',
				],
			]
		);

		$this->add_control(
			'masonry_grid',
			[
				'label'        => __( 'Masonry', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'masonry_on'   => __( 'On', 'ae-pro' ),
				'masonry_off'  => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => [ 'grid', 'checker-board' ],
				],
			]
		);

		$this->add_control(
			'show_infinite_scroll',
			[
				'label'        => __( 'Infinite Scroll', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => [ 'grid', 'list', 'smart-grid', 'checker-board' ],
				],
			]
		);

		$this->add_control(
			'show_pagination',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'yes' => __( 'Yes', 'ae-pro' ),
					'no'  => __( 'No', 'ae-pro' ),
				],
				'default'   => 'yes',
				'condition' => [
					'layout_mode'          => [ 'grid', 'list', 'smart-grid', 'checker-board' ],
					'show_infinite_scroll' => '',
				],

			]
		);

		$this->add_control(
			'enable_overlay',
			[
				'label'        => __( 'Overlay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [

					'layout_mode' => [ 'grid', 'list', 'smart-grid', 'checker-board' ],
				],
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label'           => __( 'Columns', 'ae-pro' ),
				'type'            => Controls_Manager::NUMBER,
				'desktop_default' => '3',
				'tablet_default'  => '2',
				'mobile_default'  => '1',
				'min'             => 1,
				'max'             => 12,
				'condition'       => [
					'layout_mode' => [ 'grid', 'checker-board' ],
				],
				'selectors'       => [
					'{{WRAPPER}} .ae-post-list-item' => 'width: calc(100%/{{VALUE}})',
				],
				'render_type'     => 'template',
			]
		);

		$this->add_responsive_control(
			'item_col_gap',
			[
				'label'     => __( 'Column Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 10,
				],
				'condition' => [
					'layout_mode' => [ 'grid', 'checker-board' ],
				],
				'selectors' => [
					'{{WRAPPER}}.ae-post-layout-grid article.ae-post-list-item' => 'padding-left:{{SIZE}}{{UNIT}}; padding-right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-post-layout-grid .ae-pagination-wrapper' => 'padding-right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-post-layout-checker-board article.ae-post-list-item' => 'padding-left:{{SIZE}}{{UNIT}}; padding-right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.ae-post-layout-checker-board .ae-pagination-wrapper' => 'padding-right:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-pagination-wrapper' => 'padding-left:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aep-filter-bar'        => 'padding-left:{{SIZE}}{{UNIT}}; padding-right:{{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'item_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}  article.ae-post-list-item' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode!' => [ 'carousel', 'smart-grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'sg_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}  .ae-post-list-wrapper' => 'grid-row-gap:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => [ 'smart-grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'sg_col_gap',
			[
				'label'     => __( 'Col Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}  .ae-post-list-wrapper' => 'grid-column-gap:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => [ 'smart-grid' ],
				],
			]
		);

		$this->add_responsive_control(
			'carousel_item_row_gap',
			[
				'label'     => __( 'Row Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 0,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-post-list-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);
		if ( class_exists( 'WooCommerce' ) ) {
			$this->add_control(
				'sale_badge_switcher',
				[
					'label'        => __( 'Enable Sales Badge', 'ae-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => __( 'Yes', 'ae-pro' ),
					'label_off'    => __( 'No', 'ae-pro' ),
					'return_value' => 'yes',
				]
			);
		}

		$this->add_control(
			'no_posts_message',
			[
				'label'     => __( 'No Posts Message', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->pagination_controls();

		$this->loading_overlay();

		$this->start_controls_section(
			'layout_style',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-article-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-article-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-article-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-article-inner',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'overlay_style_section',
			[
				'label'     => __( 'Overlay', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_overlay' => 'yes',
				],
			]
		);

		$this->add_control(
			'overlay_style',
			[
				'label'     => __( 'Loading Overlay', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'overlay_color',
				'label'          => __( 'Overlay Color', 'ae-pro' ),
				'types'          => [ 'none', 'classic', 'gradient' ],
				'selector'       => '{{WRAPPER}} .ae-post-overlay',
				'exclude'        => [ 'image' ],
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'default' => 'rgba(0,0,0,0.5)',
					],
				],
			]
		);

		$this->add_control(
			'overlay_icon_color',
			[
				'label'     => __( 'Icon Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-icon i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-post-overlay-icon svg' => 'fill:{{VAlUE}};',
				],
				'default'   => '#FFFFFF',
			]
		);

		$this->add_control(
			'overlay_icon_size',
			[
				'label'     => __( 'Icon Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 50,
					],
				'range'     =>
					[
						'min'  => 10,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-icon i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-post-overlay-icon svg' => 'width : {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'overlay_text_color',
			[
				'label'     => __( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-post-overlay-text' => 'color:{{VAlUE}};',
				],
				'default'   => '#FFFFFF',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'overlay_text_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-post-overlay-text',
			]
		);

		$this->add_responsive_control(
			'overlay_text_position',
			[
				'label'                => __( 'Text / Icon Position', 'ae-pro' ),
				'type'                 => Controls_Manager::CHOOSE,
				'label_block'          => true,
				'options'              => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
				],
				'selectors_dictionary' => [
					'top'    => 'position: absolute; top 0; left: 50%',
					'left'   => 'position: absolute; top: 0; left: 0;',
					'right'  => 'position: absolute; top: 0; right: 0;',
					'bottom' => 'position: absolute; bottom: 0; left: 50%;',
					'middle' => 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, 50%)',
				],
				'selectors'            => [
					'{{WRAPPER}} .ae-post-overlay-wrapper' => '{{VALUE}}',
				],
				'default'              => 'middle',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'carousel_style',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Prev/Next Navigation', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);
		$this->start_controls_tabs( 'tabs_arrow_styles' );

		$this->start_controls_tab(
			'tab_arrow_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'arrow_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'fill:{{VAlUE}};',
				],
				'default'   => '#444',
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'arrow_border',
				'label'     => __( 'Border', 'ae-pro' ),
				'selector'  => '{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-next',
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev, {{WRAPPER}} .ae-swiper-button-prev' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next, {{WRAPPER}} .ae-swiper-button-next' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						'navigation_button' => 'yes',
					],
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_arrow_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);
		$this->add_control(
			'arrow_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover i' => 'color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-prev:hover svg' => 'fill:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover svg' => 'fill:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_bg_color_hover',
			[
				'label'     => __( ' Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'background-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_color_hover',
			[
				'label'     => __( ' Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev:hover' => 'border-color:{{VAlUE}};',
					'{{WRAPPER}} .ae-swiper-button-next:hover' => 'border-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_control(
			'arrow_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-prev:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
					'{{WRAPPER}} .ae-swiper-container .ae-swiper-button-next:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
				'condition'  =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'arrow_size',
			[
				'label'     => __( 'Arrow Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 50,
					],
				'range'     =>
					[
						'min'  => 20,
						'max'  => 100,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-button-prev i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-next i' => 'font-size:{{SIZE}}px;',
					'{{WRAPPER}} .ae-swiper-button-prev svg' => 'width : {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next svg' => 'width : {{SIZE}}{{UNIT}};',
				],
				'condition' =>
					[
						'navigation_button' => 'yes',
					],
			]
		);

		$this->add_responsive_control(
			'arrow_horizontal_position',
			[
				'label'       => __( 'Horizontal Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'default'     => 'center',
				'condition'   => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_vertical_position',
			[
				'label'       => __( 'Vertical Position', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'     => 'center',
				'condition'   => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',

				],
			]
		);

		$this->add_responsive_control(
			'horizontal_arrow_offset',
			[
				'label'          => __( 'Horizontal Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-hpos-left .ae-swiper-button-wrapper' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-right .ae-swiper-button-wrapper' => 'right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-prev' => 'left: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-hpos-center .ae-swiper-button-next' => 'right: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);
		$this->add_responsive_control(
			'vertical_arrow_offset',
			[
				'label'          => __( 'Vertical Offset', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-vpos-top .ae-swiper-button-wrapper' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-bottom .ae-swiper-button-wrapper' => 'bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-prev' => 'top: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-vpos-middle .ae-swiper-button-next' => 'top: {{SIZE}}{{UNIT}}',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'inside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_gap',
			[
				'label'          => __( 'Arrow Gap', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ '%', 'px' ],
				'default'        => [
					'unit' => 'px',
					'size' => '25',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range'          =>
					[
						'min'  => 1,
						'max'  => 1000,
						'step' => 1,
					],
				'selectors'      => [
					'{{WRAPPER}} .ae-post-widget-wrapper .ae-swiper-container' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-post-widget-wrapper .ae-swiper-outer-wrapper' => 'position: relative',
					'{{WRAPPER}} .ae-swiper-button-prev' => 'left: 0',
					'{{WRAPPER}} .ae-swiper-button-next' => 'right: 0',

				],
				'condition'      => [
					'navigation_button' => 'yes',
					'arrows_layout'     => 'outside',
				],
			]
		);

		$this->add_responsive_control(
			'arrow_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-button-prev' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .ae-swiper-button-next' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_style_dots',
			[
				'label'     => __( 'Dots', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'dots_size',
			[
				'label'     => __( 'Dots Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'width:{{SIZE}}px; height:{{SIZE}}px;',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'dots_color',
			[
				'label'     => __( 'Active Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet-active' => 'background-color:{{VAlUE}} !important;',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'inactive_dots_color',
			[
				'label'     => __( 'Inactive Dot Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-bullet' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_bullet_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'bullets',
					],
			]
		);

		$this->add_control(
			'heading_style_scroll',
			[
				'label'     => __( 'Scrollbar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);
		$this->add_control(
			'scroll_size',
			[
				'label'     => __( 'Scrollbar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-vertical .ae-swiper-scrollbar' => 'width:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-horizontal .ae-swiper-scrollbar' => 'height:{{SIZE}}px;',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'scrollbar_color',
			[
				'label'     => __( 'Scrollbar Drag Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-scrollbar-drag' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'scroll_color',
			[
				'label'     => __( 'Scrollbar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-swiper-scrollbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'scrollbar' => 'yes',
					],
			]
		);

		$this->add_control(
			'heading_style_progress',
			[
				'label'     => __( 'Progress Bar', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' =>
					[
						'ptype' => 'progress',
					],
			]
		);
		$this->add_control(
			'progressbar_color',
			[
				'label'     => __( 'Prgress Bar Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'progress',
					],
			]
		);

		$this->add_control(
			'progress_color',
			[
				'label'     => __( 'Prgress Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .swiper-pagination-progressbar-fill' => 'background-color:{{VAlUE}};',
				],
				'condition' =>
					[
						'ptype' => 'progress',
					],
			]
		);

		$this->add_control(
			'progressbar_size',
			[
				'label'     => __( 'Prgress Bar Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   =>
					[
						'size' => 5,
					],
				'range'     =>
					[
						'min'  => 1,
						'max'  => 10,
						'step' => 1,
					],
				'selectors' => [
					'{{WRAPPER}} .swiper-container-horizontal .swiper-pagination-progressbar' => 'height:{{SIZE}}px;',
					'{{WRAPPER}} .swiper-container-vertical .swiper-pagination-progressbar' => 'width:{{SIZE}}px;',
				],
				'condition' =>
					[
						'ptype' => 'progress',
					],
			]
		);

		$this->add_responsive_control(
			'pagination_progress_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-swiper-pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  =>
					[
						'ptype' => 'progress',
					],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'pagination_style',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_mode'          => [ 'list', 'grid', 'checker-board' ],
					'show_pagination'      => 'yes',
					'show_infinite_scroll' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->add_responsive_control(
			'item_gap',
			[
				'label'     => __( 'Item Gap', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'margin-left:{{SIZE}}{{UNIT}}; margin-right:{{SIZE}}{{UNIT}};',
				],

			]
		);

		$this->add_responsive_control(
			'pi_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'pi_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pi_bg',
			[
				'label'     => __( 'Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'background-color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pi_hover_color',
			[
				'label'     => __( 'Hover/Current Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper .current' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper span:hover' => 'color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper a:hover' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'pi_hover_bg',
			[
				'label'     => __( 'Hover/Current Background', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper .current' => 'background-color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper span:hover' => 'background-color:{{VALUE}}',
					'{{WRAPPER}} .ae-pagination-wrapper a:hover' => 'background-color:{{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'pi_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->add_control(
			'pi_border_hover_color',
			[
				'label'     => __( 'Border Hover Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper *:hover' => 'border-color: {{VALUE}}',
				],
				'condition' => [
					'pi_border_border!' => '',
				],
			]
		);

		$this->add_control(
			'pi_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-pagination-wrapper *' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'pagination_box_shadow',
				'label'    => __( 'Box Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-pagination-wrapper *',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'infinite_scroll_style',
			[
				'label'     => __( 'Infinite Scroll', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'loadmore_align',
			[
				'label'     => __( 'Align', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'default'   => 'center',
				'selectors' => [
					'{{WRAPPER}} .load-more-wrapper'       => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .infinite-scroll-last'    => 'text-align: {{VALUE}}',
					'{{WRAPPER}} .infinite-scroll-request' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ias_loader_color',
			[
				'label'     => __( 'Loader Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .loader-ellips__dot' => 'background-color:{{VALUE}}',
				],
				'condition' => [
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'last_message_heading',
			[
				'label'     => __( 'Last Message', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'last_message_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'selector' => '{{WRAPPER}} .infinite-scroll-last',
			]
		);

		$this->add_control(
			'last_message_bg',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .infinite-scroll-last' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_heading',
			[
				'label'     => __( 'Load More', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'load_more_typography',
				'label'    => __( 'Typography', 'ae-pro' ),
				'global'   => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector' => '{{WRAPPER}} .view-more-button',
			]
		);

		$this->start_controls_tabs( 'tabs_read_more_styles' );

		$this->start_controls_tab(
			'tab_load_more_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'load_more_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .view-more-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_bg',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .view-more-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'load_more_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'default'  => '1px',
				'selector' => '{{WRAPPER}} .view-more-button',
			]
		);

		$this->add_control(
			'load_more_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .view-more-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'load_more_text_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .view-more-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'load_more_text_margin',
			[
				'label'      => __( 'Margin', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .view-more-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_load_more_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'load_more_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .view-more-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_bg_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
				'selectors' => [
					'{{WRAPPER}} .view-more-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_border_color_hover',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .view-more-button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'load_more_border_radius_hover',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .view-more-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->sale_badge_styles();

		$this->infinite_scroll_controls();

		$this->carousel_controls();

		$this->sale_badge_controls();
	}

	protected function render() {

		$settings = $this->get_settings_for_display();
		
		if ( ! isset( $settings['template'] ) || empty( $settings['template'] ) || get_post_status( $settings['template'] ) !== 'publish' ) {
			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || (isset($_GET['preview']) && $_GET['preview'] === 'true') ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Content > Query > Block Layout'", 'ae-pro' ) ) );
			}
		} else {
			$this->generate_output( $settings );
		}
	}

	public function generate_output( $settings, $with_wrapper = true ) {

		$post_type            = $settings['ae_post_type'];
		$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates', true );

		$settings['direction'] = 'horizontal';

		// Securing Current post of Parent Query
		$prev_post = get_post();

		list($post_items, $query_args) = $this->get_posts( $settings );

		if ( ! isset( $query_args ) && $post_type !== 'current_loop' ) {
			return;
		}

		if ( $settings['layout_mode'] === 'carousel' ) {

			$pagination_type   = $settings['ptype'];
			$navigation_button = $settings['navigation_button'];
			$scrollbar         = $settings['scrollbar'];
			$ptype             = $settings['ptype'];
			$arrows_layout     = $settings['arrows_layout'];

			$swiper_data = $this->get_swiper_data( $settings );

			$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper' );

			$this->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );

			if ( $ptype !== '' ) {
				if ( $ptype === 'progress' ) {
					$ptype = 'progressbar';
				}
			}
		}

		$alt_layout = [];
		if ( $settings['layout_mode'] === 'smart-grid' ) {
			$sg_layouts = $this->smart_grid_layouts();
			$alt_layout = $sg_layouts[ $settings['sg_layout'] ]['alternate_layouts'];
		}

		$masonry              = $settings['masonry_grid'];
		$ias                  = $settings['show_infinite_scroll'];
		$ias_load_with_button = $settings['ias_load_with_button'];
		$ias_load_offest_page = $settings['ias_load_offest_page'];
		if ( $settings['ias_history_disable'] === 'yes' ) {
			$ias_history_disable = 'false';
		} else {
			$ias_history_disable = 'replace';
		}

		$this->add_render_attribute( 'post-list-wrapper', 'class', 'ae-post-list-wrapper' );
		$this->add_render_attribute( 'article-inner', 'class', 'ae-article-inner' );

		$this->add_render_attribute( 'post-widget-wrapper', 'data-pid', get_the_ID() );
		$this->add_render_attribute( 'post-widget-wrapper', 'data-wid', $this->get_id() );
		$this->add_render_attribute( 'post-widget-wrapper', 'data-source', $settings['ae_post_type'] );
		$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-post-widget-wrapper' );

		if ( $ias === 'yes' ) {
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-ias-load-with-button-' . $ias_load_with_button );
			$this->add_render_attribute( 'post-widget-wrapper', 'data-load-offset-page', $ias_load_offest_page );
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-ias-' . $ias );
			$this->add_render_attribute( 'post-widget-wrapper', 'data-ias-history', $ias_history_disable );
		}

		if ( $settings['layout_mode'] === 'carousel' ) {
			if ( ! isset( $settings['arrow_horizontal_position'] ) && $arrows_layout === 'outside' ) {
				$settings['arrow_horizontal_position'] = 'center';
			}
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-carousel-yes' );
		} else {
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-masonry-' . $masonry );

		}

		$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-hpos-' . $settings['arrow_horizontal_position'] );
		$this->add_render_attribute( 'post-widget-wrapper', 'class', 'ae-vpos-' . $settings['arrow_vertical_position'] );

		if ( $post_type === 'current_loop' || $settings['disable_ajax'] === 'yes' ) {
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'no-ajax' );
		}

		if ( isset( $settings['disable_scroll_on_ajax_load'] ) && $settings['disable_scroll_on_ajax_load'] !== 'yes' ) {
			$this->add_render_attribute( 'post-widget-wrapper', 'data-disable_scroll_on_ajax_load', 'no' );
			$this->add_render_attribute( 'post-widget-wrapper', 'data-pagination_scroll_top_offset', $settings['pagination_scroll_top_offset']['size'] );
		}

		$this->add_render_attribute( 'post-list-item', 'class', [ 'ae-post-list-item', 'ae-post-list-item-' . $this->get_id() ] );

		if ( isset( $settings['sale_badge_switcher'] ) && $settings['sale_badge_switcher'] === 'yes' ) {
			$this->add_render_attribute( 'post-widget-wrapper', 'class', 'sale-badge-' . $settings['sale_badge_switcher'] );
			$this->add_render_attribute( 'article-inner', 'class', 'badge-type-' . $settings['sale_badge_type'] );
		}

		$with_css = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}

		?>
		<?php if ( $settings['enable_overlay'] ) { ?>
			<div class="ae-post-overlay">
				<div class="ae-post-overlay-wrapper">
					<div class="ae-post-overlay-icon">
						<?php Icons_Manager::render_icon( $settings['overlay_icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</div>
					<div class="ae-post-overlay-text">
						<?php echo $settings['overlay_text']; ?>
					</div>
				</div>
			</div>
		<?php } ?>

		<?php if ( $with_wrapper ) { ?>
			<div <?php echo $this->get_render_attribute_string( 'post-widget-wrapper' ); ?>>
		<?php } ?>

		<?php if ( $settings['layout_mode'] === 'carousel' ) { ?>
		<div <?php echo $this->get_render_attribute_string( 'outer-wrapper' ); ?> >
			<?php $this->add_render_attribute( 'swiper-container', 'class', [ 'ae-swiper-container', 'swiper-container' ] ); ?>
		<div <?php echo $this->get_render_attribute_string( 'swiper-container' ); ?> >
			<?php $this->add_render_attribute( 'post-list-wrapper', 'class', [ 'ae-swiper-wrapper', 'swiper-wrapper' ] ); ?>
			<?php $this->add_render_attribute( 'post-list-item', 'class', [ 'ae-swiper-slide', 'swiper-slide' ] ); ?>
			<?php $this->add_render_attribute( 'article-inner', 'class', 'ae-swiper-slide-wrapper' ); ?>

	<?php } ?>
		<div <?php echo $this->get_render_attribute_string( 'post-list-wrapper' ); ?>>
			<?php

			if ( $post_items->have_posts() ) {
				$seq = 0;

				global $post;

				$css_file = Post_CSS::create( $settings['template'] );
				$css_file->enqueue();
				global $wp_query;
				$old_queried_object = $wp_query->queried_object;
				if ( ! isset( $query_args ) ) {
					// Current Archive

					while ( have_posts() ) {

						$seq++;

						$template = $this->get_template( $seq, $settings, $alt_layout );

						the_post();
						Frontend::$_ae_post_block = get_the_ID();

						/**
						 * @since 2.15
						 */
						$this->item_html( $settings, $template, $with_css );
					}
				} else {

					while ( $post_items->have_posts() ) {

						$seq++;

						$template = $this->get_template( $seq, $settings, $alt_layout );

						$post_items->the_post();
						Frontend::$_ae_post_block = get_the_ID();
						$wp_query->queried_object = $post;
						/**
						 * @since 2.15
						 */
						$this->item_html( $settings, $template, $with_css );
					}
				}

				$wp_query->queried_object = $old_queried_object;

				Frontend::$_ae_post_block = 0;
			} else {
				?>
				<div class="ae-no-posts">
					<?php echo do_shortcode( $settings['no_posts_message'] ); ?>
				</div>
				<?php
			}

			$post = $prev_post;
			setup_postdata( $post );
			?>
		</div>
		<?php if ( $settings['layout_mode'] === 'carousel' ) { ?>
			<?php if ( $pagination_type !== '' ) { ?>
			<div class = "ae-swiper-pagination swiper-pagination"></div>
		<?php } ?>

			<?php if ( $navigation_button === 'yes' && $arrows_layout === 'inside' ) { ?>
				<?php
				if ( $settings['arrow_horizontal_position'] !== 'center' ) {
					;
					?>
				<div class="ae-swiper-button-wrapper">
			<?php } ?>
			<div class = "ae-swiper-button-prev swiper-button-prev">
				<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-up"></i>
				<?php } else { ?>
					<?php
					if ( is_rtl() ) {
						Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
					} else {
						Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
					}
					?>
				<?php } ?>
			</div>
			<div class = "ae-swiper-button-next swiper-button-next">
				<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-down"></i>
				<?php } else { ?>
					<?php
					if ( is_rtl() ) {
						Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
					} else {
						Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
					}
					?>
				<?php } ?>
			</div>
				<?php
				if ( $settings['arrow_horizontal_position'] !== 'center' ) {
					;
					?>
				</div>
			<?php } ?>
		<?php } ?>

			<?php if ( $scrollbar === 'yes' ) { ?>
			<div class = "ae-swiper-scrollbar swiper-scrollbar"></div>

		<?php } ?>
		</div>
			<?php if ( $navigation_button === 'yes' && $arrows_layout === 'outside' ) { ?>
				<?php
				if ( $settings['arrow_horizontal_position'] !== 'center' ) {
					;
					?>
				<div class="ae-swiper-button-wrapper">
			<?php } ?>
			<div class = "ae-swiper-button-prev swiper-button-prev">
				<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-up"></i>
				<?php } else { ?>
					<?php
					if ( is_rtl() ) {
						Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
					} else {
						Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
					}
					?>
				<?php } ?>
			</div>
			<div class = "ae-swiper-button-next swiper-button-next">
				<?php if ( $settings['direction'] === 'vertical' ) { ?>
					<i class="fa fa-angle-down"></i>
				<?php } else { ?>
					<?php
					if ( is_rtl() ) {
						Icons_Manager::render_icon( $settings['arrow_icon_left'], [ 'aria-hidden' => 'true' ] );
					} else {
						Icons_Manager::render_icon( $settings['arrow_icon_right'], [ 'aria-hidden' => 'true' ] );
					}
					?>
				<?php } ?>
			</div>
				<?php
				if ( $settings['arrow_horizontal_position'] !== 'center' ) {
					;
					?>
				</div>
			<?php } ?>
		<?php } ?>
		</div>
	<?php } ?>
		<?php
		if ( $ias === 'yes' ) {
			$last_page_text = $settings['ias_last_page_text'];
			?>
			<div class="scroller-status">
				<div class="infinite-scroll-request loader-ellips">
					<span class="loader-ellips__dot"></span>
					<span class="loader-ellips__dot"></span>
					<span class="loader-ellips__dot"></span>
					<span class="loader-ellips__dot"></span>
				</div>
				<p class="infinite-scroll-last"><?php echo $last_page_text; ?></p>
				<p class="infinite-scroll-error">No more pages to load</p>
			</div>
			<?php if ( $ias_load_with_button === 'yes' && $settings['layout_mode'] !== 'carousel' ) { ?>
				<div class="load-more-wrapper"><button class="view-more-button" style="display:none;"> <?php echo $settings['ias_load_more_button_text']; ?> </button ></div>
			<?php } ?>
		<?php } ?>



		<?php

		$this->pagination_markup( $settings, $post_items );

		?>

		<?php if ( $with_wrapper ) { ?>
			</div>
		<?php } ?>

		<?php
	}

	public function infinite_scroll_controls() {

		$this->start_controls_section(
			'infinite_scroll_controls',
			[
				'label'     => __( 'Infinite Scroll', 'ae-pro' ),
				'condition' => [
					'layout_mode!'         => 'carousel',
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'ias_last_page_text',
			[
				'label'       => __( 'Last Page Text', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Last Page Text', 'ae-pro' ),
				'default'     => __( 'End of content', 'ae-pro' ),
				'condition'   => [
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'ias_load_with_button',
			[
				'label'        => __( 'Load With Button', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'ias_load_more_button_text',
			[
				'label'       => __( 'Button Text', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Button Text', 'ae-pro' ),
				'default'     => __( 'View More', 'ae-pro' ),
				'condition'   => [
					'ias_load_with_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'ias_load_offest_page',
			[
				'label'       => __( 'Offset Page', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Offset Page', 'ae-pro' ),
				'default'     => __( '2', 'ae-pro' ),
				'condition'   => [
					'ias_load_with_button' => 'yes',
					'show_infinite_scroll' => 'yes',
				],
			]
		);

		$this->add_control(
			'ias_history_disable',
			[
				'label'        => __( 'Disable History', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'show_infinite_scroll' => 'yes',
				],
			]
		);
		$this->end_controls_section();
	}

	public function pagination_controls() {

		$this->start_controls_section(
			'pagination_contols',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'condition' => [
					'layout_mode!'         => 'carousel',
					'show_pagination'      => 'yes',
					'show_infinite_scroll' => '',
				],
			]
		);

		$this->add_control(
			'show_prev_next',
			[
				'label'        => __( 'Show Prev/Next', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Show', 'ae-pro' ),
				'label_off'    => __( 'Hide', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'show_pagination' => 'yes',
				],
			]
		);

		$this->add_control(
			'disable_ajax',
			[
				'label'        => __( 'Disable Ajax', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'show_pagination' => 'yes',
					'ae_post_type!'   => 'current_loop',
				],
			]
		);

		$this->add_control(
			'prev_text',
			[
				'label'     => __( 'Previous Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( '&laquo; Previous', 'ae-pro' ),
				'condition' => [
					'show_pagination' => 'yes',
					'show_prev_next'  => 'yes',
				],
			]
		);

		$this->add_control(
			'next_text',
			[
				'label'     => __( 'Next Text', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Next &raquo;', 'ae-pro' ),
				'condition' => [
					'show_pagination' => 'yes',
					'show_prev_next'  => 'yes',
				],
			]
		);

		$this->add_control(
			'pagination_page_limit',
			[
				'label'       => __( 'Page Limit', 'ae-pro' ),
				'condition'   => [
					'show_pagination' => 'yes',
				],
				'description' => __( 'Leave blank to show all pages', 'ae-pro' ),
			]
		);

		$this->add_control(
			'disable_scroll_on_ajax_load',
			[
				'label'        => __( 'Disable Scroll to Top on Load', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'show_pagination' => 'yes',
					'disable_ajax'    => '',
				],
			]
		);

		$this->add_control(
			'pagination_scroll_top_offset',
			[
				'label'     => __( 'Scroll To Top Offset', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 0,
				],
				'range'     => [
					'px' => [
						'min'  => 0,
						'max'  => 1000,
						'step' => 1,
					],
				],
				'condition' => [
					'disable_scroll_on_ajax_load' => '',
					'disable_ajax'                => '',
				],
			]
		);

		$this->add_responsive_control(
			'pagination_align',
			[
				'label'     => __( 'Alignment', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'fa fa-align-right',
					],
				],
				'default'   => '',
				'condition' => [
					'show_pagination' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-pagination-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function carousel_controls() {

		$this->start_controls_section(
			'carousel_control',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'image_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		// Todo:: different effects management
		$this->add_control(
			'effect',
			[
				'label'     => __( 'Effects', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'fade'      => __( 'Fade', 'ae-pro' ),
					'slide'     => __( 'Slide', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
					'flip'      => __( 'Flip', 'ae-pro' ),
				],
				'default'   => 'slide',
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_responsive_control(
			'slide_per_view',
			[
				'label'          => __( 'Slides Per View', 'ae-pro' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 100,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'conditions'     => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'slide',
						],
						[
							'name'     => 'effect',
							'operator' => '==',
							'value'    => 'coverflow',
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'slides_per_group',
			[
				'label'          => __( 'Slides Per Group', 'ae-pro' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 100,
				'default'        => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
			]
		);

		$this->add_control(
			'carousel_settings_heading',
			[
				'label'     => __( 'Setting', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'speed',
			[
				'label'       => __( 'Speed', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 5000,
				],
				'description' => __( 'Duration of transition between slides (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 1000,
						'max'  => 10000,
						'step' => 1000,
					],
				],
				'condition'   => [
					'layout_mode' => 'carousel',
				],

			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'duration',
			[
				'label'       => __( 'Duration', 'ae-pro' ),
				'type'        => Controls_Manager::SLIDER,
				'default'     => [
					'size' => 900,
				],
				'description' => __( 'Delay between transitions (in ms)', 'ae-pro' ),
				'range'       => [
					'px' => [
						'min'  => 300,
						'max'  => 3000,
						'step' => 300,
					],
				],
				'condition'   => [
					'autoplay' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'          => __( 'Space Between Slides', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'size' => 15,
				],
				'tablet_default' => [
					'size' => 10,
				],
				'mobile_default' => [
					'size' => 5,
				],
				'range'          => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 5,
					],
				],
				'condition'      => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'loop',
			[
				'label'        => __( 'Loop', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'auto_height',
			[
				'label'        => __( 'Auto Height', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'pause_on_hover',
			[
				'label'        => __( 'Pause on Hover', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'pagination_heading',
			[
				'label'     => __( 'Pagination', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'ptype',
			[
				'label'   => __( ' Pagination Type', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' =>
					[
						''         => __( 'None', 'ae-pro' ),
						'bullets'  => __( 'Bullets', 'ae-pro' ),
						'fraction' => __( 'Fraction', 'ae-pro' ),
						'progress' => __( 'Progress', 'ae-pro' ),
					],
				'default' => 'bullets',
			]
		);

		$this->add_control(
			'clickable',
			[
				'label'     => __( 'Clickable', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'label_on'  => __( 'Yes', 'ae-pro' ),
				'label_off' => __( 'No', 'ae-pro' ),
				'condition' => [
					'ptype' => 'bullets',
				],
			]
		);

		$this->add_control(
			'keyboard',
			[
				'label'        => __( 'Keyboard Control', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'scrollbar',
			[
				'label'        => __( 'Scroll bar', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'navigation_arrow_heading',
			[
				'label'     => __( 'Prev/Next Navigaton', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',

			]
		);

		$this->add_control(
			'navigation_button',
			[
				'label'        => __( 'Enable', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'arrows_layout',
			[
				'label'     => __( 'Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'inside',
				'options'   => [
					'inside'  => __( 'Inside', 'ae-pro' ),
					'outside' => __( 'Outside', 'ae-pro' ),
				],
				'condition' => [
					'navigation_button' => 'yes',
				],

			]
		);

		$this->add_control(
			'arrow_icon_left',
			[
				'label'            => __( 'Icon Prev', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-left',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'arrow_icon_right',
			[
				'label'            => __( 'Icon Next', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-angle-right',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'navigation_button' => 'yes',
				],
			]
		);

		$this->end_controls_section();
	}

	public function sale_badge_controls() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$this->start_controls_section(
			'sale_badge_layout',
			[
				'label'     => __( 'Sale Badge', 'ae-pro' ),
				'condition' => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_type',
			[
				'label'   => __( ' Type ', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' =>
					[
						'ribbon' => __( 'Ribbon', 'ae-pro' ),
						'badge'  => __( 'Badge', 'ae-pro' ),
					],
				'default' => 'ribbon',
			]
		);

		$this->add_control(
			'sale_badge_title',
			[
				'label'       => __( 'Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Sale Badge Title', 'ae-pro' ),
				'default'     => __( 'Sale!', 'ae-pro' ),
			]
		);

		$this->add_control(
			'sale_badge_horizontal_position',
			[
				'label'        => __( 'Horizontal Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],

				],
				'prefix_class' => 'badge-h-',
				'default'      => 'left',
			]
		);

		$this->add_control(
			'sale_badge_vertical_position',
			[
				'label'        => __( 'Vertical Position', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'label_block'  => true,
				'options'      => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'Bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],

				],
				'prefix_class' => 'badge-v-',
				'default'      => 'top',
				'condition'    => [
					'sale_badge_type' => 'badge',
				],
			]
		);

		$this->end_controls_section();
	}

	public function sale_badge_styles() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		$this->start_controls_section(
			'sale_badge_style',
			[
				'label'     => __( 'Sale Badge', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'sale_badge_switcher' => 'yes',
				],
			]
		);

		$this->add_control(
			'sale_badge_bg_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} span.onsale' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$ribbon_distance_transform = is_rtl() ? 'translateY(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)' : 'translateY(-50%) translateX(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)';

		$this->add_responsive_control(
			'sale_badge_distance',
			[
				'label'     => __( 'Distance', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} span.onsale' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .badge-type-ribbon .ae-sale-badge-wrapper span.onsale' => 'transform: ' . $ribbon_distance_transform,
				],
				'condition' => [
					'sale_badge_type' => 'ribbon',
				],
			]
		);

		$this->add_responsive_control(
			'sale_badge_size',
			[
				'label'          => __( 'Size', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'size_units'     => [ 'em', 'px' ],
				'default'        => [
					'unit' => 'em',
				],
				'tablet_default' => [
					'unit' => 'em',
				],
				'mobile_default' => [
					'unit' => 'em',
				],
				'range'          => [
					'em' => [
						'min'  => 0,
						'max'  => 25,
						'step' => 0.1,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} span.onsale' => 'min-height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}};',
				],
				'condition'      => [
					'sale_badge_type' => 'badge',
				],
			]
		);

		$this->add_control(
			'sale_badge_text_color',
			[
				'label'     => __( 'Text Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .ae-sale-badge-wrapper span.onsale' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'sale_badge_typography',
				'selector' => '{{WRAPPER}} span.onsale',
				'global'   => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
			]
		);

		Aepro::$_helper->box_model_controls(
			$this,
			[
				'name'          => 'badge_style',
				'label'         => __( 'Badge', 'ae-pro' ),
				'border'        => true,
				'border-radius' => true,
				'margin'        => true,
				'padding'       => true,
				'box-shadow'    => true,
				'selector'      => '{{WRAPPER}} .ae-sale-badge-wrapper  span.onsale',
			]
		);

		$this->end_controls_section();
	}

	public function loading_overlay() {
		$this->start_controls_section(
			'overlay_section',
			[
				'label'     => __( 'Overlay', 'ae-pro' ),
				'condition' => [
					'enable_overlay' => 'yes',
				],
			]
		);

		$this->add_control(
			'overlay_icon',
			[
				'label'            => __( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fa fa-sync',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'overlay_text',
			[
				'label'   => __( 'Text', 'ae-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Loading...',
			]
		);

		$this->end_controls_section();
	}

	public function get_posts( $settings ) {
		$post_items = null;
		$query_args = [];
		$post_type  = $settings['ae_post_type'];

		$ae_post_ids = $settings['ae_post_ids'];

		if ( isset( $_POST['pid'] ) ) {
			$current_post_id = $_POST['pid'];
		} else {
			$current_post_id = get_the_ID();
		}

		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : '';

		$paged = $this->get_current_page_num();

		switch ( $post_type ) {
			case 'current_loop':
				if ( ! Plugin::instance()->editor->is_edit_mode() ) {
					global $wp_query;
					$main_query = clone $wp_query;
					$post_items = $main_query;
				} else {
					$render_mode = get_post_meta( $current_post_id, 'ae_render_mode', true );
					switch ( $render_mode ) {
						case 'author_template':
							$author_data             = Aepro::$_helper->get_preview_author_data();
							$query_args['author']    = $author_data['prev_author_id'];
							$query_args['post_type'] = 'any';
							break;

						case 'archive_template':
							$term_data               = Aepro::$_helper->get_preview_term_data();
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
							$query_args['post_type'] = 'post';
							break;

						default:
							$query_args['post_type'] = 'post';
					}
				}
				break;

			case 'ae_by_id':
				$query_args['post_type'] = 'any';
				$query_args['post__in']  = $ae_post_ids;
				$query_args['orderby']   = $settings['orderby'];
				$query_args['order']     = $settings['order'];

				if ( empty( $query_args['post__in'] ) ) {
					// If no selection - return an empty query
					$query_args['post__in'] = [ -1 ];
				}
				break;

			case 'related':
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
				break;

			case 'relation':
				$field = $settings['acf_relation_field'];

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

				break;

			case 'post_object':
				$field = $settings['acf_post_field'];

				if ( isset( $_POST['fetch_mode'] ) ) {
					$cpost_id = $_POST['cpid'];
					$cpost    = get_post( $cpost_id );
				} else {
					$cpost    = Aepro::$_helper->get_demo_post_data();
					$cpost_id = $cpost->ID;
				}

				$post_items = get_field( $field, $cpost_id );

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

				break;

			default:
				$query_args = [
					'orderby'             => $settings['orderby'],
					'order'               => $settings['order'],
					'ignore_sticky_posts' => 1,
					'post_status'         => 'publish', // Hide drafts/private posts for admins
				];

				if ( $settings['orderby'] === 'meta_value' || $settings['orderby'] === 'meta_value_num' ) {
					$query_args['meta_key'] = $settings['orderby_metakey_name'];
				}

				$query_args['post_type']      = $post_type;
				$query_args['offset']         = $settings['offset'];
				$query_args['posts_per_page'] = $settings['posts_per_page'];
				$query_args['tax_query']      = [];

				if ( is_singular() && ( $settings['current_post'] === 'yes' ) ) {
					$query_args['post__not_in'] = [ $current_post_id ];
				}
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				foreach ( $taxonomies as $object ) {

					if ( isset( $settings['filter_taxonomy'] ) && $object->name === $settings['filter_taxonomy'] && isset( $_POST['term_id'] ) && $_POST['term_id'] > 0 ) {
						$query_args['tax_query'][] = [
							'taxonomy' => $settings['filter_taxonomy'],
							'field'    => 'term_id',
							'terms'    => $_POST['term_id'],
						];
					} else {
						$setting_key = $object->name . '_ae_ids';
						if ( isset( $settings['show_all'] ) ) {
							if ( $settings['show_all'] === 'yes' ) {
								if ( ! empty( $settings[ $setting_key ] ) ) {
									$query_args['tax_query'][] = [
										'taxonomy' => $object->name,
										'field'    => 'term_id',
										'terms'    => $settings[ $setting_key ],
									];
								}
							} else {
								if ( isset( $_POST['term_id'] ) ) {
									if ( ! empty( $settings[ $setting_key ] ) ) {
										$query_args['tax_query'][] = [
											'taxonomy' => $object->name,
											'field'    => 'term_id',
											'terms'    => $settings[ $setting_key ],
										];
									}
								} else {
									if ( isset( $settings[ $settings['filter_taxonomy'] . '_ae_ids' ] ) ) {
										$filter_terms              = get_terms(
											$settings['filter_taxonomy'],
											[
												'hide_empty' => true,
												'term_taxonomy_id' => $settings[ $settings['filter_taxonomy'] . '_ae_ids' ],
											]
										);
										$query_args['tax_query'][] = [
											'taxonomy' => $settings['filter_taxonomy'],
											'field'    => 'term_id',
											'terms'    => $filter_terms[0]->term_id,
										];
									}
								}
							}
						} else {
							if ( ! empty( $settings[ $setting_key ] ) ) {
								$query_args['tax_query'][] = [
									'taxonomy' => $object->name,
									'field'    => 'term_id',
									'terms'    => $settings[ $setting_key ],
								];
							}
						}
					}
				}

				if ( isset( $query_args['tax_query'] ) && count( $query_args['tax_query'] ) > 1 ) {
					$query_args['tax_query']['relation'] = $settings['tax_relation'];
				}

				if ( isset( $_POST['page_num'] ) || $paged > 1 ) {
					$query_args['offset'] = $this->calculate_offset( $settings, $query_args, $paged );
				}

				if ( is_array( $settings['author_ae_ids'] ) && count( $settings['author_ae_ids'] ) ) {
					$query_args['author'] = implode( ',', $settings['author_ae_ids'] );
				}
		}

		/**
		 * Filter - Add Custom Source Query
		 */
		$query_args = apply_filters( 'aepro/post-blocks/custom-source-query', $query_args, $settings );

		if ( $post_type === 'current_loop' && ! Plugin::instance()->editor->is_edit_mode() ) {
			$query_args = null;
		} else {
			if ( isset( $query_args ) ) {
				if ( ! empty( $settings['query_filter'] ) ) {
					$query_args = apply_filters( $settings['query_filter'], $query_args );
				}
				$post_items = new WP_Query( $query_args );
			}
		}

		return [ $post_items, $query_args ];
	}

	public function carousel_navigation( $settings ) {
	}

	public function pagination_markup( $settings, $post_items ) {

		global $wp;

		if ( $settings['layout_mode'] === 'carousel' ) {
			return;
		}

		if ( $settings['show_infinite_scroll'] === 'yes' ) {
			$settings['show_pagination'] = 'yes';
		}

		if ( $settings['show_pagination'] !== 'yes' ) {
			return;
		}

		$page_limit = $post_items->max_num_pages;

		if ( isset( $settings['pagination_page_limit'] ) && $settings['pagination_page_limit'] !== '' ) {
			$page_limit = min( $settings['pagination_page_limit'], $page_limit );
		}

		if ( 2 > $page_limit ) {
			return;
		}

		$this->add_render_attribute( 'pagination-wrapper', 'class', 'ae-pagination-wrapper' );

		?>
		<div <?php echo $this->get_render_attribute_string( 'pagination-wrapper' ); ?>>
			<?php
			$current = 1;
			if ( isset( $_POST['page_num'] ) ) {
				$current = $_POST['page_num'];
			} elseif ( is_paged() ) {
				$current = ( get_query_var( 'page' ) ) ? get_query_var( 'page' ) : 1;
			}

			if ( isset( $_POST['curr_url'] ) ) {
				$base_url = base64_decode( $_POST['curr_url'] );
			} else {
				$base_url = Aepro::$_helper->get_current_url_non_paged();
			}

			$paginate_args = [
				'base'    => $base_url . '%_%',
				'total'   => $page_limit,
				'current' => $current,
			];

			if ( $settings['ae_post_type'] === 'current_loop' ) {
				unset( $paginate_args['base'] );
				$current = get_query_var( 'paged' );
				if ( $current === 0 ) {
					$paginate_args['current'] = 1;
				} else {
					$paginate_args['current'] = $current;
				}
			}

			if ( $settings['show_prev_next'] === 'yes' || $settings['show_infinite_scroll'] === 'yes' ) {
				$paginate_args['prev_next'] = true;
				$paginate_args['prev_text'] = $settings['prev_text'];
				$paginate_args['next_text'] = $settings['next_text'];
			} else {
				$paginate_args['prev_next'] = false;
			}

			if ( ! isset( $settings['disable_ajax'] ) || $settings['disable_ajax'] === 'yes' ) {

				unset( $paginate_args['base'] );
				$paginate_args['current'] = $this->get_current_page_num();
				echo paginate_links( $paginate_args );

			} else {

				echo Aepro::$_helper->paginate_links( $paginate_args );
			}

			?>
		</div>
		<?php
	}

	public function ias_markup( $settings ) {
	}

	public function calculate_offset( $settings, $query_args, $paged ) {

		if ( $settings['show_pagination'] === 'no' ) {
			return 0;
		}

		if ( $settings['disable_ajax'] === 'yes' && $paged > 1 ) {
			$offset = ( $query_args['posts_per_page'] * ( $paged - 1 ) );

		} else {
			$offset = $query_args['posts_per_page'] * ( $this->get_current_page_num() - 1 );
		}

		if ( is_numeric( $query_args['offset'] ) ) {
			$offset += $query_args['offset'];
		}

		return $offset;
	}

	public function smart_grid_layouts() {

		$smart_grid = [

			'layout1' => [
				'title'             => __( 'Layout 1', 'ae-pro' ),
				'icon'              => 'aep aep-sg-1',
				'count'             => 3,
				'alternate_layouts' => [ 1 ],
			],

			'layout2' => [
				'title'             => __( 'Layout 2', 'ae-pro' ),
				'icon'              => 'aep aep-sg-2',
				'alternate_layouts' => [ 2 ],
			],

			'layout3' => [
				'label'             => __( 'Layout 3', 'ae-pro' ),
				'icon'              => 'aep aep-sg-3',
				'alternate_layouts' => [ 1 ],
			],

			'layout4' => [
				'label'             => __( 'Layout 4', 'ae-pro' ),
				'icon'              => 'aep aep-sg-4',
				'alternate_layouts' => [ 1 ],
			],
		];

		return $smart_grid;
	}

	public function get_template( $seq, $settings, $alt_layout ) {

		$template = $settings['template'];
		if ( is_paged() && $settings['show_infinite_scroll'] === 'yes' && $settings['layout_mode'] === 'smart-grid' ) {
			return $template;
		}

		switch ( $settings['layout_mode'] ) {

			case 'smart-grid':
				if ( in_array( $seq, $alt_layout, true ) && $settings['alt_template'] !== '' ) {
					$template = $settings['alt_template'];
				}
				break;

			case 'checker-board':
				if ( $settings['columns'] % 2 !== 0 ) {
					// col count is odd - just play even-odd
					if ( $seq % 2 === 0 ) {
						$template = $settings['alt_template'];
					}
				} else {
					// more complex
					$row = ceil( $seq / $settings['columns'] );
					if ( $row % 2 === 0 ) {
						if ( $seq % 2 === 0 ) {
							$template = $settings['alt_template'];
						}
					} else {
						if ( $seq % 2 == 1 ) {
							$template = $settings['alt_template'];
						}
					}
				}
		}

		return $template;
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

	public function item_html( $settings, $template, $with_css ) {

		if ( ! empty( $settings['query_filter'] ) ) {
			$new_template = apply_filters( "aepro/post_blocks/layout/{$settings['query_filter']}", $template );

			if ( $new_template !== $template ) {
				$template = $new_template;
				$with_css = true;
			}
		}
		?>

		<article <?php echo $this->get_render_attribute_string( 'post-list-item' ); ?>>
			<div <?php echo $this->get_render_attribute_string( 'article-inner' ); ?>>
				<div class="ae_data elementor elementor-<?php echo $template; ?>">
					<?php

					if ( class_exists( 'WooCommerce' ) ) {
						if ( $settings['sale_badge_switcher'] === 'yes' ) {
							if ( Aepro::$_helper->ae_is_product_on_sale( get_the_ID() ) ) {
								?>
								<div class="ae-sale-badge-wrapper"><span
											class="onsale"><?php echo $settings['sale_badge_title']; ?></span>
								</div>
								<?php
							}
						}
					}

					echo Plugin::instance()->frontend->get_builder_content( $template, $with_css );

					?>
				</div>
			</div>
		</article>

		<?php
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['speed']     = $settings['speed']['size'];
		$swiper_data['direction'] = $settings['direction'];

		if ( $settings['autoplay'] === 'yes' ) {
			$swiper_data['autoplay']['duration'] = $settings['duration']['size'];
		} else {
			$swiper_data['autoplay'] = false;
		}

		if ( $settings['pause_on_hover'] === 'yes' ) {
			$swiper_data['pause_on_hover'] = $settings['pause_on_hover'];
		}

		$swiper_data['effect'] = $settings['effect'];

		$swiper_data['loop']       = $settings['loop'];
		$swiper_data['autoHeight'] = ( $settings['auto_height'] === 'yes' ) ? true : false;

		if ( $settings['effect'] === 'fade' || $settings['effect'] === 'flip' ) {
			$swiper_data['spaceBetween']['default'] = 0;
			$swiper_data['spaceBetween']['tablet']  = 0;
			$swiper_data['spaceBetween']['mobile']  = 0;

			$swiper_data['slidesPerView']['default'] = 1;
			$swiper_data['slidesPerView']['tablet']  = 1;
			$swiper_data['slidesPerView']['mobile']  = 1;

		} else {
			$swiper_data['spaceBetween']['default'] = $settings['space_mobile']['size'] !== '' ? $settings['space_mobile']['size'] : 5;
			$swiper_data['spaceBetween']['tablet']  = $settings['space']['size'] !== '' ? $settings['space']['size'] : 15;
			$swiper_data['spaceBetween']['mobile']  = $settings['space_tablet']['size'] !== '' ? $settings['space_tablet']['size'] : 10;

			$swiper_data['slidesPerView']['default'] = $settings['slide_per_view_mobile'] !== '' ? $settings['slide_per_view_mobile'] : 1;
			$swiper_data['slidesPerView']['tablet']  = $settings['slide_per_view'] !== '' ? $settings['slide_per_view'] : 3;
			$swiper_data['slidesPerView']['mobile']  = $settings['slide_per_view_tablet'] !== '' ? $settings['slide_per_view_tablet'] : 2;

		}

		$swiper_data['slidesPerGroup']['default'] = $settings['slides_per_group_mobile'] !== '' ? $settings['slides_per_group_mobile'] : 1;
		$swiper_data['slidesPerGroup']['tablet']  = $settings['slides_per_group'] !== '' ? $settings['slides_per_group'] : 1;
		$swiper_data['slidesPerGroup']['mobile']  = $settings['slides_per_group_tablet'] !== '' ? $settings['slides_per_group_tablet'] : 1;

		$swiper_data['ptype'] = $settings['ptype'];
		if ( $settings['ptype'] !== '' ) {
			if ( $settings['ptype'] === 'progress' ) {
				$swiper_data['ptype'] = 'progressbar';
			}
		}
		$swiper_data['clickable']  = isset( $settings['clickable'] ) ? $settings['clickable'] : false;
		$swiper_data['navigation'] = $settings['navigation_button'];
		$swiper_data['scrollbar']  = $settings['scrollbar'];

		return $swiper_data;
	}
}
