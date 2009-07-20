<?php
class FeedWordPressAdminPage {
	var $context;
	var $link = NULL;

	/**
	 * Construct the admin page object.
	 *
	 * @param mixed $link An object of class {@link SyndicatedLink} if created for one feed's settings, NULL if created for global default settings
	 */
	function FeedWordPressAdminPage ($page = 'feedwordpressadmin', $link = NULL) {
		// Set meta-box context name
		$this->context = $page;
		if ($this->for_feed_settings()) :
			$this->context .= 'forfeed';
		endif;
		$this->link = $link;
	} /* FeedWordPressAdminPage constructor */

	function for_feed_settings () { return (is_object($this->link) and method_exists($this->link, 'found') and $this->link->found()); }
	function for_default_settings () { return !$this->for_feed_settings(); }

	function these_posts_phrase () {
		if ($this->for_feed_settings()) :
			$phrase = __('posts from this feed');
		else :
			$phrase = __('syndicated posts');
		endif;
		return $phrase;
	} /* FeedWordPressAdminPage::these_posts_phrase() */

	/**
	 * Provides a uniquely identifying name for the interface context for
	 * use with add_meta_box() and do_meta_boxes(),
	 *
	 * @return string the context name
	 *
	 * @see add_meta_box()
	 * @see do_meta_boxes()
	 */
	function meta_box_context () {
		return $this->context;
	} /* FeedWordPressAdminPage::meta_box_context () */
	
	/**
	 * Outputs JavaScript to fix AJAX toggles settings.
	 *
	 * @uses FeedWordPressAdminPage::meta_box_context()
	 */
	 function fix_toggles () {
	 	 FeedWordPressSettingsUI::fix_toggles_js($this->meta_box_context());
	 } /* FeedWordPressAdminPage::fix_toggles() */

	 function ajax_interface_js () {
?>
<script type="text/javascript">
	function contextual_appearance (item, appear, disappear, value, visibleStyle, checkbox) {
		if (typeof(visibleStyle)=='undefined') visibleStyle = 'block';

		var rollup=document.getElementById(item);
		var newuser=document.getElementById(appear);
		var sitewide=document.getElementById(disappear);
		if (rollup) {
			if ((checkbox && rollup.checked) || (!checkbox && value==rollup.value)) {
				if (newuser) newuser.style.display=visibleStyle;
				if (sitewide) sitewide.style.display='none';
			} else {
				if (newuser) newuser.style.display='none';
				if (sitewide) sitewide.style.display=visibleStyle;
			}
		}
	}
</script>

<?php
	 } /* FeedWordPressAdminPage::ajax_interface_js () */
} /* class FeedWordPressAdminPage */

function fwp_linkedit_single_submit ($caption = NULL) {
	if (fwp_test_wp_version(FWP_SCHEMA_25, FWP_SCHEMA_27)) :
		if (is_null($caption)) : $caption = __('Save'); endif;
?>
<div class="submitbox" id="submitlink">
<div id="previewview"></div>
<div class="inside"></div>

<p class="submit">
<input type="submit" name="save" value="<?php print $caption; ?>" />
</p>
</div>
<?php
	endif;
}

function fwp_linkedit_periodic_submit ($caption = NULL) {
	if (!fwp_test_wp_version(FWP_SCHEMA_25)) :
		if (is_null($caption)) : $caption = __('Save Changes &raquo;'); endif;
?>
<p class="submit">
<input type="submit" name="save" value="<?php print $caption; ?>" />
</p>
<?php
	endif;
}

function fwp_linkedit_single_submit_closer ($caption = NULL) {
	if (fwp_test_wp_version(FWP_SCHEMA_27)) :
		if (is_null($caption)) : $caption = __('Save Changes'); endif;
?>
<p class="submit">
<input class="button-primary" type="submit" name="save" value="<?php print $caption; ?>" />
</p>
<?php
	endif;
}

function fwp_authors_single_submit ($link = NULL) {
	global $wp_db_version;
	
	if (fwp_test_wp_version(FWP_SCHEMA_25)) :
?>
<div class="submitbox" id="submitlink">
<div id="previewview">
</div>
<div class="inside">
</div>

<p class="submit">
<input type="submit" name="save" value="<?php _e('Save') ?>" />
</p>
</div>
<?php
	endif;
}

