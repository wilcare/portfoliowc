<?php

namespace Aepro\Modules\PodsFields\Skins;

use Aepro\Aepro;
use Aepro\Classes\PodsMaster;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Aepro\Base\Widget_Base;
use Elementor\Group_Control_Image_Size;


class Skin_File_Image extends Skin_Website {

	public function get_id() {
		return 'file_image';
	}

	public function get_title() {
		return __( 'File -  Image', 'ae-pro' );
	}
    //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {

		parent::_register_controls_actions();
		add_action( 'elementor/element/ae-pods/general/after_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'    => 'image_size',
				'exclude' => [ 'custom' ],
			]
		);

		$this->add_control(
			'enable_image_ratio',
			[
				'label'        => __( 'Enable Image Ratio', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'label_off'    => __( 'No', 'ae-pro' ),
				'return_value' => 'yes',
			]
		);

		$this->add_responsive_control(
			'image_ratio',
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
					'{{WRAPPER}} .ae_pods_image_wrapper.ae_pods_image_ratio_yes .ae_pods_image_block' => 'padding-bottom: calc( {{SIZE}} * 100% );',
				],
				'condition'      => [
					$this->get_control_id( 'enable_image_ratio' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'enable_link',
			[
				'label'        => __( 'Enable Link', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'no'           => __( 'No', 'ae-pro' ),
				'yes'          => __( 'Yes', 'ae-pro' ),
				'return_value' => 'yes',
				'default'      => __( 'no', 'ae-pro' ),
			]
		);

		$this->add_control(
			'url_type',
			[
				'label'     => __( 'Links To', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'media'       => __( 'Full Image', 'ae-pro' ),
					'static'      => __( 'Static URL', 'ae-pro' ),
					'post'        => __( 'Post URL', 'ae-pro' ),
					'dynamic_url' => __( 'Custom Field', 'ae-pro' ),
				],
				'default'   => 'static',
				'condition' => [
					$this->get_control_id( 'enable_link' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'static_url',
			[
				'label'     => __( 'Static URL', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'http://', 'ae-pro' ),
				'condition' => [
					$this->get_control_id( 'url_type' )    => 'static',
					$this->get_control_id( 'enable_link' ) => 'yes',

				],
			]
		);

		$this->add_control(
			'custom_field_url',
			[
				'label'     => __( 'Custom Field', 'ae-pro' ),
				'type'      => Controls_Manager::TEXT,
				'condition' => [
					$this->get_control_id( 'url_type' )    => 'dynamic_url',
					$this->get_control_id( 'enable_link' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'open_lightbox',
			[
				'label'     => __( 'Lightbox', 'ae-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'default',
				'options'   => [
					'default' => __( 'Default', 'ae-pro' ),
					'yes'     => __( 'Yes', 'ae-pro' ),
					'no'      => __( 'No', 'ae-pro' ),
				],
				'condition' => [
					$this->get_control_id( 'url_type' )    => 'media',
					$this->get_control_id( 'enable_link' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'new_tab',
			[
				'label'        => __( 'Open in new tab', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition'    => [
					$this->get_control_id( 'enable_link' ) => 'yes',
				],
			]
		);

		$this->add_control(
			'nofollow',
			[
				'label'        => __( 'Add nofollow', 'ae-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_off'    => __( 'No', 'ae-pro' ),
				'label_on'     => __( 'Yes', 'ae-pro' ),
				'return_value' => 1,
				'default'      => __( 'label_off', 'ae-pro' ),
				'condition'    => [
					$this->get_control_id( 'enable_link' ) => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'text_align',
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
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'show_overlay',
			[
				'label'        => __( 'Show Overlay', 'ae-pro' ),
				'type'         => Controls_Manager::SELECT,
				'options'      => [
					'hover'  => __( 'On Hover', 'ae-pro' ),
					'always' => __( 'Always', 'ae-pro' ),
					'never'  => __( 'Never', 'ae-pro' ),
				],
				'default'      => 'never',
				'prefix_class' => 'overlay-',
				'selectors'    => [
					'{{WRAPPER}}.overlay-always .ae-pods-overlay-block' => 'display: block;',
					'{{WRAPPER}}.overlay-hover .ae_pods_image_wrapper:hover .ae-pods-overlay-block' => 'display: block;',
				],
			]
		);

		$this->add_control(
			'overlay_icon',
			[
				'label'       => __( 'Overlay Icon', 'ae-pro' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'default'     => 'fa fa-link',
			]
		);
	}

	public function register_style_image_controls() {
		$this->start_controls_section(
			'section_style_image',
			[
				'label' => __( 'Image', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'width',
			[
				'label'          => __( 'Width', 'ae-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'space',
			[
				'label'          => __( 'Max Width', 'ae-pro' ) . ' (%)',
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%' ],
				'range'          => [
					'%' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'separator_panel_style',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'image_effects' );

		$this->start_controls_tab(
			'normal',
			[
				'label' => __( 'Normal', 'ae-pro' ),
			]
		);

		$this->add_control(
			'opacity',
			[
				'label'     => __( 'Opacity', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters',
				'selector' => '{{WRAPPER}} .ae_pods_image_wrapper img',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => __( 'Hover', 'ae-pro' ),
			]
		);

		$this->add_control(
			'opacity_hover',
			[
				'label'     => __( 'Opacity', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_pods_image_wrapper:hover img' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'css_filters_hover',
				'selector' => '{{WRAPPER}} .ae_pods_image_wrapper:hover img',
			]
		);

		$this->add_control(
			'background_hover_transition',
			[
				'label'     => __( 'Transition Duration', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'transition-duration: {{SIZE}}s',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __( 'Hover Animation', 'ae-pro' ),
				'type'  => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'image_border',
				'selector'  => '{{WRAPPER}} .ae_pods_image_wrapper img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_border_radius',
			[
				'label'      => __( 'Border Radius', 'ae-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .ae_pods_image_wrapper img',
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label'     => __( 'Overlay Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pods-overlay' => 'background-color: {{VALUE}};',
				],
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
					'{{WRAPPER}} .ae_pods_image_wrapper .ae-pods-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_section();
	}

	public function register_style_icon_controls() {
		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => __( 'Icon', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [
					'{{WRAPPER}} .ae-pods-overlay-block i' => 'color: {{VALUE}};',
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
					'{{WRAPPER}} .ae_pods_image_wrapper:hover .ae-pods-overlay-block i' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'     => __( 'Size', 'ae-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'default'   => [
					'size' => 20,
				],
				'range'     => [
					'px' => [
						'min' => 6,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .ae-pods-overlay-block  i' => 'font-size: {{SIZE}}{{UNIT}};',
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

		$this->start_controls_tabs( 'overlay_style' );

		$this->start_controls_tab( 'overlay_style_default', [ 'label' => __( 'Default', 'ae-pro' ) ] );

		$this->add_control(
			'overlay_color',
			[
				'label'     => __( 'Background Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ae-pods-overlay' => 'background-color: {{VALUE}};',
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
					'{{WRAPPER}} .ae_pods_image_wrapper:hover .ae-pods-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	public function register_style_control_extend() {
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
					'{{WRAPPER}} .ae_pods_image_wrapper img' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);
	}

	public function render() {

		$settings  = $this->parent->get_settings();
		$link_text = '';

		$field_args = [
			'field_name' => $settings['field_name'],
			'field_type' => $settings['field_type'],

		];

		if ( $settings['pods_option_name'] !== '' ) {
			$field_args['pods_option_name'] = $settings['pods_option_name'];
		}

		$image = PodsMaster::instance()->get_field_value( $field_args );

		if ( isset( $image ) && ! empty( $image ) ) {
			if ( isset( $image[0] ) && is_array( $image[0] ) ) {
				$image_id = $image[0]['ID'];
			} else {
				$image_id = $image['ID'];
			}

			$image_size = $this->get_instance_value( 'image_size_size' );

			$image_url = wp_get_attachment_url( $image_id );

			$image_arr = wp_get_attachment_image_src( $image_id, $image_size );

			$image = $image_arr[0];

			if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) {
				// Get Link
				$url_type = $this->get_instance_value( 'url_type' );
				$url      = '';

				switch ( $url_type ) {

					case 'static':
						$url = $this->get_instance_value( 'static_url' );
						break;

					case 'post':
						$curr_post = Aepro::$_helper->get_demo_post_data();
						if ( isset( $curr_post ) && isset( $curr_post->ID ) ) {
							$url = get_permalink( $curr_post->ID );
						}
						break;

					case 'dynamic_url':
						$custom_field = $this->get_instance_value( 'custom_field_url' );

						if ( $custom_field !== '' ) {

							$field_args['field_name'] = $custom_field;
							$url                      = PodsMaster::instance()->get_field_value( $field_args );
						}
						break;

					case 'media':
						$url = wp_get_attachment_url( $image_id );
						break;

				}

				$this->parent->add_render_attribute( 'anchor', 'href', $url );

				$this->parent->add_render_attribute( 'anchor', [ 'data-elementor-open-lightbox' => $this->get_instance_value( 'open_lightbox' ) ] );

				if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
					$this->parent->add_render_attribute( 'anchor', [ 'class' => 'elementor-clickable' ] );
				}

				$new_tab = $this->get_instance_value( 'new_tab' );
				if ( $new_tab == 1 ) {
					$this->parent->add_render_attribute( 'anchor', 'target', '_blank' );
				}

				$no_follow = $this->get_instance_value( 'nofollow' );
				if ( $no_follow == 1 ) {
					$this->parent->add_render_attribute( 'anchor', 'rel', 'nofollow' );
				}
			}
			$this->parent->add_render_attribute( 'image_wrapper', 'class', 'ae_pods_image_wrapper' );
			if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) {
				$this->parent->add_render_attribute( 'image_wrapper', 'class', 'ae_pods_image_ratio_yes' );
			}
			?>
		<div <?php echo $this->parent->get_render_attribute_string( 'image_wrapper' ); ?>>
			<?php
			if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) {
				?>
			<a <?php echo $this->parent->get_render_attribute_string( 'anchor' ); ?>>
				<?php
			}
			if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) {
				?>
			<div class="ae_pods_image_block">
			<?php } ?>
				<img src="<?php echo $image; ?>" />
			<?php if ( $this->get_instance_value( 'enable_image_ratio' ) === 'yes' ) { ?>
			</div>
		<?php } ?>
				<div class="ae-pods-overlay-block">
					<div class="ae-pods-overlay"></div>
					<i class="<?php echo $this->get_instance_value( 'overlay_icon' ); ?>"></i>
				</div>
			<?php if ( $this->get_instance_value( 'enable_link' ) === 'yes' ) { ?>
			</a>
				<?php
			}
			?>
		</div>
			<?php
		}
	}

	public function register_style_controls() {

		$this->register_style_image_controls();

		$this->register_style_icon_controls();
	}
}
