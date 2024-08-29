<?php
namespace Aepro\Modules\DynamicRules\Rules;

use Aepro\Base\RuleBase;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Acf_Text extends RuleBase {


	public function get_group() {
		return 'acf';
	}

	public function get_name() {
		return 'acf_text';
	}


	public function get_title() {
		return __( 'ACF Text', 'ae-pro' );
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
			'label'       => 'Value',
			'type'        => Controls_Manager::TEXT,
			'placeholder' => __( 'Value', 'ae-pro' ),
		];
	}


	public function check( $name, $operator, $value ) {
		global $post;
		$field_value = get_field( $name );
		return $this->compare( $field_value, $value, $operator );
	}
}
