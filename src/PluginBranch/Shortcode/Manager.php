<?php
namespace PluginBranch\Shortcode;


/**
 * Class Manager
 *
 * @since   0.1.0
 *
 * @package PluginBranch\Shortcode
 */
class Manager {
	/**
	 * Get the list of shortcodes available for handling.
	 *
	 * @since  4.7.5
	 *
	 * @return array An associative array of shortcodes in the shape `[ <slug> => <class> ]`
	 */
	public function get_registered_shortcodes() {
		$shortcodes = [];

		/**
		 * Allow the registering of shortcodes into the our Pro plugin.
		 *
		 * @since 0.1.0
		 *
		 * @param array An associative array of shortcodes in the shape `[ <slug> => <class> ]`
		 */
		$shortcodes = apply_filters( 'pb_shortcodes', $shortcodes );

		return $shortcodes;
	}

	/**
	 * Verifies if a given shortcode slug is registered for handling.
	 *
	 * @since 0.1.0
	 *
	 * @param string $slug Which slug we are checking if is registered.
	 *
	 * @return bool
	 */
	public function is_shortcode_registered( $slug ) {
		$registered_shortcodes = $this->get_registered_shortcodes();
		return isset( $registered_shortcodes[ $slug ] );
	}

	/**
	 * Verifies if a given shortcode class name is registered for handling.
	 *
	 * @since 0.1.0
	 *
	 * @param string $class_name Which class name we are checking if is registered.
	 *
	 * @return bool
	 */
	public function is_shortcode_registered_by_class( $class_name ) {
		$registered_shortcodes = $this->get_registered_shortcodes();
		return in_array( $class_name, $registered_shortcodes );
	}

	/**
	 * Add new shortcodes handler to catch the correct strings.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function add_shortcodes() {
		$registered_shortcodes = $this->get_registered_shortcodes();

		// Add to WordPress all of the registered Shortcodes.
		foreach ( $registered_shortcodes as $shortcode => $class_name ) {
			add_shortcode( $shortcode, [ $this, 'handle' ] );
		}
	}

	/**
	 * Makes sure we are correctly handling the Shortcodes we manage.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $arguments Set of arguments passed to the Shortcode at hand.
	 * @param string $content   Contents passed to the shortcode, inside of the open and close brackets.
	 * @param string $shortcode Which shortcode tag are we handling here.
	 *
	 * @return string
	 */
	public function handle( $arguments, $content, $shortcode ) {
		$registered_shortcodes = $this->get_registered_shortcodes();

		// Bail when we try to handle an unregistered shortcode (shouldn't happen).
		if ( ! $this->is_shortcode_registered( $shortcode ) ) {
			return false;
		}

		/**
		 * @var Shortcode_Interface $instance
		 */
		$instance = new $registered_shortcodes[ $shortcode ];
		$instance->setup( $arguments, $content );

		return $instance->get_html();
	}
}