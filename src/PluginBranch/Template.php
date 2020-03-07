<?php
namespace PluginBranch;

class Template {
	/**
	 * The folders into we will look for the template
	 *
	 * @since  0.1.0
	 *
	 * @var array
	 */
	protected $folder = array();

	/**
	 * The origin class for the plugin where the template lives
	 *
	 * @since  0.1.0
	 *
	 * @var object
	 */
	public $origin;

	/**
	 * The local context for templates, mutable on every self::template() call
	 *
	 * @since  0.1.0
	 *
	 * @var array
	 */
	protected $context;

	/**
	 * The global context for this instance of templates
	 *
	 * @since  0.1.0
	 *
	 * @var array
	 */
	protected $global = array();

	/**
	 * Allow changing if class will extract data from the local context
	 *
	 * @since  0.1.0
	 *
	 * @var boolean
	 */
	protected $template_context_extract = false;

	/**
	 * Base template for where to look for template
	 *
	 * @since  0.1.0
	 *
	 * @var array
	 */
	protected $template_base_path;

	/**
	 * Should we use a lookup into the list of folders to try to find the file
	 *
	 * @since  0.1.0
	 *
	 * @var  bool
	 */
	protected $template_folder_lookup = false;

	/**
	 * Just an empty Construct
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function __construct() {

	}

	/**
	 * Configures the class origin plugin path
	 *
	 * @since  0.1.0
	 *
	 * @param  object|string  $origin   The base origin for the templates
	 *
	 * @return self
	 */
	public function set_template_origin( $origin = null ) {
		if ( empty( $origin ) ) {
			$origin = $this->origin;
		}

		if ( is_string( $origin ) ) {
			// Origin needs to be a class with a `instance` method
			if ( class_exists( $origin ) && method_exists( $origin, 'instance' ) ) {
				$origin = call_user_func( array( $origin, 'instance' ) );
			}
		}

		if ( empty( $origin->path ) && ! is_dir( $origin ) ) {
			throw new \InvalidArgumentException( 'Invalid Origin Class for Template Instance' );
		}

		if ( ! is_string( $origin ) ) {
			$this->origin = $origin;
			$this->template_base_path = untrailingslashit( $this->origin->path );
		} else {
			$this->template_base_path = untrailingslashit( (array) explode( '/', $origin ) );
		}

		return $this;
	}

	/**
	 * Configures the class with the base folder in relation to the Origin
	 *
	 * @since  0.1.0
	 *
	 * @param  array|string   $folder  Which folder we are going to look for templates
	 *
	 * @return self
	 */
	public function set_template_folder( $folder = null ) {
		// Allows configuring a already set class
		if ( ! isset( $folder ) ) {
			$folder = $this->folder;
		}

		// If Folder is String make it an Array
		if ( is_string( $folder ) ) {
			$folder = (array) explode( '/', $folder );
		}

		// Cast as Array and save
		$this->folder = (array) $folder;

		return $this;
	}

	/**
	 * Configures the class with the base folder in relation to the Origin
	 *
	 * @since  0.1.0
	 *
	 * @param  mixed   $value  Should we look for template files in the list of folders
	 *
	 * @return self
	 */
	public function set_template_folder_lookup( $value = true ) {
		$this->template_folder_lookup = pb_is_truthy( $value );

		return $this;
	}

	/**
	 * Configures the class global context
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $context  Default global Context
	 *
	 * @return self
	 */
	public function add_template_globals( $context = array() ) {
		// Cast as Array merge and save
		$this->global = wp_parse_args( (array) $context, $this->global );

		return $this;
	}

	/**
	 * Configures if the class will extract context for template
	 *
	 * @since  0.1.0
	 *
	 * @param  bool  $value  Should we extract context for templates
	 *
	 * @return self
	 */
	public function set_template_context_extract( $value = false ) {
		// Cast as bool and save
		$this->template_context_extract = pb_is_truthy( $value );

		return $this;
	}

	/**
	 * Sets a Index inside of the global or local context
	 * Final to prevent extending the class when the `get` already exists on the child class
	 *
	 * @since  0.1.0
	 *
	 * @see    Utils\Arrays::set
	 *
	 * @param  array    $index     Specify each nested index in order.
	 *                             Example: array( 'lvl1', 'lvl2' );
	 * @param  mixed    $default   Default value if the search finds nothing.
	 * @param  boolean  $is_local  Use the Local or Global context
	 *
	 * @return mixed The value of the specified index or the default if not found.
	 */
	final public function get( $index, $default = null, $is_local = true ) {
		$context = $this->global;

		if ( true === $is_local ) {
			$context = $this->context;
		}

		/**
		 * Allows filtering the the getting of Context variables, also short circuiting
		 * Following the same structure as WP Core
		 *
		 * @since  0.1.0
		 *
		 * @param  mixed    $value     The value that will be filtered
		 * @param  array    $index     Specify each nested index in order.
		 *                             Example: array( 'lvl1', 'lvl2' );
		 * @param  mixed    $default   Default value if the search finds nothing.
		 * @param  boolean  $is_local  Use the Local or Global context
		 * @param  self     $template  Current instance of the Template
		 */
		$value = apply_filters( 'pb_template_context_get', null, $index, $default, $is_local, $this );
		if ( null !== $value ) {
			return $value;
		}

		return pb( Utils\Arrays::class )->get( $context, $index, $default );
	}

