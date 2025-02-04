<?php

namespace Aepro\Classes;

use Aepro\Aepro;

class AcfMaster {


	private static $_instance = null;


	protected $post_id;

	protected $field_name;

	protected $field_list;

	protected $field_types;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected function set_field_types() {

		$acf_free = [

			'text'     => 'Text',
			'textarea' => 'Text Area',
			'number'   => 'Text Area',
			'range'    => 'Text Area',
			'email'    => 'Text Area',
			'url'      => 'Text Area',
			'password' => 'Text Area',
			'image'    => 'Text Area',
			'file'     => 'Text Area',
			'wysiwyg'  => 'Text Area',
			'oembed'   => 'Text Area',
			'gallery'  => 'Text Area',

		];

		$this->field_types = $acf_free;
	}

	/**
	 * @param $data
	 * @param $field_name
	 * @param $field_type
	 *
	 * $data -
	 * $field_name - Key for ACF Field
	 * $field_type - term, post, option, user
	 *
	 * @return mixed|string
	 */
	public function get_field_value( $field_args ) {

		$field_value = '';

		switch ( $field_args['field_type'] ) {

			case 'post':
				$post = Aepro::$_helper->get_demo_post_data();
				if ( $field_args['is_sub_field'] === 'repeater' ) {
					$field_value = $this->get_repeater_field_data( $field_args['field_name'], $field_args['parent_field'], $post->ID );
				} elseif ( $field_args['is_sub_field'] === 'group' ) {
					$field_value = $this->get_group_field_data( $field_args['field_name'], $field_args['parent_field'], $post->ID );
				} elseif ($field_args['is_sub_field'] === 'flexible') {
					$field_value = $this->get_flexible_field_data( $field_args, $post->ID );
				}else {
					$field_value = get_field( $field_args['field_name'], $post->ID, true );
				}

				break;

			case 'term':
				$term                    = Aepro::$_helper->get_preview_term_data();
							$field_value = get_field( $field_args['field_name'], $term['taxonomy'] . '_' . $term['prev_term_id'], false );
				break;

			case 'user':   // Get current author of current archive using queries object
							$author      = Aepro::$_helper->get_preview_author_data();
							$field_value = get_field( $field_args['field_name'], 'user_' . $author['prev_author_id'], true );

				break;

			case 'option': // Get Option page's field value		
					if ($field_args['is_sub_field'] === 'flexible') {
						$field_value = $this->get_flexible_field_data( $field_args, 'option');
					}else{
						$field_value = get_field( $field_args['field_name'], 'option', true );
					}		
				break;

		}

		return $field_value;
	}

	
	public function get_flexible_field_data($field_args, $data){
		$value = '';
		$field_name = $field_args['field_name'];
		if(empty($field_name)){
			return;
		}
		if ( \Elementor\Plugin::instance()->editor->is_edit_mode() ) {
			if(empty($field_args['flexible_field'])){
				return;
			}
			$parent_field_data = explode( ':', $field_args['flexible_field'] );
			if($parent_field_data[0] === 'option'){
				$parent_field_name = $parent_field_data[2];
				$layout = $parent_field_data[3];
				$data = 'option';
			}else{
				$parent_field_name = $parent_field_data[1];
				$layout = $parent_field_data[2];
			}
			if(get_post_type() == 'ae_global_templates'){
				global $post;
				$ae_render_mode = get_post_meta($post->ID, 'ae_render_mode', true);
				if($ae_render_mode === 'acf_repeater_layout'){
					$flexible_content = get_field( $parent_field_name, $data );
					
					foreach($flexible_content as $key => $fc){
						if(!is_array($fc)||(!array_key_exists('acf_fc_layout',$fc))){
							return;
						}
						if($fc['acf_fc_layout'] == $layout ){
							$index = $key;
							break;
						}
					}
					$value = $flexible_content[$index][$field_name];
				}else{
					$value = get_sub_field( $field_name );
				}
			}else{
				$value = get_sub_field( $field_name );
			}
		}else{
			$value = get_sub_field($field_name);
		}
		return $value;
	}

