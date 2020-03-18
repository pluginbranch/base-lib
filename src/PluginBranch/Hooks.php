<?php
namespace PluginBranch;

/**
 * Class Hooks
 *
 * @since 0.1.0
 *
 * @package PluginBranch
 */
class Hooks {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 0.1.0
	 *
	 * @return void Service provider register has no return.
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions for the provider.
	 *
	 * @since 0.1.0
	 *
	 * @return void Adds the filters but has no return.
	 */
	protected function add_actions() {
		add_action( 'init', [ $this, 'action_add_shortcodes' ] );
	}

	/**
	 * Adds the filters for the provider.
	 *
	 * @since 0.1.0
	 *
	 * @return void Adds the filters but has no return.
	 */
	protected function add_filters() {

	}

	/**
	 * Action to add all shortcodes associated with Plugin branch Lib.
	 *
	 * @since 0.1.0
	 *
	 * @return void Action hook has no return.
	 */
	public function action_add_shortcodes() {
		pb( Shortcode\Manager::class )->add_shortcodes();
	}
}