<?php
namespace PluginBranch;

class Template {
	/**
	 * The folders into which we will look for the template.
	 *
	 * @since 0.1.0
	 *
	 * @var   array
	 */
	protected $include_path = [];

	/**
	 * Base template for where to look for template.
	 *
	 * @since 0.1.0
	 *
	 * @var   array
	 */
	protected $base_path = [];

	/**
	 * Namespace used to locate files in the theme lookup.
	 *
	 * @since 0.1.0
	 *
	 * @var   array
	 */
	protected $namespace_path = [];

	/**
	 * The local context for templates, mutable on every self::render() call.
	 *
	 * @since 0.1.0
	 *
	 * @var   array
	 */
	protected $context = [];

	/**
	 * The global context for this instance of templates.
	 *
	 * @since 0.1.0
	 *
	 * @var   array
	 */
	protected $global = [];

	/**
	 * Allow changing if class will extract data from the local context.
	 *
	 * @since 0.1.0
	 *
	 * @var   boolean
	 */
	protected $context_extract = false;

	/**
	 * Should we use a lookup into the list of folders to try to find the file.
	 *
	 * @since 0.1.0
	 *
	 * @var   bool
	 */
	protected $theme_lookup = false;

	/**
	 * Create a class variable for the include path, to avoid conflicting with extract.
	 *
	 * @since 0.1.0
	 *
	 * @var   string
	 */
	protected $__current_file_path;

	/**
	 * Setup which is the base path for this template instance, will not be included in the path for hooks.
	 *
	 * @since  0.1.0
	 *
	 * @param  string|array  $base_path   Base path where this template class will load from, not used for hooks.
	 *
	 * @return self                       Allow daisy-chain.
	 */
	public function set_base_path( $base_path ) {
		$this->base_path = pb_normalize_path_parts( $base_path );

		return $this;
	}

	/**
	 * Returns the array of the base path we will look for templates for this instance, will not be included on path for hooks.
	 *
	 * @since  0.1.0
	 *
	 * @return array Current folder we are looking for templates.
	 */
	public function get_base_path() {
		/**
		 * Allows filtering of the base path for templates.
		 *
		 * @since 0.1.0
		 *
		 * @param array  base_path  Which is the base folder we will look for files in the plugin.
		 * @param self   $template  Current instance of the Template.
		 */
		return apply_filters( 'pb_template_base_path', $this->base_path, $this );
	}

	/**
	 * Setup which is the include path for this template instance, will be included in the path for hooks.
	 *
	 * @since  0.1.0
	 *
	 * @param  array|string   $include_path  Which folder we are going to look for templates, will be included for hooks.
	 *
	 * @return self                          Allow daisy-chain.
	 */
	public function set_include_path( $include_path ) {
		$this->include_path = pb_normalize_path_parts( $include_path );

		return $this;
	}

	/**
	 * Returns the array of the base path we will look for templates for this instance, will be included on path for hooks.
	 *
	 * @since  0.1.0
	 *
	 * @return array Current folder we are looking for templates.
	 */
	public function get_include_path() {
		/**
		 * Allows filtering of the base path for templates.
		 *
		 * @since 0.1.0
		 *
		 * @param array  $include_path Which is the base folder we will look for files in the plugin.
		 * @param self   $template     Current instance of the Template.
		 */
		return apply_filters( 'pb_template_base_path', $this->include_path, $this );
	}

	/**
	 * Setup which is the Namespace for the theme paths, will be included on the start of the hooks.
	 *
	 * @since  0.1.0
	 *
	 * @param  array|string   $namespace_path  Which folder we are going to look for templates, will be included for hooks.
	 *
	 * @return self                          Allow daisy-chain.
	 */
	public function set_namespace_path( $namespace_path ) {
		$this->namespace_path = pb_normalize_path_parts( $namespace_path );

		return $this;
	}

	/**
	 * Fetches the Namespace for the public paths, normally folders to look for in the theme's directory.
	 *
	 * @since  0.1.0
	 *
	 * @return array Namespace where we to look for templates.
	 */
	protected function get_namespace_path() {
		/**
		 * Allows filtering of the base path for templates
		 *
		 * @since  0.1.0
		 *
		 * @param array  namespace_path Which is the namespace we will look for files in the theme.
		 * @param self   $template      Current instance of the Template.
		 */
		return apply_filters( 'pb_template_namespace_path', $this->namespace_path, $this );
	}

	/**
	 * Sets if this template instance look for files inside of theme folders.
	 *
	 * @since  0.1.0
	 *
	 * @param  mixed $value Should we look for template files in theme folders.
	 *
	 * @return self         Allow daisy-chain.
	 */
	public function set_theme_lookup( $value = true ) {
		$this->theme_lookup = pb_is_truthy( $value );

		return $this;
	}

	/**
	 * Gets if this template instance look for files inside of theme folders.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Whether this instance will look for templates inside of theme folders.
	 */
	public function get_theme_lookup() {
		return $this->theme_lookup;
	}

