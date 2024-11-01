<?php

/*
Plugin Name: Snap.icio.us for Wordpress
Version: 0.5
Plugin URI: http://blog.quickes-wohnzimmer.de/snapicious
Description: Displays your recently listed del.icio.us links as Websnapr shots in a sidebar widget. Can be used as a "website of the day" function. Based on <a href="http://rick.jinlabs.com/code/delicious">del.icio.us for Wordpress</a> by <a href="http://rick.jinlabs.com/code/delicious">Ricardo Gonz&aacute;lez</a> and <a href="http://cavemonkey50.com/code/pownce/">Pownce for Wordpress</a> by <a href="http://cavemonkey50.com/">Cavemonkey50</a>. 
Author: quicke
Author URI: http://blog.quickes-wohnzimmer.de/
*/

/*  Copyright 2008  Quicke (plugins[under]quickes-wohnzimmer.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


define('MAGPIE_CACHE_AGE', 120);
define('MAGPIE_INPUT_ENCODING', 'UTF-8');

$snapicious_options['widget_fields']['title'] = array('label'=>'Title:', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['username'] = array('label'=>'<a  href="http://del.icio.us" target="_blank">del.icio.us</a> Username', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['num'] = array('label'=>'Number of screenshots:', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['tags'] = array('label'=>'Show own tags:', 'type'=>'checkbox', 'default'=>false);
$snapicious_options['widget_fields']['globaltag'] = array('label'=>'Show global tags:', 'type'=>'checkbox', 'default'=>false);
$snapicious_options['widget_fields']['displaylink'] = array('label'=>'del.icio.us link', 'type'=>'text', 'default'=>'My del.icio.us');
$snapicious_options['widget_fields']['displaylist'] = array('label'=>'Display as list', 'type'=>'checkbox', 'default'=>true);
$snapicious_options['widget_fields']['filtertag'] = array('label'=>'Tag(s) to display [cats+dogs+birds]: ', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['nodisplaytag'] = array('label'=>'Tag(s) to filter [cats+dogs+birds]:', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['displaytitle'] = array('label'=>'Display title:', 'type'=>'checkbox', 'default'=>false);
$snapicious_options['widget_fields']['displaydesc'] = array('label'=>'Display notes:', 'type'=>'checkbox', 'default'=>false);
$snapicious_options['widget_fields']['update'] = array('label'=>'Show post time:', 'type'=>'checkbox', 'default'=>false);
$snapicious_options['widget_fields']['websnaprkey'] = array('label'=>'<a  href="http://websnapr.com" target="_blank">Websnapr.com</a> API key:', 'type'=>'text', 'default'=>'');
$snapicious_options['widget_fields']['websnaprsize'] = array('label'=>'Screenshot size: (t,s,m,l)', 'type'=>'text', 'default'=>'s');
$snapicious_options['widget_fields']['encode_utf8'] = array('label'=>'UTF8 Encode:', 'type'=>'checkbox', 'default'=>false);


$snapicious_options['prefix'] = 'snapicious';
$snapicious_options['delicious_url'] = 'http://del.icio.us/';
$snapicious_options['rss_url'] = 'http://del.icio.us/rss/';
$snapicious_options['tag_url'] = 'http://del.icio.us/tag/';
$snapicious_options['websnapr_url'] = 'http://images.websnapr.com/';


// Display del.icio.us recently bookmarked links as screenshots

function snapicious_bookmarks($username = '', $num = 5, $displaylink='My del.icio.us',$list = true,$update = true, $tags = false, $filtertag = '', $displaytitle=false,$displaydesc = false, $nodisplaytag = '', $globaltag = false, $encode_utf8 = false ,$websnaprsize='s',$websnaprkey='') {

	global $snapicious_options;
	include_once(ABSPATH . WPINC . '/rss.php');
	$options = get_option('widget_snapicious');
		
	$rss = $snapicious_options['rss_url'].$username;
		      
	$screenshot_url = $snapicious_options['websnapr_url']."?size=".$websnaprsize."&key=".$websnaprkey."&url=";
	
	if($filtertag != '') { $rss .= '/'.$filtertag; }

	$bookmarks = fetch_rss($rss);
			
	if ($list) echo '<ul class="snapicious">';
	
	if ($username == '') {
		if ($list) echo '<li>';
		echo 'Username not configured';
		if ($list) echo '</li>';
	} else {
		if ( empty($bookmarks->items) ) {
			if ($list) echo '<li>';
			echo 'No bookmarks avaliable.';
			if ($list) echo '</li>';
		} else {
			foreach ( $bookmarks->items as $bookmark ) {
				$msg = $bookmark['title'];
				if($encode_utf8) utf8_encode($msg);					
				$link = $bookmark['link'];
				$desc = $bookmark['description'];
					if($encode_utf8) utf8_encode($desc);
					
				if ($list) echo '<li class="snapicious-item">'; elseif ($num != 1) echo '<p class="snapicious">';
       		echo '<a href="'.$link.'" class="snapicious-link" title="'.$msg.'"><img src="'.$screenshot_url.$link.'"></a>';

        if($update) {				
          $time = strtotime($bookmark['dc']['date']);
          
          if ( ( abs( time() - $time) ) < 86400 )
            $h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
          else
            $h_time = date(__('Y/m/d'), $time);
     			echo '<br />';
          echo sprintf( '%s',' <span class="snapicious-timestamp"><abbr title="' . date(__('Y/m/d H:i:s'), $time) . '">' . $h_time . '</abbr></span>' );
         }      
				
				if ($displaytitle && $msg != '') {
        			echo '<br />';
        			echo '<span class="snapicious-title">'.$msg.'</span>';
				}
				
				if ($displaydesc && $desc != '') {
        			echo '<br />';
        			echo '<span class="snapicious-desc">'.$desc.'</span>';
				}

				if ($tags) {
					echo '<br />';
					echo '<div class="snapicious-tags">';
					$tagged = explode(' ', $bookmark['dc']['subject']);
					$ndtags = explode('+', $nodisplaytag);
					if ($globaltag) { $gttemp = 'tag'; } else { $gttemp = $username; }
					foreach ($tagged as $tag) {
					  if (!in_array($tag,$ndtags)) {
       			  echo '<a href="http://del.icio.us/'.$gttemp.'/'.$tag.'" class="snapicious-link-tag">'.$tag.'</a> '; // Puts a link to the tag.              
            }
					}
					echo '</div>';
				}

				if ($list) echo '</li>'; elseif ($num != 1) echo '</p>';
			
				$i++;
				if ( $i >= $num ) break;
			}
		}	
  }
	if ($list) echo '</ul>';  

	if ($displaylink!='') {
		 			$link = $snapicious_options['delicious_url'].$username;
     			echo '<p class="snapicious-link"><a href="'.$link.'">'.$displaylink.'</a></p>';
		}

}
	
	
// snapicious widget stuff

function widget_snapicious_init() {
	
	if ( !function_exists('register_sidebar_widget') )
		return;
	
	$check_options = get_option('widget_snapicious');
  if ($check_options['number']=='') {
    $check_options['number'] = 1;
    update_option('widget_snapicious', $check_options);
  }
  	
	function widget_snapicious($args, $number = 1) {
		
		global $snapicious_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_snapicious');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($snapicious_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		$bookmarks = fetch_rss($snapicious_options['rss_url'] . $username);

		// These lines generate our output.
		echo $before_widget . $before_title . $item['title'] . $after_title;
		snapicious_bookmarks($item['username'], $item['num'], $item['displaylink'],$item['displaylist'], $item['update'], $item['tags'], $item['filtertag'], $item['displaytitle'],$item['displaydesc'], $item['nodisplaytag'], $item['globaltag'], $item['encode_utf8'],$item['websnaprsize'],$item['websnaprkey']);
		echo $after_widget;
	}



	// This is the function that outputs the form.
	function widget_snapicious_control($number) {
		
		global $snapicious_options;
		
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_snapicious');


		if ( isset($_POST['snapicious-submit']) ) {

			foreach($snapicious_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $snapicious_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_snapicious', $options);
		}

		foreach($snapicious_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $snapicious_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="snapicious_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}
		echo '<input type="hidden" id="snapicious-submit" name="snapicious-submit" value="1" />';
	}


	function widget_snapicious_setup() {
		$options = $newoptions = get_option('widget_snapicious');
		
		//echo '<style type="text/css">.snapicious_field { text-align:right; } .snapicious_field .text { width:200px; }</style>';
		
		if ( isset($_POST['snapicious-number-submit']) ) {
			$number = (int) $_POST['snapicious-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('widget_snapicious', $newoptions);
			widget_snapicious_register();
		}
	}
	
	
	function widget_snapicious_page() {
		$options = $newoptions = get_option('widget_snapicious');
	?>
		<div class="wrap">
			<form method="POST">
				<h2><?php _e('snap.icio.us Widgets'); ?></h2>
				<p style="line-height: 30px;"><?php _e('How many snap.icio.us widgets would you like?'); ?>
				<select id="snapicious-number" name="snapicious-number" value="<?php echo $options['number']; ?>">
	<?php for ( $i = 1; $i < 11; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="snapicious-number-submit" id="snapicious-number-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span></p>
			</form>
		</div>
	<?php
	}
	
	
	function widget_snapicious_register() {
		
		$options = get_option('widget_snapicious');
		$dims = array('width' => 300, 'height' => 400);
		$class = array('classname' => 'widget_snapicious');

		for ($i = 1; $i < 11; $i++) {
			$name = sprintf(__('snap.icio.us #%d'), $i);
			$id = "snapicious-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_snapicious' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_snapicious_control' : /* unregister */ '', $dims, $i);
		}
		
		add_action('sidebar_admin_setup', 'widget_snapicious_setup');
		add_action('sidebar_admin_page', 'widget_snapicious_page');
	}

	widget_snapicious_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_snapicious_init');

?>