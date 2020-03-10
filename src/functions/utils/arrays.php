<?php
/**
 * Tests to see if the requested variable is set either as a post field or as a URL
 * param and returns the value if so.
 *
 * Post data takes priority over fields passed in the URL query. If the field is not
 * set then $default (null unless a different value is specified) will be returned.
 *
 * The variable being tested for can be an array if you wish to find a nested value.
 *
 * @see PluginBranch\Utils\Arrays->get()
 *
 * @param string|array $var
 * @param mixed        $default
 *
 * @return mixed
 */
function pb_get_request_var( $var, $default = null ) {
	$post_var = pb( PluginBranch\Utils\Arrays::class )->get( $_POST, $var );

	if ( null !== $post_var ) {
		return $post_var;
	}

	$query_var = pb( PluginBranch\Utils\Arrays::class )->get( $_GET, $var );

	if ( null !== $query_var ) {
		return $query_var;
	}

	return $default;
}

/**
 * Flattens a set of arguments splitting strings by dash.
 *
 * @since 0.1.0
 *
 * @return array The flattened array with any strings divided by dash.
 */
function pb_array_flatten_by_dash() {
	$arguments = func_get_args();
	$arguments = pb_array_flatten( $arguments );
	$arguments = array_map( static function( $path ) {
		return explode( '-', $path );
	}, $arguments );

	$flat = pb_array_flatten( $arguments );
	$flat = array_filter( $flat );

	return $flat;
}

/**
 * Flattens a set of arguments splitting strings by underscore.
 *
 * @since 0.1.0
 *
 * @return array The flattened array with any strings divided by underscore.
 */
function pb_array_flatten_by_underscore() {
	$arguments = func_get_args();
	$arguments = pb_array_flatten( $arguments );
	$arguments = array_map( static function( $path ) {
		return explode( '_', $path );
	}, $arguments );

	$flat = pb_array_flatten( $arguments );
	$flat = array_filter( $flat );

	return $flat;
}

/**
 * Flattens a set of arguments splitting strings by dot.
 *
 * @since 0.1.0
 *
 * @return array The flattened array with any strings divided by dot.
 */
function pb_array_flatten_by_dot() {
	$arguments = func_get_args();
	$arguments = pb_array_flatten( $arguments );
	$arguments = array_map( static function( $path ) {
		return explode( '.', $path );
	}, $arguments );

	$flat = pb_array_flatten( $arguments );
	$flat = array_filter( $flat );

	return $flat;
}

/**
 * Flattens an multidimensional array into a flat array.
 *
 * @since 0.1.0
 *
 * @return array The flattened array.
 */
function pb_array_flatten() {
	$result = [];
	$array  = func_get_args();

	foreach ( $array as $key => $value ) {
		if ( is_array( $value ) ) {
			$result = array_merge( $result, pb_array_flatten( ... $value ) );
		} else {
			$result = array_merge( $result, [ $key => $value ] );
		}
	}

	return $result;
}

