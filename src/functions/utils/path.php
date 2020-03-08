<?php
/**
 * Normalize a set of paths and return it normalized as one array with each folder being an item.
 *
 * @since 0.1.0
 *
 * @return array The normalized path without any empty items.
 */
function pb_normalize_path_parts() {
	$arguments = func_get_args();
	$arguments = array_map( static function( $path ) {
		return explode( '/', $path );
	}, $arguments );

	$path = array_merge( ...$arguments );
	$path = array_filter( $path );
	
	return $path;
}

/**
 * Normalize a path and return it ready for inclusion.
 *
 * @since 0.1.0
 *
 * @return string      Return string ready for PHP inclusions.
 */
function pb_path() {
	$arguments = func_get_args();
	$path = pb_normalize_path_parts( $arguments );
	return implode( DIRECTORY_SEPARATOR, $path );
}

/**
 * Based on a path or url add a extension if not present.
 *
 * @since 0.1.0
 *
 * @param string|array $path       To which path we want to add the extension.
 * @param string       $extension  Which extension we should ensure.
 *
 * @return string      Return string with extension included.
 */
function pb_ensure_extension( $path, $extension = 'php' ) {
	$path = pb_path( $path );
	$extension_str    = sprintf( '.%s', $extension );
	$extension_length = count_chars( $extension_str );

	// If the file path doesnt have .php we append it.
	if ( $extension_str !== substr( $path, -$extension_length, $extension_length ) ) {
		$path .= $extension_str;
	}

	return $path;
}