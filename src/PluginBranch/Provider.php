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
		$this->register_hooks();
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @return void Register of hooks has no return.
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
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
