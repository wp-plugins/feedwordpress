<?php
require_once(dirname(__FILE__) . '/syndicatedlink.class.php');

class MagpieMockLink extends SyndicatedLink {
	var $url;

	function MagpieMockLink ($rss, $url) {
		$this->link = $rss;
		$this->magpie = $rss;
		$this->url = $url;
		$this->id = -1;
		$this->settings = array(
			'unfamiliar category' => 'default',
			
		);
	} /* function MagpieMockLink::MagpieMockLink () */

	function poll ($crash_ts = NULL) {
		// Do nothing but update copy of feed
		$this->simplepie = FeedWordPress::fetch($this->url);
		$this->magpie = new MagpieFromSimplePie($this->simplepie);

		$this->link = $this->magpie;
	} /* function MagpieMockLink::poll () */

	function uri () {
		return $this->url;
	} /* function MagpieMockLink::uri() */

	function homepage () {
		return (!is_wp_error($this->simplepie) ? $this->simplepie->get_link() : null);
	} /* function MagpieMockLink::homepage () */
} /* class MagpieMockLink */


