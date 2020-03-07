<?php
namespace PluginBranch;

use InvalidArgumentException;
use tad_DI52_Container;

/**
 * Class Gym/Container
 *
 * Gym Dependency Injection Container.
 *
 * @since  0.1.0
 *
 */
class Container extends tad_DI52_Container {
	/**
 	 * Static Singleton Holder
	 *
	 * @since  0.1.0
	 *
	 * @var    self
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @since  0.1.0
	 *
	 * @return self
	 */
	public static function init() {
		return self::$instance ? self::$instance : self::$instance = new self;
	}
}