	public function get_group_field_data(  $field_name, $group_field, $data_id ) {

		$group_fields_arr = explode( '.', $group_field );

		$main_field = get_field( $group_fields_arr[0], $data_id );

		$leaf = $main_field;

		foreach ( $group_fields_arr as $rf ) {

			if ( $rf === $group_fields_arr[0] ) {
				continue;
			}

			if ( isset( $leaf[0][ $rf ] ) ) {
				$leaf = $main_field[0][ $rf ];
			} else {
				break;
			}
		}

		$value = $leaf[ $field_name ];
		return $value;
	}

	public function get_repeater_field_data( $field_name, $repeater_field, $data_id ) {
		$repeater = Aepro::$_helper->is_repeater_block_layout();
		
		if ( isset( $repeater['field'] ) ) {
			// editing a block layout. Return first item matched

			$repeater_fields_arr = explode( '.', $repeater_field );
			$main_field          = get_field( $repeater_fields_arr[0], $data_id );
			$leaf                = $main_field;

			foreach ( $repeater_fields_arr as $rf ) {

				if ( $rf === $repeater_fields_arr[0] ) {
					continue;
				}

				if ( isset( $leaf[0][ $rf ] ) ) {
					$leaf = $main_field[0][ $rf ];
				} else {
					break;
				}
			}

			$value = $leaf[0][ $field_name ];

		} else {
			// fetch data using get_sub_field.
			$repeater_fields_arr = explode( '.', $repeater_field );
			if ( is_array( $repeater_fields_arr ) && count( $repeater_fields_arr ) == 1 ) {
				return get_sub_field( $field_name );
			}
			// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedElse
			else {
				// Todo:: Nested Repeater Fields
			}
		}
		return $value;
	}

	protected function get_sub_field_data() {
	}

	public function get_field_object( $field_args, $data ) {
		switch ( $field_args['field_type'] ) {
			case 'post':
				if ( $field_args['is_sub_field'] === 'repeater' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} elseif ( $field_args['is_sub_field'] === 'group' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} elseif ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				} else {
					$field_object = get_field_object( $field_args['field_name'], $data );
				}
				break;

			case 'term':
				$term = get_term_by( 'term_taxonomy_id', $data['prev_term_id'] );
				$field_object = get_field_object( $field_args['field_name'], $term );
				break;
			case 'option':
				if ( $field_args['is_sub_field'] === 'flexible' ) {
					$field_object = $this->get_sub_field_object( $field_args, $data );
				}else{
					$field_object = get_field_object( $field_args['field_name'], $data );
				}
				break;
			case 'user':
				$field_object = get_field_object( $field_args['field_name'], $data );
				break;
		}
		return $field_object;
	}

	public function get_sub_field_object( $field_args, $data ) {

		if(empty($field_args['parent_field'])){
			return [];
		}
		$choices    = [];
		if($field_args['is_sub_field'] == 'flexible'){
			$parent_field_data = explode( ':', $field_args['flexible_field'] );
			if($parent_field_data[0] === 'option'){
				$data = 'option';
			}
		}
		
		$fields_arr = get_field_object( $field_args['parent_field'], $data );
		
		if($field_args['is_sub_field'] == 'flexible'){
			$layouts = $fields_arr['layouts'];

			foreach($layouts as $key => $layout){
				if($layout['name'] == $field_args['layout'] ){
					if(!array_key_exists('sub_fields',$layout)){
						return;
					}
					$sub_fields = $layout['sub_fields'];
				}
			}
		}else{
			$sub_fields = $fields_arr['sub_fields'];
		}
		foreach ( $sub_fields as $sfield ) {
			if ( $sfield['type'] === $field_args['_skin'] ) {
				if ( $sfield['name'] === $field_args['field_name'] ) {
					$choices = $sfield;
				}
			}
		}
		return $choices;
	}
}

AcfMaster::instance();
