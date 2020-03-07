<?php
/**
 * Registers a class as a singleton.
 *
 * Each call to obtain an instance of this class made using the `pb( $slug )` function
 * will return the same instance; the instances are built just in time (if not passing an
 * object instance or callback function) and on the first request.
 * The container will call the class `__construct` method on the class (if not passing an object
 * or a callback function) and will try to automagically resolve dependencies.
 *
 * Example use:
 *
 *      pb_singleton( 'pb.admin', 'PluginBranch/Admin' );
 *
 *      // some code later...
 *
 *      // class is built here
 *      pb( 'pb.admin' )->doSomething();
 *
 * Need the class built immediately? Build it and register it:
 *
 *      pb_singleton( 'pb.admin', new PluginBranch/Admin() );
 *
 *      // some code later...
 *
 *      pb( 'pb.admin' )->doSomething();
 *
 * Need a very custom way to build the class? Register a callback:
 *
 *      pb_singleton( 'pb.admin', array( PluginBranch/Admin/Factory, 'make' ) );
 *
 *      // some code later...
 *
 *      pb( 'pb.admin' )->doSomething();
 *
 * Or register the methods that should be called on the object after its construction:
 *
 *      pb_singleton( 'pb.admin', 'PluginBranch/Admin', array( 'hook', 'register' ) );
 *
 *      // some code later...
 *
 *      // the `hook` and `register` methods will be called on the built instance.
 *      pb( 'pb.admin' )->doSomething();
 *
 * The class will be built only once (if passing the class name or a callback function), stored
 * and the same instance will be returned from that moment on.
 *
 * @since  0.1.0
 *
 * @param string                 $slug                The human-readable and catchy name of the class.
 * @param string|object|callable $class               The full class name or an instance of the class
 *                                                    or a callback that will return the instance of the class.
 * @param array                  $after_build_methods An array of methods that should be called on
 *                                                    the built object after the `__construct` method; the methods
 *                                                    will be called only once after the singleton instance
 *                                                    construction.
 */
function pb_singleton( $slug, $class, array $after_build_methods = null ) {
	PluginBranch\Container::init()->singleton( $slug, $class, $after_build_methods );
}

/**
 * Registers a class.
 *
 * Each call to obtain an instance of this class made using the `pb( $slug )` function
 * will return a new instance; the instances are built just in time (if not passing an
 * object instance, in that case it will work as a singleton) and on the first request.
 * The container will call the class `__construct` method on the class (if not passing an object
 * or a callback function) and will try to automagically resolve dependencies.
 *
 * Example use:
 *
 *      pb_register( 'pb.some', 'PluginBranch/Some' );
 *
 *      // some code later...
 *
 *      // class is built here
 *      $some_one = pb( 'pb.some' )->doSomething();
 *
 *      // $some_two !== $some_one
 *      $some_two = pb( 'pb.some' )->doSomething();
 *
 * Need the class built immediately? Build it and register it:
 *
 *      pb_register( 'pb.admin', new PluginBranch/Admin() );
 *
 *      // some code later...
 *
 *      // $some_two === $some_one
 *      // acts like a singleton
 *      $some_one = pb( 'pb.some' )->doSomething();
 *      $some_two = pb( 'pb.some' )->doSomething();
 *
 * Need a very custom way to build the class? Register a callback:
 *
 *      pb_register( 'pb.some', array( PluginBranch/Some_Factory, 'make' ) );
 *
 *      // some code later...
 *
 *      // $some_two !== $some_one
 *      $some_one = pb( 'pb.some' )->doSomething();
 *      $some_two = pb( 'pb.some' )->doSomething();
 *
 * Or register the methods that should be called on the object after its construction:
 *
 *      pb_singleton( 'pb.admin', 'PluginBranch/Admin', array( 'hook', 'register' ) );
 *
 *      // some code later...
 *
 *      // the `hook` and `register` methods will be called on the built instance.
 *      pb( 'pb.admin' )->doSomething();
 *
 * @since  0.1.0
 *
 * @param string                 $slug                The human-readable and catchy name of the class.
 * @param string|object|callable $class               The full class name or an instance of the class
 *                                                    or a callback that will return the instance of the class.
 * @param array                  $after_build_methods An array of methods that should be called on
 *                                                    the built object after the `__construct` method; the methods
 *                                                    will be called each time after the instance contstruction.
 */
function pb_register( $slug, $class, array $after_build_methods = null ) {
	PluginBranch\Container::init()->bind( $slug, $class, $after_build_methods );
}

/**
 * Returns a ready to use instance of the requested class.
 *
 * Example use:
 *
 *      pb_singleton( 'common.main', 'PluginBranch/Main');
 *
 *      // some code later...
 *
 *      pb( 'common.main' )->do_something();
 *
 * @since  0.1.0
 *
 * @param string|null $slug_or_class Either the slug of a binding previously registered using `pb_singleton` or
 *                                   `pb_register` or the full class name that should be automagically created or
 *                                   `null` to get the container instance itself.
 *
 * @return mixed|object|PluginBranch/Container The instance of the requested class. Please note that the cardinality of
 *                                       the class is controlled registering it as a singleton using `pb_singleton`
 *                                       or `pb_register`; if the `$slug_or_class` parameter is null then the
 *                                       container itself will be returned.
 */
function pb( $slug_or_class = null ) {
	$container = PluginBranch\Container::init();

	return null === $slug_or_class ? $container : $container->make( $slug_or_class );
}

/**
 * Registers a value under a slug in the container.
 *
 * Example use:
 *
 *      pb_set_var( 'pb.url', 'http://example.com' );
 *
 * @since  0.1.0
 *
 * @param string $slug  The human-readable and catchy name of the var.
 * @param mixed  $value The variable value.
 */
function pb_set_var( $slug, $value ) {
	$container = PluginBranch\Container::init();
	$container->setVar( $slug, $value );
}

/**
 * Returns the value of a registered variable.
 *
 * Example use:
 *
 *      pb_set_var( 'pb.url', 'http://example.com' );
 *
 *      $url = pb_get_var( 'pb.url' );
 *
 * @since  0.1.0
 *
 * @param string $slug    The slug of the variable registered using `pb_set_var`.
 * @param null   $default The value that should be returned if the variable slug
 *                        is not a registered one.
 *
 * @return mixed Either the registered value or the default value if the variable
 *               is not registered.
 */
function pb_get_var( $slug, $default = null ) {
	$container = PluginBranch\Container::init();

	try {
		$var = $container->getVar( $slug );
	} catch ( InvalidArgumentException $e ) {
		return $default;
	}

	return $var;
}

/**
 * Registers a service provider in the container.
 *
 * Service providers must implement the `tad_DI52_ServiceProviderInterface` interface or extend
 * the `tad_DI52_ServiceProvider` class.
 *
 * @see tad_DI52_ServiceProvider
 * @see tad_DI52_ServiceProviderInterface
 *
 * @since  0.1.0
 *
 * @param string $provider_class
 */
function pb_register_provider( $provider_class ) {
	$container = PluginBranch\Container::init();

	$container->register( $provider_class );
}

/**
 * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
 * bound to `$classOrInterface` and return the value of a call to `$method` method with the call arguments.
 *
 * @since  0.1.0
 *
 * @param string $slug                   A class or interface fully qualified name or a string slug.
 * @param string $method                 The method that should be called on the resolved implementation with the
 *                                       specified array arguments.
 *
 * @return mixed The called method return value.
 */
function pb_callback( $slug, $method ) {
	$container = PluginBranch\Container::init();

	return $container->callback( $slug, $method );
}