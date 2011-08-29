<?php 
/*
Plugin Name: Facebook Open Graph Meta in WordPress
Version: 0.2
Plugin URI: http://www.wpbeginner.com/
Description: Simple plugin that adds Facebook Open Graph Meta information in WordPress themes to avoid no thumbnail issue, wrong title issue, and wrong description issue.
Author: WPBeginner
Author URI: http://www.wpbeginner.com/
*/
/* Version check */
function fbogmeta_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder )
			$path = path_join( ltrim( $folder, '/' ), $path );

		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}
//Adding the Open Graph in the Language Attributes

function add_opengraph_doctype( $output ) {
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
	}
add_filter('language_attributes', 'add_opengraph_doctype');



/*Generates the best Image thumbnail by first looking for the featured image, then looking for the first image. 
If none exists, then it shows the default url. I replaced my snippet with Yoast's snippet to improve the functionality. 
His article can be found here - http://yoast.com/facebook-open-graph-protocol/
*/

function get_fbimage() {
  if ((function_exists('has_post_thumbnail')) && (has_post_thumbnail())) {
  $src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), '', '' );
  $fbimage = $src[0];
  } else {
    global $post, $posts;
    $fbimage = '';
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',
    $post->post_content, $matches);
    $fbimage = $matches [1] [0];
  }
  if(empty($fbimage)) {
	$options = get_option('fbogmeta');
    $fbimage = $options['default_img'];
  }
  return $fbimage;
}

//Lets add Open Graph Meta Info

