<?php
namespace Aepro\Modules\TaxonomyBlocks\Skins;

use Aepro\Aepro;
use Elementor\Controls_Manager;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Widget_Base;
use Elementor\Plugin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Icons_Manager;
use Aepro\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base {

	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {

		add_action( 'elementor/element/ae-taxonomy-blocks/section_layout/after_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_style_controls() {

		$this->register_style_block_controls();

		$this->register_style_title_controls();

		$this->register_style_overlay_controls();

		$this->register_style_image_controls();
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;
	}


	public function layout_controls() {

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
				'selectors'       => [
					'{{WRAPPER}} .ae-term-list-wrapper' => 'grid-template-columns:repeat({{VALUE}}, 1fr);',
				],
				'render_type'     => 'ui',
				'condition'       => [
					'layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'block_row_gap',
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
					'{{WRAPPER}}  .ae-term-list-wrapper' => 'grid-row-gap:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'block_col_gap',
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
					'{{WRAPPER}}  .ae-term-list-wrapper' => 'grid-column-gap:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout' => 'grid',
				],
			]
		);

		$this->add_responsive_control(
			'block_min_height',
			[
				'label'          => __( 'Minimum Height', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'size' => 400,
				],
				'tablet_default' => [
					'size' => '300',
				],
				'mobile_default' => [
					'size' => '200',
				],
				'range'          => [
					'px' => [
						'min'  => 1,
						'max'  => 1440,
						'step' => 1,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae-term-list-item' => 'min-height: {{SIZE}}px;',
				],
			]
		);
	}

	public function list_layout_controls() {
		$this->add_control(
			'list_layout',
			[
				'label'          => __( 'Layout', 'ae-pro' ),
				'type'           => Controls_Manager::CHOOSE,
				'default'        => 'traditional',
				'options'        => [
					'traditional' => [
						'title' => __( 'Default', 'ae-pro' ),
						'icon'  => 'eicon-editor-list-ul',
					],
					'inline' => [
						'title' => __( 'Inline', 'ae-pro' ),
						'icon'  => 'eicon-ellipsis-h',
					],
				],
				'render_type'    => 'template',
				'label_block'    => false,
				'style_transfer' => true,
				'prefix_class'   => 'ae-term-list--layout-',
			]
		);
	}

	public function icon_controls() {

		$this->add_control(
			'icon_heading',
			[
				'label'     => __( 'Icon', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_icon',
			[
				'label'        => __( 'Show Icon', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'icon',
			[
				'label'       => __( 'Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => 'fa fa-arrow-right',
				'condition'   => [
					$this->get_control_id( 'show_icon' ) => 'yes',
				],
			]
		);
	}

	public function title_controls() {

		$this->add_control(
			'title_heading',
			[
				'label'     => __( 'Title', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_title',
			[
				'label'        => __( 'Show Title', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'html_tag',
			[
				'label'     => __( 'HTML Tag', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				],
				'default'   => 'h3',
				'condition' => [
					$this->get_control_id( 'show_title' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'strip_title',
			[
				'label'        => __( 'Strip Title', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					$this->get_control_id( 'show_title' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'strip_mode',
			[
				'label'     => __( 'Strip Mode', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'word'   => __( 'Word', 'ae-pro' ),
					'letter' => __( 'Letter', 'ae-pro' ),
				],
				'default'   => 'word',
				'condition' => [
					$this->get_control_id( 'strip_title' ) => 'yes',
					$this->get_control_id( 'show_title' )  => 'yes',
				],
			]
		);

		$this->add_control(
			'strip_size',
			[
				'label'       => __( 'Strip Size', 'ae-pro' ),
				'type'        => Controls_Manager::NUMBER,
				'placeholder' => __( 'Strip Size', 'ae-pro' ),
				'default'     => __( '5', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'strip_title' ) => 'yes',
					$this->get_control_id( 'show_title' )  => 'yes',
				],
				'description' => __( 'Number of words to show.', 'ae-pro' ),
			]
		);

		$this->add_control(
			'strip_append',
			[
				'label'       => __( 'Append Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Append Text', 'ae-pro' ),
				'default'     => __( '...', 'ae-pro' ),
				'condition'   => [
					$this->get_control_id( 'strip_title' ) => 'yes',
					$this->get_control_id( 'show_title' )  => 'yes',
				],
				'description' => __( 'What to append if Title needs to be trimmed.', 'ae-pro' ),
			]
		);

		$this->add_control(
			'enable_title_link',
			[
				'label'     => __( 'Enable Link', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => [
					$this->get_control_id( 'show_title' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'title_new_tab',
			[
				'label'     => __( 'Open in new tab', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => [
					$this->get_control_id( 'enable_title_link' ) => 'yes',
					$this->get_control_id( 'show_title' ) => 'yes',
				],
			]
		);
	}

	public function overlay_controls() {

		$this->add_control(
			'overlay_title_heading',
			[
				'label'     => __( 'Overlay Title', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'overlay_enable_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'prefix_class' => 'ae-term-overlay-link-',
				'render_type'  => 'tempalte',
			]
		);

		$this->add_control(
			'overlay_link_new_tab',
			[
				'label'     => __( 'Open in new tab', 'ae-pro' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'no',
				'condition' => [
					$this->get_control_id( 'overlay_enable_link' ) => 'yes',
				],
			]
		);
	}

	public function image_controls() {

		$this->add_control(
			'image_heading',
			[
				'label'     => __( 'Image', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_image',
			[
				'label'        => __( 'Show Image', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'image_type',
			[
				'label'        => __( 'Image Type', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'default'      => 'Default',
					'custom_field' => 'Custom Field',
				],
				'default'      => 'default',
				'prefix_class' => 'ae-taxonomy-bg-',
				'condition'    => [
					$this->get_control_id( 'show_image' ) => 'yes',
				],
				'render_type'  => 'template',

			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'      => 'taxonomy_background',
				'label'     => __( 'Item Image', 'ae-pro' ),
				'types'     => [ 'none', 'classic', 'gradient' ],
				'selector'  => '{{WRAPPER}} .ae-term-skin-card .ae-term-list-item',
				'default'   => '#fff',
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					$this->get_control_id( 'image_type' ) => 'custom-field',
				],
			]
		);

		$this->add_control(
			'taxonomy_image',
			[
				'label'     => __( 'Term Block Image', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [
					'url' => Aepro::$_helper->get_ae_placeholder_image_src(),
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					$this->get_control_id( 'image_type' ) => 'default',
				],
			]
		);

		$this->add_control(
			'ae_taxonomy_bg_cf_field_key',
			[
				'label'       => __( 'Custom Field key', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field Key', 'ae-pro' ),
				'default'     => '',
				'condition'   => [
					$this->get_control_id( 'show_image' ) => 'yes',
					$this->get_control_id( 'image_type' ) => 'custom_field',
				],
			]
		);

		$this->add_control(
			'cf_taxonomy_image_fallback',
			[
				'label'     => __( 'Fallback Image', 'ae-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => [
					'url' => Aepro::$_helper->get_ae_placeholder_image_src(),
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					$this->get_control_id( 'image_type' ) => 'custom_field',
				],
			]
		);

		$this->add_control(
			'ae_taxonomy_image_size',
			[
				'label'        => __( 'Image Size', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => Aepro::$_helper->ae_get_intermediate_image_sizes(),
				'default'      => 'large',
				'prefix_class' => 'ae-taxonomy-img-size-',
				'condition'    => [
					$this->get_control_id( 'show_image' ) => 'yes',
				],
				'render_type'  => 'template',
			]
		);

		$this->add_control(
			'ae_taxonomy_bg_size',
			[
				'label'     => __( 'Background Size', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'auto'    => __( 'Auto', 'ae-pro' ),
					'cover'   => __( 'Cover', 'ae-pro' ),
					'contain' => __( 'Contain', 'ae-pro' ),
				],
				'default'   => 'cover',
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item' => 'background-size: {{VALUE}};',
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'classic',
				],
			]
		);

		$this->add_control(
			'ae_taxonomy_bg_position',
			[
				'label'     => __( 'Position', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''              => __( 'Default', 'ae-pro' ),
					'top left'      => __( 'Top Left', 'ae-pro' ),
					'top center'    => __( 'Top Center', 'ae-pro' ),
					'top right'     => __( 'Top Right', 'ae-pro' ),
					'center left'   => __( 'Center Left', 'ae-pro' ),
					'center center' => __( 'Center Center', 'ae-pro' ),
					'center right'  => __( 'Center Right', 'ae-pro' ),
					'bottom left'   => __( 'Bottom Left', 'ae-pro' ),
					'bottom center' => __( 'Bottom Center', 'ae-pro' ),
					'bottom right'  => __( 'Bottom Right', 'ae-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item' => 'background-position: {{VALUE}};',
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'classic',
				],
			]
		);

		$this->add_control(
			'ae_taxonomy_bg_attachment',
			[
				'label'     => __( 'Attachment', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''       => __( 'Default', 'ae-pro' ),
					'scroll' => __( 'Scroll', 'ae-pro' ),
					'fixed'  => __( 'Fixed', 'ae-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item' => 'background-attachment: {{VALUE}};',
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'classic',
				],
			]
		);

		$this->add_control(
			'ae_taxonomy_bg_repeat',
			[
				'label'     => __( 'Repeat', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''          => __( 'Default', 'ae-pro' ),
					'no-repeat' => __( 'No-repeat', 'ae-pro' ),
					'repeat'    => __( 'Repeat', 'ae-pro' ),
					'repeat-x'  => __( 'Repeat-x', 'ae-pro' ),
					'repeat-y'  => __( 'Repeat-y', 'ae-pro' ),
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item' => 'background-repeat: {{VALUE}};',
				],
				'condition' => [
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'classic',
				],
			]
		);

		$this->add_control(
			'enable_ratio',
			[
				'label'        => __( 'Enable Image Ratio', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'prefix_class' => 'ae-term-image-ratio-',
				'default'      => 'yes',
				'condition'    => [
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'card',
				],
				'render_type'  => 'template',
			]
		);

		$this->add_responsive_control(
			'img_ratio',
			[
				'label'          => __( 'Image Ratio', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'size' => 0.66,
				],
				'tablet_default' => [
					'size' => '',
				],
				'mobile_default' => [
					'size' => 0.5,
				],
				'range'          => [
					'px' => [
						'min'  => 0.1,
						'max'  => 2,
						'step' => 0.01,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae-term-skin-classic.ae-term-list-wrapper .ae-post-image' => 'padding-bottom: calc( {{SIZE}} * 100% );',
				],
				'condition'      => [
					$this->get_control_id( 'enable_ratio' ) => 'yes',
					$this->get_control_id( 'show_image' ) => 'yes',
					'_skin!'                              => 'card',
				],
			]
		);
	}

	public function count_controls() {
		$this->add_control(
			'count_heading',
			[
				'label'     => __( 'Count', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_count',
			[
				'label'        => __( 'Show Count', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
	}

	public function register_carousel_controls() {

		$this->start_controls_section(
			'section_carousel',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [
					$this->get_control_id( 'layout' ) => 'carousel',
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
				'label'   => __( 'Effects', 'ae-pro' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'fade'      => __( 'Fade', 'ae-pro' ),
					'slide'     => __( 'Slide', 'ae-pro' ),
					'coverflow' => __( 'Coverflow', 'ae-pro' ),
					'flip'      => __( 'Flip', 'ae-pro' ),
				],
				'default' => 'slide',
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
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label'        => __( 'Autoplay', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'On', 'ae-pro' ),
				'label_off'    => __( 'Off', 'ae-pro' ),
				'return_value' => 'yes',
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

	public function register_style_block_controls() {
		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Layout', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'item_background',
			[
				'label'     => 'Background Color',
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-term-list-item',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-term-list-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => __( 'Padding', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-term-list-item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-top.caption-block-align-right .ae-term-title-wrapper' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-top.caption-block-align-left .ae-term-title-wrapper' => 'top: {{TOP}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-bottom.caption-block-align-right .ae-term-title-wrapper' => 'bottom: {{BOTTOM}}{{UNIT}}; right: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-bottom.caption-block-align-left .ae-term-title-wrapper' => 'bottom: {{BOTTOM}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-middle.caption-block-align-right .ae-term-title-wrapper' => 'right: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-middle.caption-block-align-left .ae-term-title-wrapper' => 'left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-top.caption-block-align-center .ae-term-title-wrapper' => 'top: {{TOP}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-bottom.caption-block-align-center .ae-term-title-wrapper' => 'bottom: {{BOTTOM}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-top.caption-block-align-justify .ae-term-title-wrapper' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-bottom.caption-block-align-justify .ae-term-title-wrapper' => 'bottom: {{BOTTOM}}{{UNIT}}; right: {{RIGHT}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}.caption-block-align-middle.caption-block-align-justify .ae-term-title-wrapper' => 'right: {{RIGHT}}{{UNIT}}; left: {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-term-list-item',
			]
		);

		$this->end_controls_section();
	}

	public function register_style_list_controls() {
		$this->start_controls_section(
			'section_icon_list',
			[
				'label' => __( 'List', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'space_between_list_item',
			[
				'label'     => __( 'Space Between', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'default'   => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors' => [
					'{{WRAPPER}}.ae-term-list--layout-traditional .ae-icon-list-items .ae-icon-list-item:not(:last-child)' => 'margin-bottom: calc({{SIZE}}{{UNIT}}/2); padding-bottom: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.ae-term-list--layout-traditional .ae-icon-list-items .ae-icon-list-item:not(:first-child)' => 'margin-top: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.ae-term-list--layout-traditional .ae-icon-list-items .ae-icon-list-item:after' => 'bottom: calc(-{{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items .ae-icon-list-item' => 'margin-right: calc({{SIZE}}{{UNIT}}/2); margin-left: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items' => 'margin-right: calc(-{{SIZE}}{{UNIT}}/2); margin-left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body.rtl {{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items .ae-icon-list-item:after' => 'left: calc(-{{SIZE}}{{UNIT}}/2)',
					'body:not(.rtl) {{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items .ae-icon-list-item:after' => 'right: calc(-{{SIZE}}{{UNIT}}/2)',
				],
			]
		);

		$this->add_responsive_control(
			'list_align',
			[
				'label'        => __( 'Alignment', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
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
				'prefix_class' => 'ae-icl-align-',
			]
		);

		$this->add_control(
			'divider',
			[
				'label'        => __( 'Divider', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'render_type'  => 'template',
				'prefix_class' => 'ae-sep-divider-',
				'selectors'    => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'content: ""',
				],
				'separator'    => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label'     => __( 'Style', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'solid'  => __( 'Solid', 'ae-pro' ),
					'double' => __( 'Double', 'ae-pro' ),
					'dotted' => __( 'Dotted', 'ae-pro' ),
					'dashed' => __( 'Dashed', 'ae-pro' ),
				],
				'default'   => 'solid',
				'condition' => [
					$this->get_control_id( 'divider' ) => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}.ae-term-list--layout-traditional .ae-icon-list-items .ae-icon-list-item:not(:last-child):after' => 'border-top-style: {{VALUE}}',
					'{{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items .ae-icon-list-item:not(:last-child):after' => 'border-left-style: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label'     => __( 'Weight', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'condition' => [
					$this->get_control_id( 'divider' ) => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}}.ae-term-list--layout-traditional .ae-icon-list-items .ae-icon-list-item:not(:last-child):after' => 'border-top-width: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}}.ae-term-list--layout-inline .ae-icon-list-items .ae-icon-list-item:not(:last-child):after' => 'border-left-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_width',
			[
				'label'     => __( 'Width', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'unit' => '%',
				],
				'condition' => [
					$this->get_control_id( 'divider' ) => 'yes',
					$this->get_control_id( 'list_layout!' ) => 'inline',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_height',
			[
				'label'      => __( 'Height', 'ae-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'default'    => [
					'unit' => '%',
				],
				'range'      => [
					'px' => [
						'min' => 1,
						'max' => 100,
					],
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'condition'  => [
					$this->get_control_id( 'divider' )     => 'yes',
					$this->get_control_id( 'list_layout' ) => 'inline',
				],
				'selectors'  => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ddd',
				'global'    => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'condition' => [
					$this->get_control_id( 'divider' ) => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-item:not(:last-child):after' => 'border-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_style_icon_controls() {
		$this->start_controls_section(
			'section_icon_style',
			[
				'label'     => __( 'Icon', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					$this->get_control_id( 'show_icon' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .term-list-icon i' => 'color: {{VALUE}};',
				],
				'global'    => [
					'default' => Global_Colors::COLOR_PRIMARY,
				],
			]
		);

		$this->add_control(
			'icon_color_hover',
			[
				'label'     => __( 'Hover', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .ae-icon-list-item:hover .term-list-icon i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'     => __( 'Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 14,
				],
				'range'     => [
					'px' => [
						'min' => 6,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .term-list-icon'   => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .term-list-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_style_title_controls() {
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => __( 'Title', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_background_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-term-title-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'           => 'title_typography',
				'label'          => __( 'Typography', 'ae-pro' ),
				'global'         => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'selector'       => '{{WRAPPER}} .ae-element-term-title',
				'fields_options' => [
					'font_family' => [
						'default' => 'Poppins',
					],
					'font_size' => [
						'default' => [
							'unit' => 'px',
							'size' => 18,
						],
					],
					'line_height' => [
						'default' => [
							'unit' => 'px',
							'size' => 18,
						],
					],
				],
			]
		);

		$this->add_responsive_control(
			'title_padding',
			[
				'label'          => __( 'Spacing', 'ae-pro' ),
				'type'           => Controls_Manager::DIMENSIONS,
				'size_units'     => [ 'px', 'em', '%' ],
				'default'        => [
					'top'    => 5,
					'right'  => 5,
					'bottom' => 5,
					'left'   => 5,
					'unit'   => 'px',
				],
				'mobile_default' => [
					'top'    => 5,
					'right'  => 5,
					'bottom' => 5,
					'left'   => 5,
					'unit'   => 'px',
				],
				'tablet_default' => [
					'top'    => 5,
					'right'  => 5,
					'bottom' => 5,
					'left'   => 5,
					'unit'   => 'px',
				],
				'selectors'      => [
					'{{WRAPPER}} .ae-element-term-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'title_style' );

		$this->start_controls_tab( 'title_style_default', [ 'label' => __( 'Default', 'ae-pro' ) ] );

		$this->add_control(
			'title_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-element-term-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'title_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-element-term-title',
			]
		);

		$this->add_control(
			'title_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-element-term-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_box_shadow',
				'label'    => __( 'Title Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-element-term-title',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'title_style_hover', [ 'label' => __( 'Hover', 'ae-pro' ) ] );

		$this->add_control(
			'title_color_hover',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'global'    => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item:hover .ae-element-term-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_border_color_hover',
			[
				'label'     => __( 'Border Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item:hover .ae-element-term-title' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'title_box_shadow_hover',
				'label'    => __( 'Title Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-term-list-item:hover .ae-element-term-title',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'title_align_vertical',
			[
				'label'        => __( 'Vertical Align', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'top' => [
						'title' => __( 'Top', 'ae-pro' ),
						'icon'  => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __( 'Middle', 'ae-pro' ),
						'icon'  => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __( 'bottom', 'ae-pro' ),
						'icon'  => 'eicon-v-align-bottom',
					],
				],
				'default'      => 'middle',
				'prefix_class' => 'caption-block-align-',
			]
		);

		$this->add_control(
			'title_align_horizontal',
			[
				'label'        => __( 'Horizontal Align', 'ae-pro' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
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
					'justify' => [
						'title' => __( 'Justify', 'ae-pro' ),
						'icon'  => 'eicon-h-align-stretch',
					],
				],
				'default'      => 'center',
				'prefix_class' => 'caption-block-align-',
			]
		);

		$this->add_control(
			'title_align',
			[
				'label'     => __( 'Text Align', 'ae-pro' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left' => [
						'title' => __( 'Left', 'ae-pro' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'ae-pro' ),
						'icon'  => 'eicon-h-align-center',
					],
					'Right' => [
						'title' => __( 'Right', 'ae-pro' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-title-wrapper' => 'text-align: {{VALUE}};',
				],
				'default'   => 'center',
				'condition' => [
					$this->get_control_id( 'title_align_horizontal' ) => 'justify',
				],
			]
		);

		$this->add_control(
			'text_indent',
			[
				'label'     => __( 'Text Indent', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .term-list-text' => is_rtl() ? 'padding-right: {{SIZE}}{{UNIT}};' : 'padding-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	public function register_style_overlay_controls() {
		$this->start_controls_section(
			'section_overlay_style',
			[
				'label' => __( 'Overlay', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'blend_mode',
			[
				'label'     => __( 'Blend Mode', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''            => __( 'Normal', 'ae-pro' ),
					'multiply'    => 'Multiply',
					'screen'      => 'Screen',
					'overlay'     => 'Overlay',
					'darken'      => 'Darken',
					'lighten'     => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'saturation'  => 'Saturation',
					'color'       => 'Color',
					'difference'  => 'Difference',
					'exclusion'   => 'Exclusion',
					'hue'         => 'Hue',
					'luminosity'  => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .term-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->start_controls_tabs( 'overlay_style' );

		$this->start_controls_tab( 'overlay_style_default', [ 'label' => __( 'Default', 'ae-pro' ) ] );

		$this->add_control(
			'overlay_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .term-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'overlay_opacity',
			[
				'label'     => __( 'Opacity (%)', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .term-overlay' => 'opacity: {{SIZE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'overlay_style_hover', [ 'label' => __( 'Hover', 'ae-pro' ) ] );

		$this->add_control(
			'overlay_color_hover',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item:hover .term-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'overlay_opacity_hover',
			[
				'label'     => __( 'Opacity (%)', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item:hover .term-overlay' => 'opacity: {{SIZE}}',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function register_style_image_controls() {
		$this->start_controls_section(
			'section_image_style',
			[
				'label' => __( 'Image', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'image_grid_thumbnails_tabs_style' );

		$this->start_controls_tab(
			'image_grid_thumbnails_style_normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'image_opacity',
			[
				'label'     => __( 'Opacity (%)', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item-inner .ae-post-image img' => 'opacity: {{SIZE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'image_box_shadow',
				'selector'  => '{{WRAPPER}} .ae-term-list-item-inner .ae-post-image',
				'separator' => '',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .ae-term-list-item-inner .ae-post-image',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'image_grid_thumbnails_style_hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_responsive_control(
			'image_opacity_hover',
			[
				'label'     => __( 'Opacity (%)', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 1,
				],
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item-inner .ae-post-image:hover img' => 'opacity: {{SIZE}}',
				],
			]
		);

		$this->add_responsive_control(
			'image_scale_hover',
			[
				'label'     => __( 'Scale', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'min'  => 1,
						'max'  => 2,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-term-list-item-inner .ae-post-image:hover img' => 'transform: scale({{SIZE}});',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'image_box_shadow_hover',
				'selector'  => '{{WRAPPER}} .ae-term-list-item-inner .ae-post-image:hover',
				'separator' => '',
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .ae-term-list-item-inner .ae-post-image:hover',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function render_title( $settings, $parent, $term ) {
		$title_html = '';
		$parent->add_render_attribute( 'term-title-wrapper', 'class', 'ae-term-title-wrapper' );
		$title_html .= '<div ' . $parent->get_render_attribute_string( 'term-title-wrapper' ) . '>';
		if ( $settings['show_title'] === 'yes' ) {
			$parent->add_render_attribute( 'term-title-class', 'class', 'ae-element-term-title' );
			$term_title = $term->name;
			if ( $settings['strip_title'] === 'yes' ) {
				if ( $settings['strip_mode'] === 'word' ) {
					$term_title = wp_trim_words( $term_title, $settings['strip_size'], $settings['strip_append'] );
				} else {
					$term_title = rtrim( substr( $term_title, 0, $settings['strip_size'] ) ) . $settings['strip_append'];
				}
			}
			if ( $settings['enable_title_link'] === 'yes' ) {
				if ( $settings['title_new_tab'] === 'yes' ) {
					$parent->set_render_attribute( 'term-link-class', 'target', '_blank' );
				}
				$title_html .= '<a ' . $parent->get_render_attribute_string( 'term-link-class' ) . ' href="' . esc_url( get_term_link( $term ) ) . '">';
			}
			if ( $settings['show_count'] === 'yes' ) {
				$term_title .= ' (' . $term->count . ')';
			}
			$title_html .= sprintf( '<%1$s itemprop="name" %2$s>%3$s</%1$s>', $settings['html_tag'], $parent->get_render_attribute_string( 'term-title-class' ), $term_title );

			if ( $settings['enable_title_link'] === 'yes' ) {
				$title_html .= '</a>';
			}
		}
		$title_html .= '</div>';
		return $title_html;
	}

	public function render_grid() {

		$settings = $this->parent->get_settings_for_display();

		$terms = Aepro::$_helper->ae_taxonomy_terms( $settings['ae_taxonomy'], $settings );

		$taxonomy = get_taxonomy( $settings['ae_taxonomy'] );

		$this->parent->add_render_attribute( 'term-list-wrapper', 'class', 'ae-term-skin-' . $settings['_skin'] );
		$this->parent->add_render_attribute( 'term-list-wrapper', 'class', 'ae-term-list-wrapper' );
		$this->parent->add_render_attribute( 'term-list-item', 'class', 'ae-term-list-item' );
		$this->parent->add_render_attribute( 'term-list-item-inner', 'class', 'ae-term-list-item-inner' );

		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-pid', get_the_ID() );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-wid', $this->parent->get_id() );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-source', $settings['ae_taxonomy'] );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'class', [ 'ae-taxonomy-widget-wrapper', 'ae-layout-grid' ] );
		?>
		<div <?php echo $this->parent->get_render_attribute_string( 'taxonomy-widget-wrapper' ); ?>>
			<div <?php echo $this->parent->get_render_attribute_string( 'term-list-wrapper' ); ?>>
				<?php
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$count = count( $terms );
					$i     = 0;
					foreach ( $terms as $term ) {
						$i++;

						$this->render_item( $term );
					}
				}
				?>
			</div>
		</div>
		<?php
	}

	public function render_carousel() {

		$settings = $this->parent->get_settings_for_display();

		$pagination_type   = $settings['ptype'];
		$navigation_button = $settings['navigation_button'];
		$scrollbar         = $settings['scrollbar'];

		$arrows_layout         = $settings['arrows_layout'];
		$settings['direction'] = 'horizontal';

		$terms = Aepro::$_helper->ae_taxonomy_terms( $settings['ae_taxonomy'], $settings );

		$taxonomy = get_taxonomy( $settings['ae_taxonomy'] );

		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-pid', get_the_ID() );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-wid', $this->parent->get_id() );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'data-source', $settings['ae_taxonomy'] );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'class', 'ae-taxonomy-widget-wrapper' );

		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'class', 'ae-hpos-' . $settings['arrow_horizontal_position'] );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'class', 'ae-vpos-' . $settings['arrow_vertical_position'] );
		$this->parent->add_render_attribute( 'taxonomy-widget-wrapper', 'class', 'ae-carousel-yes' );
		$swiper_data = $this->get_swiper_data( $settings );
		$this->parent->add_render_attribute( 'outer-wrapper', 'class', [ 'ae-swiper-outer-wrapper' ] );
		$this->parent->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );

		$this->parent->add_render_attribute( 'term-list-wrapper', 'class', 'ae-term-skin-' . $settings['_skin'] );
		$this->parent->add_render_attribute( 'term-list-wrapper', 'class', [ 'ae-term-list-wrapper', 'ae-swiper-wrapper', 'swiper-wrapper' ] );
		$this->parent->add_render_attribute( 'term-list-wrapper', 'class', 'ae-pagination-' . $pagination_type );

		$this->parent->add_render_attribute( 'term-list-item', 'class', [ 'ae-term-list-item', 'ae-swiper-slide', 'swiper-slide' ] );
		$this->parent->add_render_attribute( 'term-list-item-inner', 'class', [ 'ae-term-list-item-inner', 'ae-swiper-slide-wrapper' ] );
		?>

		<div <?php echo $this->parent->get_render_attribute_string( 'taxonomy-widget-wrapper' ); ?> >
			<div <?php echo $this->parent->get_render_attribute_string( 'outer-wrapper' ); ?> >
				<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) { ?>
				<div class="ae-swiper-container swiper-container">
					<div <?php echo $this->parent->get_render_attribute_string( 'term-list-wrapper' ); ?> >

						<?php
							$count = count( $terms );
							$i     = 0;
						foreach ( $terms as $term ) {
							$i++;

							$this->render_item( $term );
						}
						?>

					</div>

					<?php if ( $settings['ptype'] !== '' ) { ?>
						<div class = "ae-swiper-pagination swiper-pagination"></div>
					<?php } ?>

					<?php if ( $navigation_button === 'yes' && $arrows_layout === 'inside' ) { ?>
						<?php
						if ( $settings['arrow_horizontal_position'] !== 'center' ) {
							;
							?>
							<div class="ae-swiper-button-wrapper swiper-button-wrapper">
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
				<?php } ?>

			</div>

			<?php } ?>


		</div>
		<?php
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['direction'] = 'horizontal';

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

			$swiper_data['slidesPerGroup']['default'] = 1;
			$swiper_data['slidesPerGroup']['tablet']  = 1;
			$swiper_data['slidesPerGroup']['mobile']  = 1;

		} else {
			$swiper_data['spaceBetween']['default'] = $settings['space_mobile']['size'] !== '' ? $settings['space_mobile']['size'] : 5;
			$swiper_data['spaceBetween']['tablet']  = $settings['space']['size'] !== '' ? $settings['space']['size'] : 15;
			$swiper_data['spaceBetween']['mobile']  = $settings['space_tablet']['size'] !== '' ? $settings['space_tablet']['size'] : 10;

			$swiper_data['slidesPerView']['default'] = $settings['slide_per_view_mobile'] !== '' ? $settings['slide_per_view_mobile'] : 1;
			$swiper_data['slidesPerView']['tablet']  = $settings['slide_per_view'] !== '' ? $settings['slide_per_view'] : 3;
			$swiper_data['slidesPerView']['mobile']  = $settings['slide_per_view_tablet'] !== '' ? $settings['slide_per_view_tablet'] : 2;

			$swiper_data['slidesPerGroup']['default'] = $settings['slides_per_group_mobile'] !== '' ? $settings['slides_per_group_mobile'] : 1;
			$swiper_data['slidesPerGroup']['tablet']  = $settings['slides_per_group'] !== '' ? $settings['slides_per_group'] : 1;
			$swiper_data['slidesPerGroup']['mobile']  = $settings['slides_per_group_tablet'] !== '' ? $settings['slides_per_group_tablet'] : 1;
		}

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