function fwp_option_box_opener ($legend, $id, $class = "stuffbox") {
	global $wp_db_version;
	if (isset($wp_db_version) and $wp_db_version >= FWP_SCHEMA_25) :
?>
<div id="<?php print $id; ?>" class="<?php print $class; ?>">
<h3><?php print htmlspecialchars($legend); ?></h3>
<div class="inside">
<?php
	else :
?>
<fieldset class="options"><legend><?php print htmlspecialchars($legend); ?></legend>
<?php
	endif;
}

function fwp_option_box_closer () {
	global $wp_db_version;
	if (isset($wp_db_version) and $wp_db_version >= FWP_SCHEMA_25) :
?>
	</div> <!-- class="inside" -->
	</div> <!-- class="stuffbox" -->
<?php
	else :
?>
</fieldset>
<?php
	endif;
}

function fwp_tags_box ($tags, $object) {
	if (!is_array($tags)) : $tags = array(); endif;
	
	$desc = "<p style=\"font-size:smaller;font-style:bold;margin:0\">Tag $object as...</p>";

	if (fwp_test_wp_version(FWP_SCHEMA_28)) : // WordPress 2.8+
?>
			<?php print $desc; ?>
			<div class="tagsdiv" id="post_tag">
		<div class="jaxtag">
		 <div class="nojs-tags hide-if-js">
		  <p><?php _e('Add or remove tags'); ?></p>
		  <textarea name="tax_input[post_tag]" class="the-tags" id="tax-input[post_tag]"><?php echo implode(",", $tags); ?></textarea>
		 </div>
		
		 <span class="ajaxtag hide-if-no-js">
			<label class="screen-reader-text" for="new-tag-post_tag"><?php _e('Tags'); ?></label>
			<input type="text" id="new-tag-post_tag" name="newtag[post_tag]" class="newtag form-input-tip" size="16" autocomplete="off" value="<?php esc_attr_e('Add new tag'); ?>" />
			<input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" />
		 </span>
		</div>
		<p class="howto"><?php echo __('Separate tags with commas.'); ?></p>
		<div class="tagchecklist"></div>
		</div>
		<p class="tagcloud-link hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-post_tag"><?php printf( __('Choose from the most used tags in %s'), 'Post Tags'); ?></a></p>
		</div>
		</div>
<?php
	else :
?>
		<?php print $desc; ?>
		<p id="jaxtag"><input type="text" name="tags_input" class="tags-input" id="tags-input" size="40" tabindex="3" value="<?php echo implode(",", $tags); ?>" /></p>
		<div id="tagchecklist"></div>
		</div>
		</div>
<?php
	endif;
}

