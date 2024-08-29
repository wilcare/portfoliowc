<?php

namespace Aepro\Modules\AcfRepeater\Widgets;

use Aepro\Aepro;
use Aepro\Modules\AcfRepeater\Skins;

use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Plugin;
use Elementor\Group_Control_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Aepro\Base\Widget_Base;
use Aepro\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AeAcfRepeater extends Widget_Base {

	public function get_name() {

		return 'ae-acf-repeater';
	}

	public function is_enabled() {

		if ( AE_ACF_PRO ) {
			return true;
		}

		return false;
	}

	public function get_title() {

		return __( 'AE - ACF Repeater', 'ae-pro' );
	}

	public function get_icon() {
		return 'eicon-post-list';
	}

	public function get_categories() {
		return [ 'ae-template-elements' ];
	}

	public function get_script_depends() {

		return [ 'jquery-masonry', 'ae-swiper' ];
	}

	public function get_keywords()
    {
        return[
            'acf',
			'fields',
			'custom fields',
			'meta',
			'repeater',
			'carousel',
			'grid',
			'accordion'
        ];
    }

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_skins() {
		$this->add_skin( new Skins\Skin_Tabs( $this ) );
		$this->add_skin( new Skins\Skin_Accordion( $this ) );
	}
    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls() {

		// Register Controls

		$this->setting_controls();

		$this->layout_controls();

		$this->carousel_controls();

		// Register Styles

		$this->layout_styles();

		$this->carousel_styles();
	}

	protected function render() {
		if ( $this->is_debug_on() ) {
			return;
		}
		$settings = $this->get_settings();
		if ( ! isset( $settings['template'] ) || empty( $settings['template'] )  || get_post_status( $settings['template'] ) !== 'publish' ) {
			if( \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode() || (isset($_GET['preview']) && $_GET['preview'] === 'true') ) {
				printf( '<div class"message"><p class="%1$s">%2$s</p></div>', esc_attr( 'elementor-alert elementor-alert-warning' ), esc_html( __( "Please select a Block Layout first from 'Repeater > Block Layout", 'ae-pro' ) ) );
			}
		} else {
			$settings['template'] = apply_filters( 'wpml_object_id', $settings['template'], 'ae_global_templates' );
			$this->generate_output( $settings );
		}
	}

	public function generate_output( $settings, $with_wrapper = true ) {

		$masonry              = $settings['masonry_grid'];

		if ( $settings['layout_mode'] === 'carousel' ) {
			$pagination_type   = $settings['ptype'];
			$navigation_button = $settings['navigation_button'];
			$scrollbar         = $settings['scrollbar'];

			$arrows_layout         = $settings['arrows_layout'];
			$settings['direction'] = 'horizontal';

			$swiper_data = $this->get_swiper_data( $settings );
			$this->add_render_attribute( 'outer-wrapper', 'class', 'ae-swiper-outer-wrapper' );
			$this->add_render_attribute( 'outer-wrapper', 'data-swiper-settings', wp_json_encode( $swiper_data ) );

			/*-- Carousel */
		}

		$this->add_render_attribute( 'acf-repeater-wrapper', 'class', 'ae-acf-repeater-wrapper' );
		$this->add_render_attribute( 'acf-repeater-inner', 'class', 'ae-acf-repeater-inner' );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'data-pid', get_the_ID() );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'data-wid', $this->get_id() );
		$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-acf-repeater-widget-wrapper' );

		if ( $settings['layout_mode'] === 'carousel' ) {
			$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-carousel-yes' );

			if ( ( ! isset( $arrow_horizontal_position ) || $arrow_horizontal_position !== 'center' ) && $arrows_layout === 'outside' ) {
				$settings['arrow_horizontal_position'] = 'center';
			} else {
				$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-hpos-' . $settings['arrow_horizontal_position'] );
				$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-vpos-' . $settings['arrow_vertical_position'] );
			}
		} else {
			$this->add_render_attribute( 'acf-repeater-widget-wrapper', 'class', 'ae-masonry-' . $masonry );

		}

		$this->add_render_attribute( 'acf-repeater-item', 'class', 'ae-acf-repeater-item' );

		$with_css = false;
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			$with_css = true;
		}

		$post_data = Aepro::$_helper->get_demo_post_data();

		if ( $with_wrapper ) { ?>
			<div <?php echo $this->get_render_attribute_string( 'acf-repeater-widget-wrapper' ); ?>>
		<?php } ?>

		<?php if ( $settings['layout_mode'] === 'carousel' ) { ?>
		<div <?php echo $this->get_render_attribute_string( 'outer-wrapper' ); ?> >
			<?php $this->add_render_attribute( 'swiper-container', 'class', [ 'ae-swiper-container', 'swiper-container' ] ); ?>
		<div <?php echo $this->get_render_attribute_string( 'swiper-container' ); ?> >
			<?php $this->add_render_attribute( 'acf-repeater-wrapper', 'class', [ 'ae-swiper-wrapper', 'swiper-wrapper' ] ); ?>
			<?php $this->add_render_attribute( 'acf-repeater-item', 'class', [ 'ae-swiper-slide', 'swiper-slide' ] ); ?>
			<?php $this->add_render_attribute( 'acf-repeater-inner', 'class', [ 'ae-swiper-slide-wrapper', 'swiper-slide-wrapper' ] ); ?>

	<?php } ?>
		<?php
		$repeater_data = Aepro::$_helper->get_repeater_data( $settings, $post_data->ID );
		?>
		<?php
		if ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
			Frontend::$_in_repeater_block = true;
			?>
			<div <?php echo $this->get_render_attribute_string( 'acf-repeater-wrapper' ); ?>>
				<?php
				while ( have_rows( $repeater_data['repeater_name'], $repeater_data['repeater_type'] ) ) {
					the_row();
					?>
					<div <?php echo $this->get_render_attribute_string( 'acf-repeater-item' ); ?>>
						<div <?php echo $this->get_render_attribute_string( 'acf-repeater-inner' ); ?>>
							<div class="ae_data elementor elementor-<?php echo esc_html( $settings['template'] ); ?>">
								<?php echo Plugin::instance()->frontend->get_builder_content( $settings['template'], $with_css ); ?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
			<?php
			Frontend::$_in_repeater_block = false;
		} else {
			?>
			<div class="ae-no-posts">
				<?php
				echo do_shortcode( $settings['no_posts_message'] );
				$settings['layout_mode'] = '';
				?>
			</div>
		<?php } ?>

		<?php if ( $settings['layout_mode'] === 'carousel' ) { ?>
			<?php if ( $pagination_type !== '' ) { ?>
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
		<?php if ( $with_wrapper ) { ?>
			</div>
		<?php } ?>

		<?php
	}

	public function setting_controls() {
		$this->start_controls_section(
			'repeater_section',
			[
				'label' => __( 'Repeater', 'ae-pro' ),
			]
		);

		$block_layouts[''] = 'Select Template';
		$block_layouts     = $block_layouts + Aepro::$_helper->ae_acf_repeater_layouts();

		$this->add_control(
			'template',
			[
				'label'       => __( 'Block Layout', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $block_layouts,
				'description' => __( 'Know more about layouts <a href="https://wpvibes.link/go/feature-creating-repeater-block-layout" target="_blank">Click Here</a>', 'ae-pro' ),
			]
		);

		$repeater_fields = Aepro::$_helper->get_acf_repeater_field();
		$this->add_control(
			'acf_repeater_field_name',
			[
				'label'       => __( 'Repeater Field', 'ae-pro' ),
				'type'        => Controls_Manager::SELECT,
				'groups'      => $repeater_fields,
				'placeholder' => __( 'Repeater Field', 'ae-pro' ),
				'default'     => '',
			]
		);

		$this->add_control(
			'tab_title',
			[
				'label'       => __( 'Tab Title', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'description' => 'Sub Field name for tab title',
				'condition'   => [
					'_skin' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'tab_layout',
			[
				'label'        => __( 'Layout', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'horizontal' => __( 'Horizontal', 'ae-pro' ),
					'vertical'   => __( 'Vertical', 'ae-pro' ),
				],
				'seperator'    => 'before',
				'prefix_class' => 'ae-acf-repeater-tabs-view-',
				'default'      => 'horizontal',
				'condition'    => [
					'_skin' => 'tabs',
				],

			]
		);

		$this->add_responsive_control(
			'tab_align',
			[
				'label'     => __( 'Tab Align', 'ae-pro' ),
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
				'default'   => 'left',
				'selectors' => [
					'{{WRAPPER}}.ae-acf-repeater-tabs-view-horizontal .ae-acf-repeater-tabs-wrapper' => 'text-align: {{VALUE}};',
				],
				'condition' => [
					'tab_layout' => 'horizontal',
					'_skin'      => 'tabs',
				],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label'            => __( 'Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'separator'        => 'before',
				'fa4compatibility' => 'icon',
				'default'          => [
					'value'   => 'fas fa-plus',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'_skin' => 'accordion',
				],
			]
		);

		$this->add_control(
			'selected_active_icon',
			[
				'label'            => __( 'Active Icon', 'ae-pro' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon_active',
				'default'          => [
					'value'   => 'fas fa-minus',
					'library' => 'fa-solid',
				],
				'condition'        => [
					'selected_icon[value]!' => '',
					'_skin'                 => 'accordion',
				],
			]
		);

		$this->add_control(
			'title_html_tag',
			[
				'label'     => __( 'Title HTML Tag', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'h6'  => 'H6',
					'div' => 'div',
				],
				'default'   => 'div',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'tab_state',
			[
				'label'     => __( 'State on Load', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'       => __( 'Default', 'ae-pro' ),
					'open_specific' => __( 'Open Specific', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin' => [ 'tabs' ],
				],
			]
		);

		$this->add_control(
			'accordion_state',
			[
				'label'     => __( 'State on Load', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'       => __( 'Default', 'ae-pro' ),
					'all_open'      => __( 'All Open', 'ae-pro' ),
					'all_closed'    => __( 'All Close', 'ae-pro' ),
					'open_specific' => __( 'Open Specific', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin' => [ 'accordion' ],
				],
			]
		);

		$this->add_control(
			'specific_tab',
			[
				'label'      => __( 'Specific Tab', 'ae-pro' ),
				'type'       => Controls_Manager::NUMBER,
				'default'    => '2',
				'min'        => 1,
				'max'        => 100,
				'conditions' => [
					'relation' => 'and',
					'terms'    => [
						[

							'relation' => 'or',
							'terms'    => [
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'tabs',
								],
								[
									'name'     => '_skin',
									'operator' => '==',
									'value'    => 'accordion',
								],
							],
						],
						[
							'relation' => 'or',
							'terms'    => [
								[
									'name'     => 'accordion_state',
									'operator' => '==',
									'value'    => 'open_specific',
								],
								[
									'name'     => 'tab_state',
									'operator' => '==',
									'value'    => 'open_specific',
								],
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'enable_url_hashtag',
			[
				'label'        => __( 'Enable Hashtag', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
				'condition'    => [
					'_skin' => [ 'accordion', 'tabs' ],
				],
			]
		);

		$this->add_control(
			'fragment_type',
			[
				'label'     => __( 'Fragment', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'default'      => __( 'Default', 'ae-pro' ),
					'custom_field' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'default',
				'condition' => [
					'_skin'              => [ 'accordion', 'tabs' ],
					'enable_url_hashtag' => 'yes',
				],
			]
		);

		$this->add_control(
			'fragment_custom_field',
			[
				'label'       => __( 'Fragment Custom Field', 'ae-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'Custom Field', 'ae-pro' ),
				'condition'   => [
					'_skin'              => [ 'accordion', 'tabs' ],
					'enable_url_hashtag' => 'yes',
					'fragment_type'      => 'custom_field',
				],
			]
		);

		$this->end_controls_section();
	}

	public function layout_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'condition' => [
					'_skin!' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'layout_mode',
			[
				'label'        => __( 'Layout Mode', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'grid'     => __( 'Grid', 'ae-pro' ),
					'carousel' => __( 'Carousel', 'ae-pro' ),
				],
				'default'      => 'grid',
				'prefix_class' => 'ae-acf-repeater-layout-',
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
					'layout_mode' => 'grid',
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
					'layout_mode' => 'grid',
				],
				'selectors'       => [
					'{{WRAPPER}} .ae-acf-repeater-item' => 'width: calc(100%/{{VALUE}})',
				],
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
					'layout_mode' => 'grid',
				],
				'selectors' => [
					'{{WRAPPER}}.ae-acf-repeater-layout-grid .ae-acf-repeater-item' => 'padding-left:{{SIZE}}{{UNIT}}; padding-right:{{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .ae-acf-repeater-item' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => 'grid',
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
					'{{WRAPPER}} .ae-acf-repeater-wrapper' => 'margin-bottom:{{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'layout_mode' => 'carousel',
				],
			]
		);

		$this->add_control(
			'no_posts_message',
			[
				'label'     => __( 'No Posts Message', 'ae-pro' ),
				'type'      => Controls_Manager::TEXTAREA,
				'separator' => 'before',
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
					'_skin!'      => [ 'tabs', 'accordion' ],
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
				'condition'      => [
					'effect' => [ 'slide', 'coverflow' ],
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
				'condition'      => [
					'effect' => [ 'slide', 'coverflow' ],
				],
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
						'min'  => 300,
						'max'  => 10000,
						'step' => 300,
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
						'max'  => 10000,
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
					'effect' => [ 'slide', 'coverflow' ],
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

	public function layout_styles() {
		$this->start_controls_section(
			'layout_style',
			[
				'label'     => __( 'Layout', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'_skin!' => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'item_bg',
				'label'    => __( 'Item Background', 'ae-pro' ),
				'types'    => [ 'none', 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
				'default'  => '#fff',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'item_border',
				'label'    => __( 'Border', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
			]
		);

		$this->add_control(
			'item_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae-acf-repeater-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'item_box_shadow',
				'label'    => __( 'Item Shadow', 'ae-pro' ),
				'selector' => '{{WRAPPER}} .ae-acf-repeater-inner',
			]
		);

		$this->end_controls_section();
	}

	public function carousel_styles() {
		$this->start_controls_section(
			'carousel_style',
			[
				'label'     => __( 'Carousel', 'ae-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_mode' => 'carousel',
					'_skin!'      => [ 'tabs', 'accordion' ],
				],
			]
		);

		$this->add_control(
			'heading_style_arrow',
			[
				'label'     => __( 'Arrow', 'ae-pro' ),
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
						'size' => 25,
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
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper .ae-swiper-container' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .ae-acf-repeater-widget-wrapper .ae-swiper-outer-wrapper' => 'position: relative',
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
	}

	public function get_swiper_data( $settings ) {

		if ( $settings['speed']['size'] ) {
			$swiper_data['speed'] = $settings['speed']['size'];
		} else {
			$swiper_data['speed'] = 1000;
		}
		$swiper_data['direction'] = $settings['direction'];

		if ( $settings['autoplay'] === 'yes' ) {
			$swiper_data['autoplay']['delay'] = $settings['duration']['size'];

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
