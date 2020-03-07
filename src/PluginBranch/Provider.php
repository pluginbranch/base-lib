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