function fwp_category_box ($checked, $object, $tags = array()) {
	global $wp_db_version;

	if (fwp_test_wp_version(FWP_SCHEMA_25)) : // WordPress 2.5.x
?>
<div id="category-adder" class="wp-hidden-children">
    <h4><a id="category-add-toggle" href="#category-add" class="hide-if-no-js" tabindex="3"><?php _e( '+ Add New Category' ); ?></a></h4>
    <p id="category-add" class="wp-hidden-child">
	<input type="text" name="newcat" id="newcat" class="form-required form-input-tip" value="<?php _e( 'New category name' ); ?>" tabindex="3" />
	<?php wp_dropdown_categories( array( 'hide_empty' => 0, 'name' => 'newcat_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => __('Parent category'), 'tab_index' => 3 ) ); ?>
	<input type="button" id="category-add-sumbit" class="add:categorychecklist:category-add button" value="<?php _e( 'Add' ); ?>" tabindex="3" />
	<?php wp_nonce_field( 'add-category', '_ajax_nonce', false ); ?>
	<span id="category-ajax-response"></span>
    </p>
</div>

<ul id="category-tabs">
	<?php /* ui-tabs-selected in WP 2.7 CSS = tabs in WP 2.8 CSS. Thank you, o brilliant wordsmiths of the WordPress 2.8 stylesheet... */ ?>
	<li class="ui-tabs-selected tabs"><a href="#categories-all" tabindex="3"><?php _e( 'All posts' ); ?></a>
        <p style="font-size:smaller;font-style:bold;margin:0">Give <?php print $object; ?> these categories</p>
</li>
</ul>

<?php /* ui-tabs-panel in WP 2.7 CSS = tabs-panel in WP 2.8 CSS. Thank you, o brilliant wordsmiths of the WordPress 2.8 stylesheet... */ ?>
<div id="categories-all" class="ui-tabs-panel tabs-panel">
    <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
	<?php fwp_category_checklist(NULL, false, $checked) ?>
    </ul>
</div>
<?php
	elseif (fwp_test_wp_version(FWP_SCHEMA_20)) : // WordPress 2.x
?>
		<div id="moremeta" style="position: relative; right: auto">
		<div id="grabit" class="dbx-group">
			<fieldset id="categorydiv" class="dbx-box">
			<h3 class="dbx-handle"><?php _e('Categories') ?></h3>
			<div class="dbx-content">
			<p style="font-size:smaller;font-style:bold;margin:0">Place <?php print $object; ?> under...</p>
			<p id="jaxcat"></p>
			<div id="categorychecklist"><?php fwp_category_checklist(NULL, false, $checked); ?></div>
			</div>
			</fieldset>
		</div>
		</div>
<?php
	else : // WordPress 1.5
?>
		<fieldset style="width: 60%;">
		<legend><?php _e('Categories') ?></legend>
		<p style="font-size:smaller;font-style:bold;margin:0">Place <?php print $object; ?> under...</p>
		<div style="height: 10em; overflow: scroll;"><?php fwp_category_checklist(NULL, false, $checked); ?></div>
		</fieldset>
<?php
	endif;
}

function update_feeds_mention ($feed) {
	echo "<li>Updating <cite>".$feed['link/name']."</cite> from &lt;<a href=\""
		.$feed['link/uri']."\">".$feed['link/uri']."</a>&gt; ...";
	flush();
}
function update_feeds_finish ($feed, $added, $dt) {
	echo " completed in $dt second".(($dt==1)?'':'s')."</li>\n";
}

function fwp_author_list () {
	global $wpdb;
	$ret = array();

	// display_name introduced in WP 2.0
	if (fwp_test_wp_version(FWP_SCHEMA_20)) :
		$name_column = 'display_name';
	else :
		$name_column = 'user_nickname';
	endif;

	$users = $wpdb->get_results("SELECT * FROM $wpdb->users ORDER BY {$name_column}");
	if (is_array($users)) :
		foreach ($users as $user) :
			$id = (int) $user->ID;
			$ret[$id] = $user->{$name_column};
			if (strlen(trim($ret[$id])) == 0) :
				$ret[$id] = $user->user_login;
			endif;
		endforeach;
	endif;
	return $ret;
}

class FeedWordPressSettingsUI {
	function instead_of_posts_box ($link_id = null) {
		if (!is_null($link_id)) :
			$from_this_feed = 'from this feed';
			$by_default = '';
			$id_param = "&amp;link_id=".$link_id;
		else :
			$from_this_feed = 'from syndicated feeds';
			$by_default = " by default";
			$id_param = "";
		endif;
?>
<p>Use the <a href="admin.php?page=<?php print $GLOBALS['fwp_path'] ?>/posts-page.php<?php print $id_param; ?>"><?php _e('Posts & Links'); ?></a>
settings page to set up how new posts <?php print $from_this_feed; ?> will be published<?php $by_default; ?>, whether they will accept
comments and pings, any custom fields that should be set on each post, etc.</p>
<?php
	} /* FeedWordPressSettingsUI::instead_of_posts_box () */
	
	function instead_of_authors_box ($link_id = null) {
		if (!is_null($link_id)) :
			$from_this_feed = 'from this feed';
			$by_default = '';
			$id_param = "&amp;link_id=".$link_id;
		else :
			$from_this_feed = 'from syndicated feeds';
			$by_default = " by default";
			$id_param = "";
		endif;

?>
<p>Use the <a
href="admin.php?page=<?php print $GLOBALS['fwp_path']
?>/authors-page.php<?php print $id_param; ?>"><?php _e('Authors');
?></a> settings page to set up how new posts
<?php print $from_this_feed; ?> will be assigned to
authors.</p>
<?php 
	} /* FeedWordPressSettingsUI::instead_of_authors_box () */
	
	function instead_of_categories_box ($link_id = null) {
		if (!is_null($link_id)) :
			$from_this_feed = 'from this feed';
			$by_default = '';
			$id_param = "&amp;link_id=".$link_id;
		else :
			$from_this_feed = 'from syndicated feeds';
			$by_default = " by default";
			$id_param = "";
		endif;
		
?>
<p>Use the <a href="admin.php?page=<?php print $GLOBALS['fwp_path'] ?>/categories-page.php<?php print $id_param; ?>"><?php _e('Categories'.FEEDWORDPRESS_AND_TAGS); ?></a>
settings page to set up how new posts <?php print $from_this_feed; ?> are assigned categories <?php if (FeedWordPressCompatibility::post_tags()) : ?>or tags<?php endif; ?><?php print $by_default; ?>.</p>
<?php
	} /* FeedWordPressSettingsUI::instead_of_categories_box () */

	/*static*/ function ajax_nonce_fields () {
		if (function_exists('wp_nonce_field')) :
			echo "<form style='display: none' method='get' action=''>\n<p>\n";
			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
			echo "</p>\n</form>\n";
		endif;
	} /* FeedWordPressSettingsUI::ajax_nonce_fields () */

	/*static*/ function fix_toggles_js ($context) {
	?>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
			<?php if (FeedWordPressCompatibility::test_version(FWP_SCHEMA_25, FWP_SCHEMA_27)) : ?>
				// In case someone got here first...
				jQuery('.postbox h3').unbind('click');

				add_postbox_toggles('<?php print $context; ?>');
			<?php elseif (FeedWordPressCompatibility::test_version(FWP_SCHEMA_27)) : ?>
				// In case someone got here first...
				$('.postbox h3, .postbox .handlediv').unbind('click');
				$('.postbox h3 a').unbind('click');
				$('.hide-postbox-tog').unbind('click');
				$('.columns-prefs input[type="radio"]').unbind('click');
				$('.meta-box-sortables').sortable('destroy');
				
				postboxes.add_postbox_toggles('<?php print $context; ?>');
			<?php endif; ?>
			} );
		</script>
	<?php
	}
} /* class FeedWordPressSettingsUI */