function insert_fb_in_head() {
$options = get_option('fbogmeta');
echo '<meta property="fb:admins" content="'. $options['user_id'] .'"/>'; ?>
        
<meta property="og:title" content="<?php if(is_home()) { bloginfo('name'); } elseif(is_category()) { echo single_cat_title();} elseif(is_author()) { $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author')); echo $curauth->display_name; } else { echo the_title(); } ?>" />
<meta property="og:description" content="<?php echo strip_tags(get_the_excerpt($post->ID)); ?>"/>
<meta property="og:url" content="<?php the_permalink(); ?>"/>
<meta property="og:image" content="<?php echo get_fbimage(); ?>"/>
<meta property="og:type" content="<?php if (is_single() || is_page()) { echo "article"; } else { echo "website";} ?>"/>
<meta property="og:site_name" content="<?php bloginfo('name'); ?>"/>

<?php
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

add_action('admin_init', 'fbogmeta_init' );
add_action('admin_menu', 'fbogmeta_add_page');

// Init plugin options to white list our options
function fbogmeta_init(){
	register_setting( 'fbogmeta_options', 'fbogmeta', 'fbogmeta_validate' );
}

// Add menu page
function fbogmeta_add_page() {
	add_options_page('Facebook OG Meta', 'Facebook OG Meta', 'manage_options', 'fbogmetaoptions', 'fbogmeta_do_page');
}

// Draw the menu page itself
function fbogmeta_do_page() {
	?>
    <div style="width: 200px; right: 0; float: right; position: fixed; margin: 30px 10px 20px 0; background: #fff; border: 1px solid #e9e9e9; padding: 5px 5px 5px 5px; color: #666; font-size: 11px;">
<h3 style="margin: 0 0 10px 0; border-bottom: 1px dashed #666;">Donate</h3>
If you like this plugin and want WPBeginner to release more cool products, then please consider making a donation.
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="margin: 10px 0 20px 0;">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="KXE7F3TEK9Z5Y">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<h3 style="margin: 0 0 10px 0; border-bottom: 1px dashed #666;">Check us out:</h3>
Check our main site <a href="http://www.wpbeginner.com">WPBeginner</a> for WordPress tutorials. Don't forget to <a href="http://www.twitter.com/wpbeginner">follow us on twitter</a> and <a href="http://facebook.com/wpbeginner">join our facebook page</a>.

</div>
	<div class="wrap">
		<h2>Facebook Open Graph Meta Data</h2>
		<form method="post" action="options.php">
			<?php settings_fields('fbogmeta_options'); ?>
			<?php $options = get_option('fbogmeta'); ?>

                <table class="form-table" style="width: 70%;">
  
        <tr valign="top">
        <th scope="row">Your Facebook Account ID</th>
        <td>
         <input type="text" name="fbogmeta[user_id]" value="<?php echo $options['user_id']; ?>" /><br />Must enter one, if you want to receive insights (analytics) about the Like Buttons. You can find it by going to the URL like this: http://graph.facebook.com/syedbalkhi</td>
     </tr>
        
        <tr valign="top">
        <th scope="row">Your Site Name</th>
        <td>
        <input type="text" name="fbogmeta[site_name]" value="<?php echo $options['site_name']; ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Default Image URL</th>
        <td>
        <input type="text" name="fbogmeta[default_img]" value="<?php echo $options['default_img']; ?>" /><br /> Enter the URL for your default image. This will show if your post does not have a thumbnail.</td>
        </tr> 
        
        
    </table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function fbogmeta_validate($input) {
	
	// Say our second option must be safe text with no HTML tags
	$input['user_id'] =  wp_filter_nohtml_kses($input['user_id']);
	$input['site_name'] =  wp_filter_nohtml_kses($input['site_name']);
	$input['default_img'] =  wp_filter_nohtml_kses($input['default_img']);
	return $input;
}

add_action('wp_dashboard_setup', 'fbogmeta_dashboard_widgets');

function fbogmeta_dashboard_widgets() {
   global $wp_meta_boxes;

   wp_add_dashboard_widget('wpbeginnerfbogmetawidget', 'Latest from WPBeginner', 'fbogmeta_widget');
}
		function ogmetatext_limit( $text, $limit, $finish = ' [&hellip;]') {
			if( strlen( $text ) > $limit ) {
		    	$text = substr( $text, 0, $limit );
				$text = substr( $text, 0, - ( strlen( strrchr( $text,' ') ) ) );
				$text .= $finish;
			}
			return $text;
		}

		function fbogmeta_widget() {
			$options = get_option('wpbeginnerfbogmetawidget');
			require_once(ABSPATH.WPINC.'/rss.php');
			if ( $rss = fetch_rss( 'http://wpbeginner.com/feed/' ) ) { ?>
				<div class="rss-widget">
                
				<a href="http://www.wpbeginner.com/" title="WPBeginner - Beginner's guide to WordPress"><img src="http://cdn.wpbeginner.com/pluginimages/wpbeginner.gif"  class="alignright" alt="WPBeginner"/></a>			
				<ul>
                <?php 
				$rss->items = array_slice( $rss->items, 0, 5 );
				foreach ( (array) $rss->items as $item ) {
					echo '<li>';
					echo '<a class="rsswidget" href="'.clean_url( $item['link'], $protocolls=null, 'display' ).'">'. ($item['title']) .'</a> ';
					echo '<span class="rss-date">'. date('F j, Y', strtotime($item['pubdate'])) .'</span>';
					
					echo '</li>';
				}
				?> 
				</ul>
				<div style="border-top: 1px solid #ddd; padding-top: 10px; text-align:center;">
				<a href="http://feeds2.feedburner.com/wpbeginner"><img src="http://cdn.wpbeginner.com/pluginimages/feed.png" alt="Subscribe to our Blog" style="margin: 0 5px 0 0; vertical-align: top; line-height: 18px;"/> Subscribe with RSS</a>
				&nbsp; &nbsp; &nbsp;
				<a href="http://www.wpbeginner.com/wordpress-newsletter/"><img src="http://cdn.wpbeginner.com/pluginimages/email.gif" alt="Subscribe via Email"/> Subscribe by email</a>
                &nbsp; &nbsp; &nbsp;
                <a href="http://facebook.com/wpbeginner/"><img src="http://cdn.wpbeginner.com/pluginimages/facebook.png" alt="Join us on Facebook" style="margin: 0 5px 0 0; vertical-align: middle; line-height: 18px;" />Join us on Facebook</a>
				</div>
				</div>
			<?php }
		}


?>