<?php
$GLOBALS['fwp_credentials'] = NULL;

class FeedWordPress_File extends WP_SimplePie_File {
	function FeedWordPress_File ($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		self::__construct($url, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
	}
	
	function __construct ($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false) {
		global $fwp_oLinks;
		global $wp_version;

		$source = NULL;
		if (isset($fwp_oLinks[$url])) :
			$source = $fwp_oLinks[$url];
		endif;
		
		$this->url = $url;
		$this->timeout = $timeout;
		$this->redirects = $redirects;
		$this->headers = $headers;
		$this->useragent = $useragent;

		$this->method = SIMPLEPIE_FILE_SOURCE_REMOTE;
		
		global $wpdb;
		global $fwp_credentials;
		
		if ( preg_match('/^http(s)?:\/\//i', $url) ) {
			$args = array( 'timeout' => $this->timeout, 'redirection' => $this->redirects);
	
			if ( !empty($this->headers) )
				$args['headers'] = $this->headers;

			// Use default FWP user agent unless custom has been specified
			if ( SIMPLEPIE_USERAGENT != $this->useragent ) :
				$args['user-agent'] = $this->useragent;
			else :
				$args['user-agent'] = apply_filters('feedwordpress_user_agent',
					'FeedWordPress '.FEEDWORDPRESS_VERSION
					.' (aggregator:feedwordpress; WordPress/'.$wp_version
					.' + '.SIMPLEPIE_NAME.'/'.SIMPLEPIE_VERSION
					.'; Allow like Gecko; +http://feedwordpress.radgeek.com/) '
					. feedwordpress_display_url(get_bloginfo('url')),
					$this
				);
			endif;

			// This is ugly as hell, but communicating up and down the chain
			// in any other way is difficult.

			if (!is_null($fwp_credentials)) :

				$args['authentication'] = $fwp_credentials['authentication'];
				$args['username'] = $fwp_credentials['username'];
				$args['password'] = $fwp_credentials['password'];

			elseif ($source InstanceOf SyndicatedLink) :

				$args['authentication'] = $source->authentication_method();
				$args['username'] = $source->username();
				$args['password'] = $source->password();
			
			endif;

			$res = wp_remote_request($url, $args);

			if ( is_wp_error($res) ) {
				$this->error = 'WP HTTP Error: ' . $res->get_error_message();
				$this->success = false;
			} else {
				$this->headers = wp_remote_retrieve_headers( $res );
				$this->body = wp_remote_retrieve_body( $res );
				$this->status_code = wp_remote_retrieve_response_code( $res );
			}
			
			if ($source InstanceOf SyndicatedLink) :
				$source->update_setting('link/filesize', strlen($this->body));
				$source->update_setting('link/http status', $this->status_code);
				$source->save_settings(/*reload=*/ true);
			endif;
			
		} else {
			if ( ! $this->body = file_get_contents($url) ) {
				$this->error = 'file_get_contents could not read the file';
				$this->success = false;
			}
		}

		// SimplePie makes a strongly typed check against integers with
		// this, but WordPress puts a string in. Which causes caching
		// to break and fall on its ass when SimplePie is getting a 304,
		// but doesn't realize it because this member is "304" instead.
		$this->status_code = (int) $this->status_code;
	}
} /* class FeedWordPress_File () */

