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

	/**
	 * The max number of most recent revisions to consider when calculating the page owner.
	 * It doesn't matter if this is even or odd. Must be at least 1.
	 */
	const REVISION_HISTORY_ITEMS_TO_CONSIDER = '5';

	/**
	 * @var WP_Post $wp_post
	 */
	protected $wp_post; // the original WP_Post object, which contains standard info about the post

	/**
	 * @var WP_User $owner
	 */
	protected $owner; // the calculated owner of the page. either the author, or the algorithmically chosen owner.

	/**
	 * @var int $stale_threshold
	 */
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

	/**
	 * Returns the user who should be notified of the stale page.
	 * This can be set manually on a page-by-page basis, but most pages will have their
	 * owner calculated based on the user(s) who made the most recent edits.
	 * @return WP_User A WP_User object with the user's name and email.
	 */
	public function get_owner(){
		/*
		 * 1. Check $owner variable. If set, just return that.
		 */
		if ($this->owner){
			return $this->owner;
		}
		/*
		 * 2. Check manually set owner. If set, set the $owner variable and return that owner.
		 */
		$this->owner = $this->get_manual_owner();
		if ($this->owner) {
			return $this->owner;
		}
		/*
		 * 3. Call calculate_owner. Set the $owner variable and return it.
		 */
		$this->owner = $this->calculate_owner();
		return $this->owner;
	}

	/**
	 * Will check the database to see if a manual override owner is set. If so, it will return
	 * that value.
	 * If not set, it will return null.
	 * @return WP_User
	 */
	protected function get_manual_owner(){
		/*
		 * 1. Check db for manual. If exists, get wp_user from database id and return the user.
		 */
	}

	protected function calculate_owner(){
		/*
		 * This would be easy with a direct sql command. Just select the last 5, sorted by date. Then
		 * select the id and count, group by id and ordered by count then date. If any counts are equal,
		 * the date would prioritize. The first row in this query would be our calculated owner.
		 * SELECT access, count(access) as 'count' from (SELECT access, last_modified FROM postfix.virtual_recipient_email_access  order by last_modified desc  limit 5) as `mytable` group by access order by count desc, last_modified desc;
		 */
		/*
		 * 1. Get post revisions. Limit to the most recent.
		 */
		$revisions = wp_get_post_revisions($this->wp_post->ID, array('limit' => self::REVISION_HISTORY_ITEMS_TO_CONSIDER));

		/*
		 * 2. Count each distinct author.
		 */
		$array_authors = array();
		$array_authors_date = array();
		foreach ($revisions as $revision) {
			/* @var WP_Post $revision */
			$author_id = $revision->post_author;
			if (array_key_exists($author_id, $array_authors)){
				$array_authors[$author_id] += 1;
			} else {
				$array_authors[$author_id] = 1;
			}

			array_push($array_authors_date, $author_id);
		}

		$array_winners = array();
		/*
		 * 3. Check for winner and return winner. Continue if tie.
		 */
		arsort($array_authors); // highest post count is now first
		$winning_post_count = $array_authors[0];
		foreach ($array_authors as $author => $count){
			if ($count == $winning_post_count){
				array_push($array_winners, $author);
			}
		}
		if (count($array_winners) == 1){
			// no ties, only one highest edit count. clear winner.
			// Note: this may not be the most recent editor. The whole point
			// is that if a post is edited by one person 4 times, and then
			// someone else makes an edit, the page owner is probably
			// still that other person.
			return $array_winners[0];
		}

		/*
		 * 4. In case of a tie, get the index of each tie contestant within the array_authors_date array.
		 * The contestant with the lowest key has the earliest edit date and is declared the winner.
		 */

		// Since get_post_revisions defaults to most recent first, our array is already sorted by date.
		// Therefore, if we have a tie, we can go search for the first instance in our array to find the winner.

		$winning_number = self::REVISION_HISTORY_ITEMS_TO_CONSIDER;
		// since arrays are 0-based, the maximum index possible is REVISION_HISTORY_ITEMS_TO_CONSIDER - 1.
		// that is only possible if all REVISION_HISTORY_ITEMS_TO_CONSIDER edits were done by different people.

		if ($winning_number == 1){
			// all X revisions were done by different people. no need to loop to find the winner; we don't need to weed
			// out anyone. just return the most recent edit.
			return $array_authors_date[0];
		}

		// this scenario is where there are 3 users editing. one edits once, the other two edit two times.
		// Loop through just the winners (those with two edits), then find the most recent of the two editors
		// by looking at the $array_authors_date array, which stores the author id sorted by revision date.

		foreach ($array_winners as $author){
			// don't use index/key of $array_winners. we just want the author id.
			$author_number = array_search($author, $array_authors_date); // first matching key
			if ($author_number < $winning_number){
				$winning_number = $author_number; // new lowest index in the date array.
			}
		}

		// $winning_number now has the index value of the most recent author of the page (that has the
		// highest or tied-for-highest post count within the last X number of edits)

		return $array_authors_date[$winning_number];

	}
}