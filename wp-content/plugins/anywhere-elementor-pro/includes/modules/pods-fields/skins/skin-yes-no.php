<?php

namespace Aepro\Modules\PodsFields\Skins;

use Aepro\Classes\PodsMaster;
use Aepro\Base\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;


class Skin_Yes_No extends Skin_Base {

	public function get_id() {
		return 'yes_no';
	}

	public function get_title() {
		return __( 'Yes No', 'ae-pro' );
	}
	//phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	protected function _register_controls_actions() {

		parent::_register_controls_actions();
		add_action( 'elementor/element/ae-pods/general/after_section_end', [ $this, 'register_style_controls' ] );
	}

	public function register_controls( Widget_Base $widget ) {

		$this->parent = $widget;

		$this->register_boolean_controls();
	}

	public function register_style_controls() {

		$this->start_controls_section(
			'general_style',
			[
				'label' => __( 'Global Style', 'ae-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'typography',
				'selector' => '{{WRAPPER}}',
			]
		);

		$this->add_control(
			'color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'       => __( 'Align', 'ae-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
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
				'selectors'   => [
					'{{WRAPPER}}' => 'text-align:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'true_heading',
			[
				'label'     => __( 'True Content Styles', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'true_typography',
				'selector' => '{{WRAPPER}} .true-message',
			]
		);

		$this->add_control(
			'true_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .true-message' => 'color:{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'false_heading',
			[
				'label'     => __( 'False Content Styles', 'ae-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'false_typography',
				'selector' => '{{WRAPPER}} .false-message',
			]
		);

		$this->add_control(
			'false_color',
			[
				'label'     => __( 'Color', 'ae-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .false-message' => 'color:{{VALUE}}',
				],
			]
		);

		$this->end_controls_section();
	}



	public function render() {

		$list_items = [];
		$settings   = $this->parent->get_settings();

		$field_args = [
			'field_name' => $settings['field_name'],
			'field_type' => $settings['field_type'],

		];

		if ( $settings['pods_option_name'] !== '' ) {
			$field_args['pods_option_name'] = $settings['pods_option_name'];
		}

		$value = PodsMaster::instance()->get_field_value( $field_args );

		$true_message  = $this->get_instance_value( 'true_message' );
		$false_message = $this->get_instance_value( 'false_message' );

		if ( $value == 1 && $true_message !== '' ) {

			echo '<div class="true-message">' . do_shortcode( $true_message ) . '</div>';

		} elseif ( $value === 0 && $false_message !== '' ) {

			echo '<div class="false-message">' . do_shortcode( $false_message ) . '</div>';

		}
	}

	protected function register_boolean_controls() {

		$this->add_control(
			'message_heading',
			[
				'label'       => __( 'Messages', 'ae-pro' ),
				'type'        => Controls_Manager::HEADING,
				'separator'   => 'before',
				'description' => __( 'Message to display when field return True or False. Also supports shortcode', 'ae-pro' ),
			]
		);

		$this->add_control(
			'true_message',
			[
				'label' => __( 'True Message', 'ae-pro' ),
				'type'  => Controls_Manager::TEXTAREA,
			]
		);

		$this->add_control(
			'false_message',
			[
				'label' => __( 'False Message', 'ae-pro' ),
				'type'  => Controls_Manager::TEXTAREA,
			]
		);
	}

}
