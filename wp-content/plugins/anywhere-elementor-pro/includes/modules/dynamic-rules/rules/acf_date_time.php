<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_Date_Time extends RuleBase {


	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_date_time';
	}


	public function get_title() {
		return __( 'ACF Date Time', 'ae-pro' );
	}

	public function get_name_control() {
		return [
			'label'       => 'ACF Name',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Name', 'ae-pro' ),
		];
	}

	public function get_multiple_value_control() {
		return [
			[
				'condition_name' => 'ae_rule_datepicker_type',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => Controls_Manager::DATE_TIME,
				'object_type'    => 'date_time',
				'picker_options' => [
					'enableTime' => true,
					'dateFormat' => 'Y-m-d h:i K',
				],
			],
			[
				'condition_name' => 'ae_rule_datepicker_type',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => Controls_Manager::DATE_TIME,
				'object_type'    => 'date',
				'picker_options' => [
					'enableTime' => false,
					'dateFormat' => 'F j, Y',
				],
			],
			[
				'condition_name' => 'ae_rule_datepicker_type',
				'label'          => __( 'Value', 'ae-pro' ),
				'type'           => Controls_Manager::DATE_TIME,
				'object_type'    => 'time',
				'picker_options' => [
					'enableSeconds' => true,
					'noCalendar'    => true,
					'dateFormat'    => 'G:i:S K',
					'time_24hr'     => false,
				],
			],
		];
	}

	protected function get_rule_operators() {
		$rule_operators = [];

		$rule_operators = [
			'equal'     => __( 'Is Equal', 'ae-pro' ),
			'not_equal' => __( 'Is Not Equal', 'ae-pro' ),
			'empty'     => __( 'Is Empty', 'ae-pro' ),
			'not_empty' => __( 'Is Not Empty', 'ae-pro' ),
			'less'      => __( 'Less Than', 'ae-pro' ),
			'greater'   => __( 'Greater Than', 'ae-pro' ),
		];

		return $rule_operators;
	}

	public function check( $name, $operator, $value ) {
		global $post;

		$field_value = acf_get_metadata( $post->ID, $name );
		$value       = strtotime( $value );
		$field_value = strtotime( $field_value );

		return $this->compare( $field_value, $value, $operator );
	}
}
