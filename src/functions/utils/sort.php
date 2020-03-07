<?php
/**
 * Sorting function based on Priority
 *
 * @since  0.1.0
 *
 * @param  object|array  $a  First Subject to compare
 * @param  object|array  $b  Second subject to compare
 *
 * @return int
 */
function pb_sort_by_priority( $a, $b ) {
	if ( is_array( $a ) ) {
		$a_priority = $a['priority'];
	} else {
		$a_priority = $a->priority;
	}

	if ( is_array( $b ) ) {
		$b_priority = $b['priority'];
	} else {
		$b_priority = $b->priority;
	}

	return (int) $a_priority === (int) $b_priority ? 0 : (int) $a_priority > (int) $b_priority;
}