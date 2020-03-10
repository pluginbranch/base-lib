<?php
namespace PluginBranch;

/**
 * Class Options Abstract
 *
 * @since   0.1.0
 *
 * @package PluginBranch
 */
abstract class Options_Abstract {
	/**
	 * Prefix used by all options that used this class.
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	const LIB_PREFIX = 'pb';

	/**
	 * Based on a key passed return the prefixed key for the Options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key Which key we are prefixing.
	 *
	 * @return string      Prefixed value for the key.
	 */
	public function get_key( $key ) {
		$key_parts = pb_array_flatten_by_dash( static::LIB_PREFIX, $this->get_prefix(), $key );
		$prefixed_key = implode( '-', $key_parts );
		return $prefixed_key;
	}

	/**
	 * Fetches an option from the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are looking for.
	 * @param mixed        $default  If we don't find a value for that key return a default.
	 *
	 * @return mixed       Which value was stored on the options table.
	 */
	public function get( $key, $default = false ) {
		$prefixed_key = $this->get_key( $key );
		return get_option( $prefixed_key, $default );
	}

	/**
	 * Fetches an option specific array index, using Utils\Arrays::get format from the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are looking for.
	 * @param array|string $index    Which index of the array we are looking for.
	 * @param mixed        $default  If we don't find a value for that key return a default.
	 *
	 * @return mixed       Which value was stored on the options table.
	 */
	public function get_index( $key, $index, $default = false ) {
		$prefixed_key = $this->get_key( $key );
		$option = get_option( $prefixed_key, [] );

		return pb( Utils\Arrays::class )->get( $option, $index, $default );
	}

	/**
	 * Adds an option specific option into the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are adding.
	 * @param mixed        $value    Which value will be passed to add_option.
	 * @param boolean      $autoload If this value should be auto-loaded.
	 *
	 * @return bool        If the add was successful or not.
	 */
	public function add( $key, $value, $autoload = true ) {
		$prefixed_key = $this->get_key( $key );
		return add_option( $prefixed_key, $value, $autoload );
	}

	/**
	 * Updates an option specific option into the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are updating.
	 * @param mixed        $value    Which value will be passed to update_option.
	 * @param boolean      $autoload If this value should be auto-loaded.
	 *
	 * @return bool        If the update was successful or not.
	 */
	public function update( $key, $value, $autoload = null ) {
		$prefixed_key = $this->get_key( $key );
		return update_option( $prefixed_key, $value, $autoload );
	}

	/**
	 * Updates an option specific array index, using Utils\Arrays::get format from the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are updating.
	 * @param array|string $index    Which index of the array we are updating.
	 * @param mixed        $value    Which value will be saved on the index we are updating.
	 * @param boolean      $autoload If this value should be auto-loaded.
	 *
	 * @return mixed       Which value was stored on the options table.
	 */
	public function update_index( $key, $index, $value, $autoload = null ) {
		$prefixed_key = $this->get_key( $key );

		$option_value = get_option( $prefixed_key, [] );
		$option_value = pb( Utils\Arrays::class )->set( $option_value, $index, $value );

		return update_option( $prefixed_key, $option_value, $autoload );
	}

	/**
	 * Delete an option specific option into the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key  Which option we are going to delete.
	 *
	 * @return bool        If the delete was successful or not.
	 */
	public function delete( $key ) {
		$prefixed_key = $this->get_key( $key );

		return delete_option( $prefixed_key );
	}

	/**
	 * Deletes an option specific array index, using Utils\Arrays::get format from the wp_options table.
	 *
	 * @since 0.1.0
	 *
	 * @param array|string $key      Which option we are updating.
	 * @param array|string $index    Which index of the array we are deleting.
	 *
	 * @return mixed       Which value was stored on the options table.
	 */
	public function delete_index( $key, $index ) {
		$prefixed_key = $this->get_key( $key );

		$option_value = get_option( $prefixed_key, [] );
		$option_value = pb( Utils\Arrays::class )->delete( $option_value, $index );

		return update_option( $prefixed_key, $option_value );
	}

	/**
	 * Abstract method to ensure no-one uses the the abstract without setting a plugin prefix.
	 *
	 * @since 0.1.0
	 *
	 * @return string  Normally the plugin prefix we will be storing options on.
	 */
	public abstract function get_prefix();
}