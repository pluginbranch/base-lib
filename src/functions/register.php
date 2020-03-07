<?php
/**
 * Registers a plugin inside of the base lib, which will only pull the latest version of
 * base lib available when loading the plugin.
 *
 * @since  0.1.0
 *
 * @param  string $file         The base plugin file that will be loaded.
 * @param  string $label        Name of the plugin which will be used to display the initial error message for unmet requirements.
 * @param  int    $priority     In which priority internally we will load this plugin.
 * @param  array  $requirements Requirements for the plugin, PHP, WordPress and eventually other internal pieces.
 *
 * @return void   Registering of plugins needs no return.
 */
function pb_register_plugin( $file, $label, $priority = 10, array $requirements = [] ) {
	if ( ! isset( $GLOBALS['__plugin_branch_plugins'] )  || ! is_array( $GLOBALS['__plugin_branch_plugins'] ) ) {
		$GLOBALS['__plugin_branch_plugins'] = [];
	}

	$GLOBALS['__plugin_branch_plugins'][] = (object) [
		'file'         => $file,
		'label'        => $label,
		'priority'     => absint( $priority ),
		'requirements' => $requirements,
	];

	// If this is the first time we registered a plugin trigger the load on plugins_loaded.
	if ( ! has_action( 'plugins_loaded', 'pb_load_plugins' ) ) {
		add_action( 'plugins_loaded', 'pb_load_plugins', 15 );
	}
}

/**
 * Gets the list of plugins that were registered for loading.
 *
 * @since  0.1.0
 *
 * @return array  List the registered plugins in our base libs variable.
 */
function pb_get_plugins() {
	return $GLOBALS['__plugin_branch_plugins'];
}

/**
 * Hooks to plugins_loaded and does all the proper checks to load the base lib latest version as well
 * as all the plugins the properly have loading files.w
 *
 * @since  0.1.0
 *
 * @return void Load the plugin loaders, no return.
 */
function pb_load_plugins() {
	static $has_loaded = false;

	// prevent double loading.
	if ( $has_loaded ) {
		return;
	}

	$plugins = pb_get_plugins();

	// Bail when no plugins are present.
	if ( empty( $plugins ) ) {
		return;
	}

	// Bail when no plugins var is not an array.
	if ( ! is_array( $plugins ) ) {
		return;
	}

	$base_lib_folder = 'pluginbranch';
	$base_lib_json_relative_path = implode( DIRECTORY_SEPARATOR, [ $base_lib_folder, 'plugin.json' ] );
	$current_base_lib = false;
	$current_plugin = false;

	foreach ( $plugins as $index => $plugin ) {
		// Bail loading when the base plugin doesnt exist.
		if ( ! file_exists( $plugin->file ) ) {
			unset( $plugins[ $index ] );
			continue;
		}

		$plugin_path = plugin_dir_path( $plugin->file );
		$plugin->path = $plugin_path;

		$base_lib_json = $plugin_path . $base_lib_json_relative_path;

		if ( ! file_exists( $base_lib_json ) ) {
			unset( $plugins[ $index ] );
			continue;
		}

		$base_lib_json_data = file_get_contents( $base_lib_json );

		if ( ! $base_lib_json_data ) {
			unset( $plugins[ $index ] );
			continue;
		}

		$base_lib_data = json_decode( $base_lib_json_data );

		if ( ! $base_lib_data ) {
			unset( $plugins[ $index ] );
			continue;
		}

		if ( ! $current_base_lib ) {
			$current_base_lib = $base_lib_data;
			$current_plugin = $plugin;
			continue;
		}

		if ( version_compare( $base_lib_data['version'], $current_base_lib['version'] , '<=' ) ) {
			continue;
		}

		$current_base_lib = $base_lib_data;
		$current_plugin = $plugin;
	}

	//  If we dont have a current plugin we bail.
	if ( ! $current_plugin ) {
		return;
	}

	$base_lib_load_file = $current_plugin->path . str_replace( DIRECTORY_SEPARATOR, '/', "{$base_lib_folder}/src/functions/load.php" );

	if ( ! file_exists( $base_lib_load_file ) ) {
		return;
	}

	// After we compared all Base libs available load the latest version.
	require $base_lib_load_file;

	// Sort the remainder of the plugins by priority.
	uasort( $plugins, 'pb_sort_by_priority' );

	// After ordering plugins we load by priority.
	foreach ( $plugins as $plugin ) {
		$plugin_load_file = $plugin->path . str_replace( DIRECTORY_SEPARATOR, '/', "src/functions/load.php" );

		if ( ! file_exists( $plugin_load_file ) ) {
			return;
		}

		// Now load the plugin if the load file exists.
		require $plugin_load_file;
	}
	$has_loaded = true;
}

/**
 * To allow internationalization for the errors strings the text domain is
 * loaded in a 5.2 way, no Fatal Errors, only a message to the user.
 *
 * @since  0.1.0
 *
 * @return boolean

function _online_now_l10n() {
	// Doing that to use the real folder where the plugin is living, not a static string
	$plugin_folder = str_replace( DIRECTORY_SEPARATOR . basename( __ONLINENOW_FILE__ ), '', plugin_basename( __ONLINENOW_FILE__ ) );

	return load_plugin_textdomain( 'online-now', false, $plugin_folder . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR );
}
add_action( 'plugins_loaded', '_online_now_l10n' );

/**
 * Version compare to PHP 7.0, so we can use Namespaces, anonymous functions
 * and a lot of packages require 7.0.
if ( version_compare( PHP_VERSION, '7.0', '<' ) ) {
	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// If we hit this point and the plugin isn't active we throw the message and exit
		if ( ! is_plugin_active( plugin_basename( __ONLINENOW_FILE__ ) ) ) {
			wp_print_styles( 'open-sans' );
			echo "<style>body{margin: 0 2px;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.5em;}</style>";
			echo wp_kses_post( __( '<b>Online Now</b> requires PHP 7.0 or higher, and the plugin has now disabled itself.', 'online-now' ) ) .
				'<br />' .
				esc_attr__( 'To allow better control over dates, advanced security improvements and performance gain.', 'online-now' ) .
				'<br />' .
				esc_attr__( 'Contact your Hosting or your system administrator and ask for this Upgrade to version 7.0 of PHP.', 'online-now' );
			exit();
		}

		deactivate_plugins( __ONLINENOW_FILE__ );
	}
} else {
	require_once plugin_dir_path( __ONLINENOW_FILE__ ) . str_replace( DIRECTORY_SEPARATOR, '/', 'src/functions/load.php' );
}
*/