	/**
	 * Sets a Index inside of the global or local context
	 * Final to prevent extending the class when the `set` already exists on the child class
	 *
	 * @since  0.1.0
	 *
	 * @see    Utils\Arrays::set
	 *
	 * @param  string|array  $index     To set a key nested multiple levels deep pass an array
	 *                                  specifying each key in order as a value.
	 *                                  Example: array( 'lvl1', 'lvl2', 'lvl3' );
	 * @param  mixed         $value     The value.
	 * @param  boolean       $is_local  Use the Local or Global context
	 *
	 * @return array Full array with the key set to the specified value.
	 */
	final public function set( $index, $value = null, $is_local = true ) {
		if ( true === $is_local ) {
			return pb( Utils\Arrays::class )->set( $this->context, $index, $value );
		} else {
			return pb( Utils\Arrays::class )->set( $this->global, $index, $value );
		}
	}

	/**
	 * Merges local and global context, and saves it locally
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $context  Local Context array of data
	 * @param  string $file     Complete path to include the PHP File
	 * @param  array  $name     Template name
	 *
	 * @return array
	 */
	public function merge_context( $context = array(), $file = null, $name = null ) {
		// Allow for simple null usage as well as array() for nothing
		if ( is_null( $context ) ) {
			$context = array();
		}

		// Applies local context on top of Global one
		$context = wp_parse_args( (array) $context, $this->global );

		/**
		 * Allows filtering the Local context
		 *
		 * @since  0.1.0
		 *
		 * @param array  $context   Local Context array of data
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		$this->context = apply_filters( 'pb_template_context', $context, $file, $name, $this );

		return $this->context;
	}

	/**
	 * Fetches the path for locating files in the Plugin Folder
	 *
	 * @since  0.1.0
	 *
	 * @return string
	 */
	protected function get_template_plugin_path() {
		// Craft the plugin Path
		$path = array_merge( (array) $this->template_base_path, $this->folder );

		// Implode to avoid Window Problems
		$path = implode( DIRECTORY_SEPARATOR, $path );

		/**
		 * Allows filtering of the base path for templates
		 *
		 * @since  4.7.20
		 *
		 * @param string $path      Complete path to include the base plugin folder
		 * @param self   $template  Current instance of the Template
		 */
		return apply_filters( 'pb_template_plugin_path', $path, $this );
	}

	/**
	 * Fetches the Namespace for the public paths, normaly folders to look for
	 * in the theme's directory.
	 *
	 * @since  0.1.0
	 *
	 * @return array
	 */
	protected function get_template_public_namespace() {
		$namespace = array(
			'gym',
		);

		if ( ! empty( $this->origin->template_namespace ) ) {
			$namespace[] = $this->origin->template_namespace;
		}

		/**
		 * Allows filtering of the base path for templates
		 *
		 * @since  0.1.0
		 *
		 * @param array  $namespace Which is the namespace we will look for files in the theme
		 * @param self   $template  Current instance of the Template
		 */
		return apply_filters( 'pb_template_public_namespace', $namespace, $this );
	}

	/**
	 * Fetches the path for locating files given a base folder normally theme related
	 *
	 * @since  0.1.0
	 *
	 * @param  mixed  $base  Base path to look into
	 *
	 * @return string
	 */
	protected function get_template_public_path( $base ) {
		// Craft the plugin Path
		$path = array_merge( (array) $base, (array) $this->get_template_public_namespace() );

		// Implode to avoid Window Problems
		$path = implode( DIRECTORY_SEPARATOR, $path );

		/**
		 * Allows filtering of the base path for templates
		 *
		 * @since  0.1.0
		 *
		 * @param string $path      Complete path to include the base public folder
		 * @param self   $template  Current instance of the Template
		 */
		return apply_filters( 'pb_template_public_path', $path, $this );
	}

	/**
	 * Fetches the folders in which we will look for a given file
	 *
	 * @since  0.1.0
	 *
	 * @return array
	 */
	protected function get_template_path_list() {
		$folders = array();

		// Only look into public folders if we tell to use folders
		if ( $this->template_folder_lookup ) {
			$folders[] = array(
				'id' => 'child-theme',
				'priority' => 10,
				'path' => $this->get_template_public_path( STYLESHEETPATH ),
			);
			$folders[] = array(
				'id' => 'parent-theme',
				'priority' => 15,
				'path' => $this->get_template_public_path( TEMPLATEPATH ),
			);
		}

		$folders[] = array(
			'id' => 'plugin',
			'priority' => 20,
			'path' => $this->get_template_plugin_path(),
		);

		/**
		 * Allows filtering of the list of folders in which we will look for the
		 * template given.
		 *
		 * @since  0.1.0
		 *
		 * @param  array  $folders   Complete path to include the base public folder
		 * @param  self   $template  Current instance of the Template
		 */
		$folders = apply_filters( 'pb_template_path_list', $folders, $this );

		uasort( $folders, 'pb_sort_by_priority' );

		return $folders;
	}

	/**
	 * Tries to locate the correct file we want to load based on the Template class
	 * configuration and it's list of folders
	 *
	 * @since  0.1.0
	 *
	 * @param  mixed  $name  File name we are looking for
	 *
	 * @return string
	 */
	public function get_template_file( $name ) {
		// If name is String make it an Array
		if ( is_string( $name ) ) {
			$name = (array) explode( '/', $name );
		}

		$folders = $this->get_template_path_list();

		foreach ( $folders as $folder ) {
			$folder['path'] = trim( $folder['path'] );
			if ( ! $folder['path'] ) {
				continue;
			}

			// Build the File Path
			$file = implode( DIRECTORY_SEPARATOR, array_merge( (array) $folder['path'], $name ) );

			// Append the Extension to the file path
			$file .= '.php';

			// Skip non-existent files
			if ( file_exists( $file ) ) {
				/**
				 * A more Specific Filter that will include the template name
				 *
				 * @since 0.1.0
				 *
				 * @param string $file      Complete path to include the PHP File
				 * @param array  $name      Template name
				 * @param self   $template  Current instance of the Template
				 */
				return apply_filters( 'pb_template_file', $file, $name, $this );
			}
		}

		// Couldn't find a template on the Stack
		return false;
	}

	/**
	 * A very simple method to include a Template, allowing filtering and additions using hooks.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $name     Which file we are talking about including
	 * @param  array   $context  Any context data you need to expose to this file
	 * @param  boolean $echo     If we should also print the Template
	 *
	 * @return string            Final Content HTML
	 */
	public function template( $name, $context = [], $echo = true ) {
		// If name is String make it an Array
		if ( is_string( $name ) ) {
			$name = (array) explode( '/', $name );
		}

		// Clean this Variable
		$name = array_map( 'sanitize_title_with_dashes', $name );

		if ( ! empty( $this->origin->template_namespace ) ) {
			$namespace = array_merge( (array) $this->origin->template_namespace, $name );
		} else {
			$namespace = $name;
		}

		// Setup the Hook name
		$hook_name = implode( '/', $namespace );

		// Check if the file exists
		$file = $this->get_template_file( $name );

		// Check if it's a valid variable
		if ( ! $file ) {
			return false;
		}

		// Before we load the file we check if it exists
		if ( ! file_exists( $file ) ) {
			return false;
		}

		ob_start();

		// Merges the local data passed to template to the global scope
		$this->merge_context( $context, $file, $name );

		/**
		 * Fires an Action before including the template file
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		do_action( 'pb_template_before_include', $file, $name, $this );

		/**
		 * Fires an Action for a given template name before including the template file
		 *
		 * E.g.:
		 *    `pb_template_before_include:events/blocks/parts/details`
		 *    `pb_template_before_include:events/embed`
		 *    `pb_template_before_include:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		do_action( "pb_template_before_include:$hook_name", $file, $name, $this );

		// Only do this if really needed (by default it wont)
		if ( true === $this->template_context_extract && ! empty( $this->context ) ) {
			// We don't allow Extrating of a variable called $name
			if ( isset( $this->context['name'] ) ) {
				unset( $this->context['name'] );
			}

			// We don't allow Extrating of a variable called $file
			if ( isset( $this->context['file'] ) ) {
				unset( $this->context['file'] );
			}

			// Make any provided variables available in the template variable scope
			extract( $this->context ); // @codingStandardsIgnoreLine
		}

		include $file;

		/**
		 * Fires an Action after including the template file
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		do_action( 'pb_template_after_include', $file, $name, $this );

		/**
		 * Fires an Action for a given template name after including the template file
		 *
		 * E.g.:
		 *    `pb_template_after_include:events/blocks/parts/details`
		 *    `pb_template_after_include:events/embed`
		 *    `pb_template_after_include:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		do_action( "pb_template_after_include:$hook_name", $file, $name, $this );

		// Only fetch the contents after the action
		$html = ob_get_clean();

		/**
		 * Allow users to filter the final HTML
		 *
		 * @since 0.1.0
		 *
		 * @param string $html      The final HTML
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		$html = apply_filters( 'pb_template_html', $html, $file, $name, $this );

		/**
		 * Allow users to filter the final HTML by the name
		 *
		 * E.g.:
		 *    `pb_template_html:wod/item/parts/details`
		 *    `pb_template_html:wod/embed`
		 *
		 * @since  0.1.0
		 *
		 * @param string $html      The final HTML
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		$html = apply_filters( "pb_template_html:$hook_name", $html, $file, $name, $this );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}
}
