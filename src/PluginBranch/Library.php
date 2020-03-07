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
		$file_name = $this->base_path . '/src/' . $file_name;
		$file_path = implode( DIRECTORY_SEPARATOR, explode( '/', $file_name ) );

		// If the file path doesnt have .php we append it.
		if ( '.php' !== substr( $file_path, -4, 4 ) ) {
			$file_path .= '.php';
		}
		return $file_path;
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
}