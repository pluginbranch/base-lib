<?php

namespace PluginBranch;

/**
 * Class Library
 *
 * @since   TBD
 *
 * @package PluginBranch
 */
class Library {
	/**
	 * Stores the absolute path from where Base lib was loaded from.
	 *
	 * @since 0.1.0
	 *
	 * @var string $base_path Which folder we will try to load things from in base Lib.
	 */
	protected $base_path;

	/**
	 * Stores the absolute path from where Base lib was loaded from.
	 *
	 * @since 0.1.0
	 *
	 * @var   array $base_path Which folder we will try to load things from in base Lib.
	 */
	protected $plugins = [];

	/**
	 * Sets the protected variable storing the base folder where our base lib was included from.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $lib_base_path Which folder Base Lib was loaded from.
	 *
	 * @return self  To allow Daisy Chain.
	 */
	public function set_base_path( $lib_base_path ) {
		$this->base_path = $lib_base_path;

		return $this;
	}

	/**
	 * Given a file name it will return the full include file path on the plugin folder.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $file_name File to be included.
	 *
	 * @return string  Absolute file path based on the file name.
	 */
	public function get_file_path( $file_name ) {
		$file_path = pb_path( $this->base_path, 'src', $file_name );

		return pb_ensure_extension( $file_path, 'php' );
	}

	/**
	 * Includes all files in an Array, allowing for an easy include of files for loading non class files.
	 *
	 * @since  0.1.0
	 *
	 * @param  array|string $files Files to be included
	 *
	 * @return self  To allow Daisy Chain
	 */
	public function include_file( $files = [] ) {
		$files = (array) $files;

		$paths = array_map( [ $this, 'get_file_path' ], $files );

		foreach ( $paths as $path ) {
			require $path;
		}

		// Allow Daisy chain
		return $this;
	}

	/**
	 * Sets all the registered plugins on this library.
	 *
	 * @since 0.1.0
	 *
	 * @param array $plugins Which plugins we will register.
	 *
	 * @return self Daisy-chain.
	 */
	public function set_plugins( $plugins ) {
		foreach ( $plugins as $plugin ) {
			$this->register_plugin( $plugin );
		}

		uasort( $this->plugins, 'pb_sort_by_priority' );

		return $this;
	}

	/**
	 * Gets all the registered plugins on this library.
	 *
	 * @since 0.1.0
	 *
	 * @param bool $should_order If we should trigger the ordering of by priority again.
	 *
	 * @return array  List of plugins.
	 */
	public function get_plugins( $should_order = true ) {
		if ( $should_order ) {
			uasort( $this->plugins, 'pb_sort_by_priority' );
		}

		/**
		 * Allow filtering of which plugins are registered.
		 *
		 * @since 0.1.0
		 *
		 * @param  array $plugins  List of plugins registered.
		 * @param  self  $library  Instance of the Library class.
		 */
		return apply_filters( 'pb_library_get_plugins', $this->plugins, $this );
	}

	/**
	 * Gets the registered value for a specific plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param object|string $slug_or_file Which file, slug or object you are looking for.
	 *
	 * @return object|null if valid returns the plugin object.
	 */
	public function get_plugin( $slug_or_file ) {
		if ( is_object( $slug_or_file ) && isset( $slug_or_file->slug ) ) {
			$slug_or_file = $slug_or_file->slug;
		}

		if ( isset( $this->plugins[ $slug_or_file ] ) ) {
			return $this->plugins[ $slug_or_file ];
		}

		$plugin = wp_filter_object_list( $this->plugins, [ 'file' => $slug_or_file ] );

		// If we got any return just the first one.
		if ( $plugin ) {
			return reset( $plugin );
		}

		return null;
	}

	/**
	 * Registering of a plugin on the Library class.
	 *
	 * @since 0.1.0
	 *
	 * @param object|array $plugin expecting an object or array to setup the plugin.
	 *
	 * @return bool        If the registering was successful.
	 */
	public function register_plugin( $plugin ) {
		if ( empty( $plugin ) ) {
			return false;
		}

		if ( is_array( $plugin ) ) {
			$plugin = (object) $plugin;
		}

		if ( ! is_object( $plugin ) ) {
			return false;
		}

		if ( ! isset( $plugin->file, $plugin->path, $plugin->slug, $plugin->label, $plugin->priority, $plugin->requirements ) ) {
			return false;
		}

		if ( ! is_dir( $plugin->path ) ) {
			return false;
		}

		if ( ! file_exists( $plugin->file ) ) {
			return false;
		}

		// Another plugin with this slug has already been registered.
		if ( $this->get_plugin( $plugin->slug ) ) {
			return false;
		}

		// This file has been already registered.
		if ( $this->get_plugin( $plugin->file ) ) {
			return false;
		}

		/**
		 * Allow filtering of which plugins are registered.
		 *
		 * @since 0.1.0
		 *
		 * @param  object $plugin   List of plugins registered.
		 * @param  self   $library  Instance of the Library class.
		 */
		$plugin = apply_filters( 'pb_library_register_plugin', $plugin, $this );

		// Once all params have been met, we save and return true.
		$this->plugins[ $plugin->slug ] = $plugin;

		return true;
	}

