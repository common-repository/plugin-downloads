<?php 
/*
Plugin Name: Plugin Downloads
Version: 1.2.1
Plugin URI: http://yoast.com/wordpress/plugin-downloads/
Description: Adds a widget on your Dashboard showing the number of downloads for your plugins
Author: Joost de Valk
Author URI: http://yoast.com/
*/

if ( ! class_exists( 'PluginDownloads_Admin' ) ) {

	require_once('yst_plugin_tools.php');
	
	class PluginDownloads_Admin extends Yoast_Plugin_Admin {
		
		var $hook 		= 'plugin-downloads';
		var $longname	= 'Plugin Downloads Configuration';
		var $shortname	= 'Plugin Downloads';
		var $filename	= 'plugin-downloads/plugin-downloads.php';
		var $ozhicon	= 'table_gear.png';
		var $optionname = 'yst_plugin-downloads';		
		
		function get_defaults() {
			$options = array(
				'printcss' => true,
				'makesortable' => true
			);
			return $options;
		}
		
		function config_page() {
			$options = get_option($this->optionname);
			
			if (!is_array($options)) {
				$options = $this->get_defaults();
				update_option($this->optionname,$options);
			}
			
			if (isset($_POST['submit'])) {
				if (!current_user_can('manage_options')) die(__('You cannot edit the Plugin Downloads options.'));
				check_admin_referer('plugin-downloads-config');
			
				foreach(array('wpusername') as $val) {
					if (isset($_POST[$val]) && $_POST[$val] != '') {
						$options[$val] = $_POST[$val];
					}
				}

				foreach(array('printcss','makesortable') as $val) {
					if (isset($_POST[$val])) {
						$options[$val] = true;
					} else {
						$options[$val] = false;
					}
				}
				
				update_option($this->optionname,$options);
				
				echo "<div id=\"updatemessage\" class=\"updated fade\"><p><strong>Success:</strong> Plugin Downloads settings Updated.</p></div>\n";
				echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";
			}
			?>
			<div class="wrap">
				<a href="http://yoast.com/"><div id="yoast-icon" style="background: url(http://cdn.yoast.com/theme/yoast-32x32.png) no-repeat;" class="icon32"><br /></div></a>
				<h2>Plugin Downloads options</h2>
				<div class="postbox-container" style="width:70%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<form action="" method="post" id="plugin-downloads-conf" enctype="multipart/form-data">
								<?php wp_nonce_field('plugin-downloads-config'); ?>
								<?php 
									$this->postbox('wpusername','WordPress.org username',$this->textinput('wpusername','WordPress.org username'));
									
									$content = $this->checkbox('printcss','Add CSS to pages with plugin downloads table?');
									$content .= $this->checkbox('makesortable','Make the plugin downloads table sortable?');
									$this->postbox('pluginsettings','Other Plugin Settings',$content);
								?>
								<div class="submit">
									<input type="submit" class="button-primary" name="submit" value="Update Plugin Downloads Settings &raquo;" />
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="postbox-container" style="width:20%;">
					<div class="metabox-holder">	
						<div class="meta-box-sortables">
							<?php
								$this->plugin_like();
								$this->plugin_support();
								$this->news(); 
							?>
						</div>
						<br/><br/><br/>
					</div>
				</div>
			</div>
			<?php
		}
	}
	
	$pda = new PluginDownloads_Admin();
}
function yoast_plugin_downloads_head() {
	global $post;
	$options = get_option('yst_plugin-downloads');
	
	if (!is_array($options)) {
		$options = PluginDownloads_Admin::get_defaults();
	}
	// Conditional loading of needed javascripts
	if ($options['makesortable'] && (strpos($post->post_content,"[plugin_downloads]") !== false || is_admin())) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('tablesorter',WP_CONTENT_URL.'/plugins/plugin-downloads/jquery.tablesorter.min.js',array('jquery'),false,true);
		if (!function_exists('print_sortable_downloads_footer')) {
			function print_sortable_downloads_footer() {
				echo '<script type="text/javascript" charset="utf-8">jQuery(document).ready(function() { jQuery("#yoastdownloads").tablesorter({widgets: [\'zebra\']}); });</script>';
			}
		}
		add_action('wp_footer','print_sortable_downloads_footer',99);
		add_action('admin_footer','print_sortable_downloads_footer',99);
	}
	if ($options['printcss']) {
		$css = '<style type="text/css" media="screen">
			table.tablesorter {
				margin:10px 0pt 15px;
				font-size: 8pt;
				width: 100%;
				text-align: left;
				border-collapse: collapse;
			}
			table.tablesorter thead tr th, table.tablesorter tfoot tr th, table.tablesorter tfoot tr td {
				background-color: #40484D;
				color: #fff;
				border: 1px solid #FFF;
				font-size: 8pt;
				padding: 4px;
			}
			table.tablesorter thead tr .header {
				background-image: url('.WP_CONTENT_URL.'/plugins/plugin-downloads/bg.gif);
				background-repeat: no-repeat;
				background-position: center right;
				cursor: pointer;
			}
			table.tablesorter tbody td {
				color: #3D3D3D;
				padding: 4px;
				background-color: #FFF;
				vertical-align: top;
				border: 1px solid #FFF;
			}
			table.tablesorter td.num {
				text-align: right;
			}
			table.tablesorter tbody tr.odd td {
				background-color:#F0F0F6;
			}
			table.tablesorter thead tr .headerSortUp {
				background-image: url('.WP_CONTENT_URL.'/plugins/plugin-downloads/asc.gif);
			}
			table.tablesorter thead tr .headerSortDown {
				background-image: url('.WP_CONTENT_URL.'/plugins/plugin-downloads/desc.gif);
			}
			table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
				background-color: #fff;
				color: #40484D;
			}
		</style>';
		echo $css;
	}
}
add_action('wp_print_scripts','yoast_plugin_downloads_head');
add_action('wp_admin_print_scripts','yoast_plugin_downloads_head');

