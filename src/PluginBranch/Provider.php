<?php
namespace PluginBranch;

use tad_DI52_ServiceProvider;

/**
 * Plugin Provider
 *
 * This class should handle implementation binding, builder functions and hooking for any first-level hook and be
 * devoid of business logic.
 *
 * @since 0.1.0
 */
class Provider extends tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		$this->container->singleton( Utils\Arrays::class, Utils\Arrays::class );
		$this->container->singleton( Shortcode\Manager::class, Shortcode\Manager::class );
		$this->hook();
	}

	/**
	 * Any hooking for any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since 0.1.0
	 */
	protected function hook() {
		add_action( 'init', [ $this, 'action_add_shortcodes' ] );
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

	/**
	 * Binds and sets up implementations at boot time.
	 *
	 * @since 0.1.0
	 */
	public function boot() {
		// no ops
	}
}
