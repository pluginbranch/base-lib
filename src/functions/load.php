<?php
namespace PluginBranch;
/**
 * @var \stdClass $current_plugin  Before this file is required we setup that variable.
 * @var string    $base_lib_folder Before this file is required we setup that variable.
 */

// Before we configure we do our stuff we load the Composer autoloader.
require $current_plugin->path . str_replace( DIRECTORY_SEPARATOR, '/', "{$base_lib_folder}/vendor/autoload.php" );

// Load the di52 Container Class and Functions.
Container::init();

// Load the container functions.
require_once $current_plugin->path . str_replace( DIRECTORY_SEPARATOR, '/', "{$base_lib_folder}/src/functions/container.php" );
require_once $current_plugin->path . str_replace( DIRECTORY_SEPARATOR, '/', "{$base_lib_folder}/src/functions/utils/path.php" );

/**
 * After this Point we only use di52 to load any classes
 *
 * @since 0.1.0
 */
pb_singleton( Library::class, Library::class );

pb( Library::class )->set_base_path( $current_plugin->path . $base_lib_folder );

pb( Library::class )->include_file( [
	'functions/utils/sort',
	'functions/utils/conditional',
	'functions/utils/arrays',
] );

// Register base lib Service Provider
pb_register_provider( Provider::class  );