	/**
	 * Given a particular plugin triggers all the loading hooks and include the main loading file.
	 *
	 * @since  0.1.0
	 *
	 * @return void Main loading of a plugin has no return.
	 */
	public function load_plugin( $plugin ) {
		$plugin = $this->get_plugin( $plugin );
		if ( ! $plugin ) {
			return;
		}

		$plugin_load_file = pb_ensure_extension( pb_path( $plugin->path, 'src/functions/load' ) );

		/**
		 * Allow filtering of which is the load file for this plugin.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $plugin_load_file   Which file we will use to load the plugin.
		 * @param  object $plugin             List of plugins registered.
		 * @param  self   $library            Instance of the Library class.
		 */
		$plugin_load_file = apply_filters( 'pb_library_plugin_load_file', $plugin_load_file, $plugin, $this );

		/**
		 * Allow filtering of which is the load file for this plugin.
		 *
		 * @since 0.1.0
		 *
		 * @param  string $plugin_load_file   Which file we will use to load the plugin.
		 * @param  object $plugin             List of plugins registered.
		 * @param  self   $library            Instance of the Library class.
		 */
		$plugin_load_file = apply_filters( "pb_library_plugin_load_file:{$plugin->slug}", $plugin_load_file, $plugin, $this );

		if ( ! file_exists( $plugin_load_file ) ) {
			return;
		}

		/**
		 * Before loading a specific plugin we trigger an action hook.
		 *
		 * @since 0.1.0
		 *
		 * @param object $plugin  Base object used for loading with: `path`, `file`, `slug`, `label`, `priority` and `requirements`
		 * @param self   $library Instance of the library.
		 */
		do_action( "pb_library_before_load_plugin", $plugin, $this );

		/**
		 * Before loading a specific plugin we trigger an action hook that includes the plugin slug for more specific action hooking.
		 *
		 * @since 0.1.0
		 *
		 * @param object $plugin  Base object used for loading with: `path`, `file`, `slug`, `label`, `priority` and `requirements`
		 * @param self   $library Instance of the library.
		 */
		do_action( "pb_library_before_load_plugin:{$plugin->slug}", $plugin, $this );

		// Now load the plugin if the load file exists.
		require $plugin_load_file;

		/**
		 * After loading a specific plugin we trigger an action hook.
		 *
		 * @since 0.1.0
		 *
		 * @param object $plugin  Base object used for loading with: `path`, `file`, `slug`, `label`, `priority` and `requirements`
		 * @param self   $library Instance of the library.
		 */
		do_action( "pb_library_after_load_plugin", $this );

		/**
		 * After loading a specific plugin we trigger an action hook that includes the plugin slug for more specific action hooking.
		 *
		 * @since 0.1.0
		 *
		 * @param object $plugin  Base object used for loading with: `path`, `file`, `slug`, `label`, `priority` and `requirements`
		 * @param self   $library Instance of the library.
		 */
		do_action( "pb_library_after_load_plugin:{$plugin->slug}", $plugin, $this );

	}

	/**
	 * Load the all plugins that were properly registered.
	 *
	 * @uses self::load_plugin()
	 *
	 * @since  0.1.0
	 *
	 * @return self Daisy-chain.w
	 */
	public function load_plugins() {
		/**
		 * Before loading the plugins we trigger an action hook.
		 *
		 * @since 0.1.0
		 *
		 * @param self $library Instance of the library.
		 */
		do_action( 'pb_library_before_load_plugins', $this );

		foreach ( $this->get_plugins() as $plugin ) {
			$this->load_plugin( $plugin );
		}

		/**
		 * After loading the plugins we trigger an action hook.
		 *
		 * @since 0.1.0
		 *
		 * @param self $library Instance of the library.
		 */
		do_action( 'pb_library_after_load_plugins', $this );

		return $this;
	}
}