function fwp_insert_new_user ($newuser_name) {
	global $wpdb;

	$ret = null;
	if (strlen($newuser_name) > 0) :
		$userdata = array();
		$userdata['ID'] = NULL;
		
		$userdata['user_login'] = sanitize_user($newuser_name);
		$userdata['user_login'] = apply_filters('pre_user_login', $userdata['user_login']);
		
		$userdata['user_nicename'] = sanitize_title($newuser_name);
		$userdata['user_nicename'] = apply_filters('pre_user_nicename', $userdata['user_nicename']);
		
		$userdata['display_name'] = $wpdb->escape($newuser_name);

		$newuser_id = wp_insert_user($userdata);
		if (is_numeric($newuser_id)) :
			$ret = $newuser_id;
		else :
			// TODO: Add some error detection and reporting
		endif;
	else :
		// TODO: Add some error reporting
	endif;
	return $ret;
} /* fwp_insert_new_user () */

function fwp_add_meta_box ($id, $title, $callback, $page, $context = 'advanced', $priority = 'default', $callback_args = null) {
	if (function_exists('add_meta_box'))  :
		return add_meta_box($id, $title, $callback, $page, $context, $priority, $callback_args);
	else :
		/* Re-used as per terms of the GPL from add_meta_box() in WordPress 2.8.1 wp-admin/includes/template.php. */
		global $wp_meta_boxes;
	
		if ( !isset($wp_meta_boxes) )
			$wp_meta_boxes = array();
		if ( !isset($wp_meta_boxes[$page]) )
			$wp_meta_boxes[$page] = array();
		if ( !isset($wp_meta_boxes[$page][$context]) )
			$wp_meta_boxes[$page][$context] = array();
	
		foreach ( array_keys($wp_meta_boxes[$page]) as $a_context ) {
		foreach ( array('high', 'core', 'default', 'low') as $a_priority ) {
			if ( !isset($wp_meta_boxes[$page][$a_context][$a_priority][$id]) )
				continue;
	
			// If a core box was previously added or removed by a plugin, don't add.
			if ( 'core' == $priority ) {
				// If core box previously deleted, don't add
				if ( false === $wp_meta_boxes[$page][$a_context][$a_priority][$id] )
					return;
				// If box was added with default priority, give it core priority to maintain sort order
				if ( 'default' == $a_priority ) {
					$wp_meta_boxes[$page][$a_context]['core'][$id] = $wp_meta_boxes[$page][$a_context]['default'][$id];
					unset($wp_meta_boxes[$page][$a_context]['default'][$id]);
				}
				return;
			}
			// If no priority given and id already present, use existing priority
			if ( empty($priority) ) {
				$priority = $a_priority;
			// else if we're adding to the sorted priortiy, we don't know the title or callback. Glab them from the previously added context/priority.
			} elseif ( 'sorted' == $priority ) {
				$title = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['title'];
				$callback = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['callback'];
				$callback_args = $wp_meta_boxes[$page][$a_context][$a_priority][$id]['args'];
			}
			// An id can be in only one priority and one context
			if ( $priority != $a_priority || $context != $a_context )
				unset($wp_meta_boxes[$page][$a_context][$a_priority][$id]);
		}
		}
	
		if ( empty($priority) )
			$priority = 'low';
	
		if ( !isset($wp_meta_boxes[$page][$context][$priority]) )
			$wp_meta_boxes[$page][$context][$priority] = array();
	
		$wp_meta_boxes[$page][$context][$priority][$id] = array('id' => $id, 'title' => $title, 'callback' => $callback, 'args' => $callback_args);
	endif;
} /* function fwp_add_meta_box () */

