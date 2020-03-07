<?php
namespace PluginBranch;

abstract class Plugin_Abstract {
	/**
	 * Stores the file plugin.
	 *
	 * @since 0.1.0
	 *
	 * @var   string  Path to the file that initialized the plugin.
	 */
	public $file;

	/**
	 * Stores the Path for the plugin.
	 *
	 * @since 0.1.0
	 *
	 * @var   string  Path for the plugin.
	 */
	public $path;

	/**
	 * Stores the folder the plugin currently live.
	 *
	 * @since 0.1.0
	 *
	 * @var   string  Which folder the plugin currently live.
	 */
	public $dir;

	/**
	 * Stores the URL base for the Plugin.
	 *
	 * @since 0.1.0
	 *
	 * @var   string  URL base for the Plugin.
	 */
	public $url;

	/**
	 * Initializes plugin variables for the plugin.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $file Which file initialized the plugin.
	 *
	 * @return self Allows daisy-chain.
	 */
	public function setup( $file ) {
		$this->file = $file;
		$this->path = trailingslashit( dirname( $this->file ) );
		$this->dir  = trailingslashit( basename( $this->path ) );
		$this->url  = str_replace( basename( $this->file ), '', plugins_url( basename( $this->file ), $this->file ) );

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
		$file_name = $this->path . '/' . $file_name;
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