	/**
	 * Configures if the class will extract context for template into local variables.
	 *
	 * @since  0.1.0
	 *
	 * @param  bool  $value  Should we extract context for templates.
	 *
	 * @return self          Allow daisy-chain.
	 */
	public function set_context_extract( $value = false ) {
		// Cast as bool and save
		$this->context_extract = pb_is_truthy( $value );

		return $this;
	}

	/**
	 * Gets if the class will extract context for template into local variables.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean Whether this instance will look for extract context variables.
	 */
	public function get_context_extract() {
		return $this->context_extract;
	}

	/**
	 * Sets a Index inside of the global or local context.
	 * Final to prevent extending the class when the `get` already exists on the child class.
	 *
	 * @since  0.1.0
	 *
	 * @see    Utils\Arrays::set
	 *
	 * @param  array    $index     Specify each nested index in order.
	 *                             Example: array( 'lvl1', 'lvl2' );
	 * @param  mixed    $default   Default value if the search finds nothing.
	 * @param  boolean  $is_local  Use the Local or Global context.
	 *
	 * @return mixed The value of the specified index or the default if not found.
	 */
	final public function get( $index, $default = null, $is_local = true ) {
		$context = $this->get_global_values();

		if ( true === $is_local ) {
			$context = $this->get_local_values();
		}

		/**
		 * Allows filtering the the getting of Context variables, also short circuiting.
		 * Following the same structure as WP Core.
		 *
		 * @since  0.1.0
		 *
		 * @param  mixed    $value     The value that will be filtered.
		 * @param  array    $index     Specify each nested index in order.
		 *                             Example: array( 'lvl1', 'lvl2' );
		 * @param  mixed    $default   Default value if the search finds nothing.
		 * @param  boolean  $is_local  Use the Local or Global context.
		 * @param  self     $template  Current instance of the Template.
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
			$this->context = pb( Utils\Arrays::class )->set( $this->context, $index, $value );

			return $this->context;
		}

		$this->global = pb( Utils\Arrays::class )->set( $this->global, $index, $value );

		return $this->global;
	}

	/**
	 * Merges local and global context, and saves it locally.
	 *
	 * @since  0.1.0
	 *
	 * @param  array  $context  Local Context array of data.
	 * @param  string $file     Complete path to include the PHP File.
	 * @param  array  $name     Template name.
	 *
	 * @return array
	 */
	public function merge_context( $context = [], $file = null, $name = null ) {
		// Allow for simple null usage as well as array() for nothing.
		if ( is_null( $context ) ) {
			$context = [];
		}

		// Applies new local context on top of Global + Previous local.
		$context = wp_parse_args( (array) $context, $this->get_values() );

		/**
		 * Allows filtering the Local context.
		 *
		 * @since  0.1.0
		 *
		 * @param array  $context   Local Context array of data.
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		$this->context = apply_filters( 'pb_template_context', $context, $file, $name, $this );

		return $this->context;
	}

	/**
	 * Get the list of theme related folders we will look up for the template.
	 *
	 * @since 4.11.0
	 *
	 * @return array
	 */
	protected function get_themes_lookup_list() {
		$folders = [];

		$folders['child-theme'] = [
			'id'       => 'child-theme',
			'priority' => 10,
			'path'     => pb_normalize_path_parts( STYLESHEETPATH ),
		];
		$folders['parent-theme'] = [
			'id'       => 'parent-theme',
			'priority' => 15,
			'path'     => pb_normalize_path_parts( TEMPLATEPATH ),
		];

		/**
		 * Allows filtering of the list of theme folders in which we will look for the template.
		 *
		 * @since  0.1.0
		 *
		 * @param  array   $folders     Complete path to include the base public folder.
		 * @param  string  $namespace   Loads the files from a specified folder from the themes.
		 * @param  self    $template    Current instance of the Template.
		 */
		$folders = (array) apply_filters( 'pb_template_theme_path_list', $folders, $this );

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
		$name = pb_normalize_path_parts( $name );

		// Build the File Path
		$file = pb_path( $this->get_base_path(), $this->get_include_path(), $name );

		if ( $this->get_theme_lookup() ) {
			$theme_folders = $this->get_themes_lookup_list();

			foreach ( $theme_folders as $folder ) {
				if ( empty( $folder['path'] ) ) {
					continue;
				}

				// Build the File Path
				$possible_file = pb_path( $folder['path'], $this->get_namespace_path(), $this->get_include_path(), $name );
				$possible_file = pb_ensure_extension( $possible_file, 'php' );

				// Skip non-existent files
				if ( file_exists( $possible_file ) ) {
					$file = $possible_file;
				}
			}
		}

		$file = pb_ensure_extension( $file, 'php' );

		/**
		 * A more specific filter that will include the template name.
		 *
		 * @since  0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		return apply_filters( 'pb_template_file', $file, $name, $this );
	}

	/**
	 * A very simple method to include a Template, allowing filtering and additions using hooks.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $name    Which file we are talking about including.
	 * @param array   $context Any context data you need to expose to this file.
	 * @param boolean $echo    If we should also print the Template.
	 *
	 * @return string|false Either the final content HTML or `false` if no template could be found.
	 */
	public function render( $name, $context = [], $echo = true ) {
		$file = $this->get_template_file( $name );

		// Specifically use '/' instead of constant here since it's for a hook and not actual files.
		$hook_name = implode( '/', pb_normalize_path_parts( $this->get_namespace_path(), $this->get_include_path(), $name ) );

		/**
		 * Allow users to filter the HTML before rendering.
		 *
		 * @since 0.1.0
		 *
		 * @param string $html      The initial HTML.
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		$pre_html = apply_filters( 'pb_template_pre_html', null, $file, $name, $this );

		/**
		 * Allow users to filter the HTML by the name before rendering.
		 *
		 * E.g.:
		 *    `pb_template_pre_html:events/blocks/parts/details`
		 *    `pb_template_pre_html:events/embed`
		 *    `pb_template_pre_html:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $html      The initial HTML.
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		$pre_html = apply_filters( "pb_template_pre_html:$hook_name", $pre_html, $file, $name, $this );

		if ( null !== $pre_html ) {
			if ( $echo ) {
				echo $pre_html;
			}

			return $pre_html;
		}

		ob_start();

		// Merges the local data passed to template to the global scope.
		$this->merge_context( $context, $file, $name );

		/**
		 * Fires an Action before including the template file.
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File
		 * @param array  $name      Template name
		 * @param self   $template  Current instance of the Template
		 */
		do_action( 'pb_template_before_include', $file, $name, $this );

		/**
		 * Fires an Action for a given template name before including the template file.
		 *
		 * E.g.:
		 *    `pb_template_before_include:events/blocks/parts/details`
		 *    `pb_template_before_include:events/embed`
		 *    `pb_template_before_include:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		do_action( "pb_template_before_include:$hook_name", $file, $name, $this );

		$this->safe_include( $file );

		/**
		 * Fires an Action after including the template file.
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		do_action( 'pb_template_after_include', $file, $name, $this );

		/**
		 * Fires an Action for a given template name after including the template file.
		 *
		 * E.g.:
		 *    `pb_template_after_include:events/blocks/parts/details`
		 *    `pb_template_after_include:events/embed`
		 *    `pb_template_after_include:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		do_action( "pb_template_after_include:$hook_name", $file, $name, $this );

		// Only fetch the contents after the action
		$html = ob_get_clean();

		/**
		 * Allow users to filter the final HTML.
		 *
		 * @since 0.1.0
		 *
		 * @param string $html      The final HTML.
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		$html = apply_filters( 'pb_template_html', $html, $file, $name, $this );

		/**
		 * Allow users to filter the final HTML by the name.
		 *
		 * E.g.:
		 *    `pb_template_html:events/blocks/parts/details`
		 *    `pb_template_html:events/embed`
		 *    `pb_template_html:tickets/login-to-purchase`
		 *
		 * @since 0.1.0
		 *
		 * @param string $html      The final HTML.
		 * @param string $file      Complete path to include the PHP File.
		 * @param array  $name      Template name.
		 * @param self   $template  Current instance of the Template.
		 */
		$html = apply_filters( "pb_template_html:$hook_name", $html, $file, $name, $this );

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Includes a give PHP inside of a safe context.
	 *
	 * This method is required to prevent template files messing with local variables used inside of the
	 * `self::render` method. Also shelters the template loading from any possible variables that could
	 * be overwritten by the context.
	 *
	 * @since 0.1.0
	 *
	 * @param string $file Which file will be included with safe context.
	 *
	 * @return void
	 */
	public function safe_include( $file ) {
		// We use this instance variable to prevent collisions.
		$this->__current_file_path = $file;
		unset( $file );

		// Only do this if really needed (by default it wont).
		if ( true === $this->get_context_extract() && ! empty( $this->context ) ) {
			// Make any provided variables available in the template variable scope.
			extract( $this->get_values() ); // @phpcs:ignore
		}

		include $this->__current_file_path;

		// After the include we reset the variable.
		unset( $this->__current_file_path );
	}

	/**
	 * Sets a number of values at the same time.
	 *
	 * @since 0.1.0
	 *
	 * @param array $values   An associative key/value array of the values to set.
	 * @param bool  $is_local Whether to set the values as global or local; defaults to local as the `set` method does.
	 *
	 * @see   Template::set()
	 */
	public function set_values( array $values = [], $is_local = true ) {
		foreach ( $values as $key => $value ) {
			$this->set( $key, $value, $is_local );
		}
	}

	/**
	 * Returns the Template global context.
	 *
	 * @since 0.1.0
	 *
	 * @return array An associative key/value array of the Template global context.
	 */
	public function get_global_values() {
		return $this->global;
	}

	/**
	 * Returns the Template local context.
	 *
	 * @since 0.1.0
	 *
	 * @return array An associative key/value array of the Template local context.
	 */
	public function get_local_values() {
		return $this->context;
	}

	/**
	 * Returns the Template global and local context values.
	 *
	 * Local values will override the template global context values.
	 *
	 * @since 0.1.0
	 *
	 * @return array An associative key/value array of the Template global and local context.
	 */
	public function get_values() {
		return array_merge( $this->get_global_values(), $this->get_local_values() );
	}
}