function yoast_plugin_downloads() {
	require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
	$options = get_option('yst_plugin-downloads');
	
	if (!is_array($options)) {
		$options = PluginDownloads_Admin::get_defaults();
	}
	$args['author'] = $options['wpusername'];
	$args['fields'] = array();
	$args['fields']['author']		 	= true;
	$args['fields']['author_profile'] 	= true;
	$args['fields']['homepage'] 		= true;
	$args['fields']['contributors'] 	= true;
	$args['fields']['rating'] 			= true;
	$args['fields']['downloaded'] 		= true;
	$args['fields']['requires'] 		= true;
	$args['fields']['tested'] 			= true;
	$args['fields']['last_updated'] 	= true;
	$args['fields']['sections'] 		= false;
	$args['fields']['downloadlink'] 	= false;
	$args['fields']['tags'] 			= false;
	$args['fields']['description'] 		= false;
	$args['fields']['short_description']= false;

	$req = (object) $args;

	$data = plugins_api( 'query_plugins', $req );
	$plugins = $data->plugins;
	
	function plugin_compare($a, $b) {
	    if ($a->downloaded == $b->downloaded) {
	        return 0;
	    }
	    return ($a->downloaded < $b->downloaded) ? 1 : -1;
	}
	
	usort($plugins, "plugin_compare");
	$i = 0;
	$total_downloaded 	= 0;
	$total_numratings 	= 0;
	$total_ratings		= 0;
	$content .= "<table id='yoastdownloads' class='tablesorter'>";
	$content .= "<thead><tr>
		<th width='40%'>Plugin</th>
		<th width='15%'>Last Update</th>
		<th  width='15%' class='num'>Rating</th>
		<th  width='15%' class='num'># Ratings</th>
		<th  width='15%' class='num'>Downloads</th>
		</tr></thead><tbody>";
	foreach ($plugins as $plugin) {
		$content .= "<tr>";
		$content .= '<td><a href="'.$plugin->homepage.'">'.$plugin->name.'</a></td>';
		$content .= '<td class="lastupdated">'.$plugin->last_updated.'</td>';
		$content .= "<td class='num'>".number_format($plugin->rating,2)."</td>";
		$content .= "<td class='num'>".number_format($plugin->num_ratings)."</td>";
		$content .= "<td class='num'><strong>".$plugin->downloaded."</strong></td>";
		$content .= "</tr>\n";
		$total_downloaded 	+= $plugin->downloaded;
		$total_numratings	+= $plugin->num_ratings;
		$total_ratings 		+= $plugin->num_ratings*$plugin->rating;
		$i++;
	}
	$avg_rating 	= number_format(($total_ratings/$total_numratings),2);
	$avg_numratings = number_format(($total_numratings/$i),2);
	$avg_downloads	= number_format(($total_downloaded/$i),0,'.','');
	$content .= "</tbody><tfoot><tr><th>Averages</th><td>&nbsp;</td><td class='num'><strong>$avg_rating</strong></td><td class='num'><strong>$avg_numratings</strong></td><td class='num'><strong>$avg_downloads</strong></td></tr>";
	$content .= "<tr><th>Totals</th><td>&nbsp;</td><td>&nbsp;</td><td class='num'><strong>".number_format($total_numratings)."</strong></td><td class='num'><strong>".$total_downloaded."</strong></td></tr>";
	$content .= "</tfoot></table>";
	
	return $content;
}

function plugin_downloads_shortcode($atts) {
	return yoast_plugin_downloads();
}
add_shortcode('plugin_downloads', 'plugin_downloads_shortcode');

if (!class_exists('YoastPluginDownloads')) {
	class YoastPluginDownloads {
		
		// Class initialization
		function YoastPluginDownloads() {
			// Add the widget to the dashboard
			add_action( 'wp_dashboard_setup', array(&$this, 'register_widget') );
			add_filter( 'wp_dashboard_widgets', array(&$this, 'add_widget') );
		}

		// Register this widget -- we use a hook/function to make the widget a dashboard-only widget
		function register_widget() {
			wp_register_sidebar_widget( 'yoast_plugin_downloads', __( 'Plugin Downloads - by Yoast', 'yoast_plugin_downloads' ), array(&$this, 'widget') );
		}

		// Modifies the array of dashboard widgets and adds this plugin's
		function add_widget( $widgets ) {
			global $wp_registered_widgets;
			if ( !isset($wp_registered_widgets['yoast_plugin_downloads']) ) return $widgets;
			array_splice( $widgets, 2, 0, 'yoast_plugin_downloads' );
			return $widgets;
		}

		function widget($args = array()) {
			global $pluginauthor;
			if (is_array($args))
				extract( $args, EXTR_SKIP );
			echo $before_widget.$before_title.$widget_name.$after_title;
			echo yoast_plugin_downloads();
			echo $after_widget;
		}
	}

	// Start this plugin once all other plugins are fully loaded
	add_action( 'plugins_loaded', create_function( '', 'global $YoastPluginDownloads; $YoastPluginDownloads = new YoastPluginDownloads();' ) );
}
?>