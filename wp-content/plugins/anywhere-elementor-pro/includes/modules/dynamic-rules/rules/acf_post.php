<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_Post extends RuleBase {



	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_post';
	}


	public function get_title() {
		return __( 'ACF Post', 'ae-pro' );
	}

	public function get_name_control() {
		return [
			'label'       => 'ACF Name',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Name', 'ae-pro' ),
		];
	}

	public function get_value_control() {
		return [
			'label'       => __( 'Value', 'ae-pro' ),
			'type'        => 'aep-query',
			'label_block' => true,
			'query_type'  => 'post',
			'multiple'    => true,
		];
	}

	protected function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'        => __( 'Is Equal', 'ae-pro' ),
			'not_equal'    => __( 'Is Not Equal', 'ae-pro' ),
			'contains'     => __( 'Contains', 'ae-pro' ),
			'not_contains' => __( 'Does Not Contains', 'ae-pro' ),
			'empty'        => __( 'Is Empty', 'ae-pro' ),
			'not_empty'    => __( 'Is Not Empty', 'ae-pro' ),
		];

		return $rule_operators;
	}



	public function check( $name, $operator, $value ) {
		global $post;
		$field_value  = '';
		$field_object = get_field_object( $name );
		if ( ! empty( $field_object ) ) {
			$return_format = $field_object['return_format'];
			if ( ! empty( $field_object['value'] ) && 'object' === $return_format ) {
				$field_value = $field_object['value']->ID;
			} else {
				$field_value = get_field( $name );
			}
		}
		return $this->compare( $field_value, $value, $operator );
	}
}
