<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_True_False extends RuleBase {



	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_true_false';
	}


	public function get_title() {
		return __( 'ACF True False', 'ae-pro' );
	}

	public function get_name_control() {
		return [
			'label'       => 'ACF Name',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Name', 'ae-pro' ),
		];
	}

	protected function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'     => __( 'Is Equal', 'ae-pro' ),
			'not_equal' => __( 'Is Not Equal', 'ae-pro' ),
			'empty'     => __( 'Is Empty', 'ae-pro' ),
			'not_empty' => __( 'Is Not Empty', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function get_value_control() {
		return [
			'label'   => 'Value',
			'type'    => Controls_Manager::SELECT,
			'options' => [
				'true' => __( 'True', 'ae-pro' ),
				''     => __( 'False', 'ae-pro' ),
			],
		];
	}

	public function check( $name, $operator, $value ) {
		global $post;
		$field_value = get_field( $name );
		return $this->compare( $field_value, $value, $operator );
	}
}
