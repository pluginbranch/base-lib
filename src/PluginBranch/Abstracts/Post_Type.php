<?php
namespace PluginBranch\Abstracts;
use Gym\Rewrite;

abstract class Post_Type {

	abstract public function get_slug();

	abstract public function get_structure();

	abstract public function get_singular();

	abstract public function get_plural();

	abstract public function get_register_args();

	abstract public function register_rewrite_bases( array $bases );

	abstract public function register_rewrite_rules( Rewrite $rewrite );

	/**
	 * Setup all the WP hooks to setup this Post Type
	 *
	 * @since  0.1.0
	 *
	 * @return void
	 */
	public function hook() {
		//add a filter to fix the url for this post type
		add_filter( 'post_type_link', [ $this, 'filter_post_type_link' ], 10, 4 );
		add_filter( 'post_type_archive_link', [ $this, 'filter_post_type_archive_link' ], 10, 2 );
		add_filter( 'wp_unique_post_slug', [ $this, 'filter_unique_slug' ], 10, 6 );

		add_action( 'init', [ $this, 'register' ] );
		add_action( 'branch_pre_rewrite', [ $this, 'register_rewrite_rules' ] );
		add_filter( 'branch_rewrite_base_slugs', [ $this, 'register_rewrite_bases' ] );
	}

	/**
	 * Get magic method to make it easier to fetch certain parts of the Post Type
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $name  Name of the Prop
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$exists = [
			'object',
			'permalink',
			'slug',
			'singular',
			'plural',
		];

		if ( ! in_array( $name, $exists ) ) {
			return null;
		}

		if ( 'object' === $name ) {
			return $this->get_object();
		}

		if ( 'permalink' === $name ) {
			return $this->get_permalink_arguments();
		}

		if ( 'slug' === $name ) {
			return $this->get_slug();
		}

		if ( 'singular' === $name ) {
			return $this->get_singular();
		}

		if ( 'plural' === $name ) {
			return $this->get_plural();
		}
	}

	/**
	 * Register the post type with WordPress
	 *
	 * @since  0.1.0
	 *
	 * @return object
	 */
	public function register() {
		$args = $this->get_register_args();

		// Make sure the rewrite settings for the post type are set to false to prevent interference
		$args['rewrite'] = false;

		/**
		 * Allows direct filtering of the arguments for the Workout Post Type
		 * before registering into the WordPress environment
		 *
		 * @since TBD
		 *
		 * @param array  $args      Arguments to register the Workout Post Type
		 * @param self   $post_type Instance of the manager class for this Post Type
		 */
		$args = apply_filters( 'branch_post_type_arguments', $args, $this );

		// register the post type and get the returned args
		return register_post_type( $this->slug, $args );
	}

	/**
	 * Returns the permalink arguments that we use to setup the Post links
	 *
	 * @since  0.1.0
	 *
	 * @return object
	 */
	public function get_permalink_arguments() {
		$arguments = (object) [
			'structure' => $this->get_structure(),
			'singular' => $this->get_singular(),
			'plural' => $this->get_plural(),
		];

		// Builds the permalink for the single page
		$arguments->single = trailingslashit( $arguments->singular ) . $arguments->structure;
		$arguments->archive = trailingslashit( $arguments->plural ) . $arguments->structure;

		return $arguments;
	}

	/**
	 * Returns the WordPress post type object handled by this Class
	 *
	 * @since  0.1.0
	 *
	 * @return object
	 */
	public function get_object() {
		return get_post_type_object( $this->get_slug() );
	}

	/**
	 * Filters the Unique Slug from WP to allow multiple items with the same name as long
	 * as they dont conflict permalinks
	 *
	 * @since  0.1.0
	 *
	 * @param string $slug        The desired slug (post_name).
	 * @param int    $post_ID     Post ID.
	 * @param string $post_status No uniqueness checks are made if the post is still draft or pending.
	 * @param string $post_type   Post type.
	 * @param int    $post_parent Post parent ID.
	 *
	 * @return string Unique slug for the post, based on $post_name (with a -1, -2, etc. suffix)
	 */
	public function filter_unique_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		global $wpdb;
		if ( $this->slug !== $post_type ) {
			return $slug;
		}

		$check_sql = "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s AND ID != %d";
		$posts = $wpdb->get_col( $wpdb->prepare( $check_sql, $original_slug, $post_type, $post_ID ) );
		$posts = array_map( 'absint', $posts );
		$links = array_map( 'get_post_permalink', $posts );

		$post = (object) get_post( $post_ID );

		$post->post_name = $original_slug;
		$post->post_date = branch_get_request_var( 'post_date', $post->post_date );
		$post->post_date_gmt = branch_get_request_var( 'post_date_gmt', $post->post_date_gmt );

		$post_link = $this->replace_permalink( $this->permalink->single, $post );

		if ( ! in_array( $post_link, $links ) ) {
			return $original_slug;
		}

		return $slug;
	}

	/**
	 * Filters the Post Type archive link to have the correct prefix.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $permalink
	 * @param  string $post_type
	 *
	 * @return string
	 */
	public function filter_post_type_archive_link( $link, $post_type ) {
		if ( $this->slug !== $post_type ) {
			return $link;
		}

		if ( empty( $link ) ) {
			return $link;
		}

		return home_url( user_trailingslashit( $this->permalink->plural, 'post_type_archive' ) );
	}

	/**
	 * Does a string replacement on a given permalink structure and which post
	 * we are dealing with, note we are not requiring WP_Post object due to it
	 * been a final object, so we allow the stdClass or WP_Post.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $permalink
	 * @param  object $post
	 *
	 * @return string
	 */
	public function replace_permalink( $permalink, $post ) {
		$rewritecode = [
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%post_id%',
			'%author%',
			'%' . $this->object->query_var . '%',
		];

		$author = '';
		if ( strpos( $this->permalink->structure, '%author%' ) !== false ) {
			$authordata = get_userdata( $post->post_author );
			$author = $authordata->user_nicename;
		}

		$unixtime = strtotime( $post->post_date );
		$date = explode( ' ', date( 'Y m d H i s', $unixtime ) );
		$rewritereplace = [
			$date[0],
			$date[1],
			$date[2],
			$date[3],
			$date[4],
			$date[5],
			$post->ID,
			$author,
		 	$post->post_name,
		];
		$permalink = str_replace( $rewritecode, $rewritereplace, '/' . $this->permalink->archive );
		$permalink = user_trailingslashit( home_url( $permalink ) );

		return $permalink;
	}

	/**
	 * Filter to turn the links for this post type into ones that match our permalink structure
	 *
	 * @since  0.1.0
	 *
	 * @param  string $permalink
	 * @param  object $post
	 *
	 * @return string
	 */
	public function filter_post_type_link( $permalink, $post ) {
		if ( $this->slug !== $post->post_type ) {
			return $permalink;
		}

		if ( empty( $permalink ) ) {
			return $permalink;
		}

		if ( in_array( $post->post_status, [ 'draft', 'pending', 'auto-draft' ] ) ) {
			return $permalink;
		}

		return  $this->replace_permalink( $permalink, $post );
	}
}
