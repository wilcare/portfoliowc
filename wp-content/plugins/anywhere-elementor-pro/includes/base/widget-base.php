<?php

namespace Aepro\Base;

use Aepro\Aepro;

abstract class Widget_Base extends \Elementor\Widget_Base {

	public function is_enabled() {

		return true;
	}

	public function get_custom_help_url() {
		return Aepro::$_helper->get_help_url_prefix() . $this->get_name();
	}

	public function is_debug_on() {

		if ( \Aepro\Aepro::$_widget_debug ) {
			echo '<div class="ae-widget-debug ' . esc_html( $this->get_name() ) . '">' . esc_html( $this->get_title() ) . '</div>';
		}

		return \Aepro\Aepro::$_widget_debug;
	}

}
