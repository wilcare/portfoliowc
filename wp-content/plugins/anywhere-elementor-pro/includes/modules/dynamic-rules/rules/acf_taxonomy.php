<?php

namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_Taxonomy extends RuleBase {



	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_taxonomy';
	}


	public function get_title() {
		return __( 'ACF Taxonomy', 'ae-pro' );
	}

	public function get_name_control() {
		return [
			'label'       => 'ACF Name',
			'type'        => Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Name', 'ae-pro' ),
		];
	}

	public function get_multiple_value_control() {
		$taxonomies              = [];
		$ae_taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];
		$taxonomies              = get_taxonomies( $ae_taxonomy_filter_args, 'names' );

		$multiple_controls = [];

		foreach ( $taxonomies as $key => $taxonomy ) {
			$multiple_controls[] = [
				'condition_name' => 'ae_rule_acf_taxonomy_types',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => 'aep-query',
				'label_block'    => true,
				'query_type'     => 'taxonomy',
				'object_type'    => $key,
				'multiple'       => true,
			];
		}

		return $multiple_controls;
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
		$field_object = get_field_object( $name );
		$field_value  = '';
		if ( ! empty( $field_object ) ) {
			$return_format = $field_object['return_format'];
			if ( ! empty( $field_object['value'] ) && 'object' === $return_format ) {
				$value = $field_object['value'];
				foreach ( $value as $key => $taxonomy ) {
					# code...
					$field_value[ $key ] = $taxonomy->term_id;
				}
			} else {
				$field_value = get_field( $name );
			}
		}
		return $this->compare( $field_value, $value, $operator );
	}
}
