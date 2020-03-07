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
 * @see Gym\Utils\Arrays->get()
 *
 * @param string|array $var
 * @param mixed        $default
 *
 * @return mixed
 */
function branch_get_request_var( $var, $default = null ) {
	$post_var = gym( 'utils.arrays' )->get( $_POST, $var );

	if ( null !== $post_var ) {
		return $post_var;
	}

	$query_var = gym( 'utils.arrays' )->get( $_GET, $var );

	if ( null !== $query_var ) {
		return $query_var;
	}

	return $default;
}