function fwp_do_meta_boxes($page, $context, $object) {
	if (function_exists('do_meta_boxes')) :
		$ret = do_meta_boxes($page, $context, $object);
		
		// Avoid JavaScript error from WordPress 2.5 bug
?>
	<div style="display: none">
	<div id="tags-input"></div> <!-- avoid JS error from WP 2.5 bug -->
	</div>
<?php
		return $ret;
	else :
		/* Derived as per terms of the GPL from do_meta_boxes() in WordPress 2.8.1 wp-admin/includes/template.php. */
		global $wp_meta_boxes;
		static $already_sorted = false;
		
		//do_action('do_meta_boxes', $page, $context, $object);
	
		echo "<div id='$context-sortables' class='meta-box-sortables'>\n";
	
		$i = 0;
		do {
			if ( !isset($wp_meta_boxes) || !isset($wp_meta_boxes[$page]) || !isset($wp_meta_boxes[$page][$context]) )
				break;
	
			foreach ( array('high', 'sorted', 'core', 'default', 'low') as $priority ) {
				if ( isset($wp_meta_boxes[$page][$context][$priority]) ) {
					foreach ( (array) $wp_meta_boxes[$page][$context][$priority] as $box ) {
						if ( false == $box || ! $box['title'] )
							continue;
						$i++;
						fwp_option_box_opener($box['title'], $box['id'], 'postbox' /*. postbox_classes($box['id'], $page)*/);
						call_user_func($box['callback'], $object, $box);
						fwp_option_box_closer();
						
						// Submit button for WP 1.5 style
						fwp_linkedit_periodic_submit();
					}
				}
			}
		} while(0);
	
		echo "</div>";
	
		return $i;	
	endif;
} /* function fwp_do_meta_boxes() */

function fwp_remove_meta_box($id, $page, $context) {
	if (function_exists('remove_meta_box')) :
		return remove_meta_box($id, $page, $context);
	else :
		/* Re-used as per terms of the GPL from remove_meta_box() in WordPress 2.8.1 wp-admin/includes/template.php */
		global $wp_meta_boxes;
	
		if ( !isset($wp_meta_boxes) )
			$wp_meta_boxes = array();
		if ( !isset($wp_meta_boxes[$page]) )
			$wp_meta_boxes[$page] = array();
		if ( !isset($wp_meta_boxes[$page][$context]) )
			$wp_meta_boxes[$page][$context] = array();
	
		foreach ( array('high', 'core', 'default', 'low') as $priority )
			$wp_meta_boxes[$page][$context][$priority][$id] = false;
	endif;
} /* function fwp_remove_meta_box() */


