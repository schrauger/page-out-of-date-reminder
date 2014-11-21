<?php
/**
 * Created by PhpStorm.
 * User: Stephen Schrauger
 * Date: 11/19/14
 * Time: 2:14 PM
 */

/**
 * Class ood_WP_Post.
 *
 */
class ood_WP_Post {
	protected $wp_post; // the original WP_Post object, which contains standard info about the post
	protected $owner; // the calculated owner of the page. either the author, or the algorithmically chosen owner.
	protected $stale_threshold; // page is stale if not modified more than this number of days.

	/**
	 * @param $post_object_or_id The WordPress Post object or Post ID.
	 */
	public function __construct( $post_object_or_id ) {
		if ( $post_object_or_id instanceof WP_Post ) {
			// Object should already contain standard variables such as author, date modified, etc.
			$this->wp_post = $post_object_or_id;
		} elseif ( is_numeric( $post_object_or_id ) ) {
			// Post ID. Query the database for some more info.
			// @TODO grab info from WordPress database
		} else {
			// Something else was passed into the constructor. We can't do anything useful, so throw an error.
			throw new InvalidArgumentException( 'ood_WP_Post class only accepts WP_Post objects or Post ID integers.' );
		}

	}


	/**
	 * Returns TRUE if:
	 *    Page is not marked FROZEN
	 *  AND
	 *    (Current_date - Page_modified_date) > stale_threshold
	 *    OR
	 *    Page has broken links
	 */
	public function is_stale() {

	}

	/**
	 * Returns TRUE if (Current_date - Page_modified_date) > stale_threshold
	 */
	public function is_out_of_date() {

	}

	/**
	 * Returns the stale threshold for this particular post. It starts checking
	 * for specific variables and checks down to the most general default value.
	 */
	public function get_stale_threshold() {
		if (!$this->stale_threshold){
			// constructor loaded all values, and there was not a user-defined override for this post.
			$this->stale_threshold = $this->get_post_type_stale_threshold();
		}
		if (!$this->stale_threshold){
			// post-type was not defined. get overall default.
			$this->stale_threshold = $this->get_default_stale_threshold();
		}
		if (!$this->stale_threshold){
			// user failed to specify any default value. default to 365 days.
			$this->stale_threshold = 365;
		}
	}


	/**
	 * Returns the user-defined threshold for this post's post-type, stored in the plugin preferences.
	 * If not defined, returns null.
	 */
	protected function get_post_type_stale_threshold(){

	}

	/**
	 * Returns the user-defined threshold, stored in the plugin preferences.
	 * If not defined, returns null.
	 */
	protected function get_default_stale_threshold(){


	}
}