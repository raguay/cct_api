<?php
/*
Plugin Name: Custom Computer Tools WebAPI plugin
Plugin URI: http://www.customct.com/api-page/
Description:  This plugin is for creating custom APIs for your website using WordPress. No more trying to get information form the database yourself for your web API. Just use WordPress PHP commands and create your very own API. Can also be used to make custom web pages with no theming. Great for building a webapp into your website. You can find more useful tools and tutorials at <a href='http://customct.com'>Custom Computer Tools</a>. This plugin was designed on <a href='http://www.customct.com/shop/script-manager/'>Script Manager by Custom Computer Tools</a>.
Author: Richard Guay
Version: 1.0
Author URI: http://customct.com/about
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 1.0
License: GPL1
Text Domain: cct_api
*/

//
// Declare the global variables.
//
global $wpdb, $cct_API;
$cct_API_version = "1.0";

//
// I can not find a dependable way to get the admin page name.  So
// this is the cheating way to get it from the url since admin pages
// do not use renaming.
//
$pagename = $_GET['page'];

//
// Class:        CCT_API
//
// Description:  This class encapsolates all the functions used in the CCT_API plugin.
//               It is mostly a holder for the different functions used.
//
class CCT_API {
  //
  // Class Variables:
  //                   pagename        Name for the current page.
  //
  public $pagename;

  //
  // Function:          on_activate
  //
  // Description:       This is called on activation of the plugin.
  //
  public function on_activate() {

  }

  //
  // Function:          on_deactivate
  //
  // Description:       This is called on deactivation of the plugin.
  //
  public function on_deactivate() {

  }

  //
  // Function:          on_uninstall
  //
  // Description:       This is called on uninstall of the plugin.
  //
  public function on_uninstall() {

  }

  //
  // Function:          init
  //
  // Description:       This is used to register the class.
  //
  public function init($pg) {
    //
    // Reference all globals used.
    //
    global $cct_API;

    //
    // Set the page name.
    //
    $this->pagename = $pg;

    //
    // Now, set the RunAPI function to be executed after WordPress is fully loaded.
    //
    add_action('wp_loaded',array(&$cct_API,'RunAPI'));

    //
    // Set the displaying of the help documentation.
    //
    add_action("load-post.php", array(&$cct_API,'plugin_help'));

    //
    // Set the diabling of the rich editor on API pages.
    //
    add_filter('user_can_richedit', array(&$cct_API,'webapiRichEdit'));
  }

  //
  // Function:         webapiRichEdit
  //
  // Description:      This function is used to stop the rich editor on API pages.
  //
  public function webapiRichEdit($c) {
    global $post_type;

    if ('api' == $post_type)
        return false;
    return $c;
  }

  //
  // Function:          RegisterAPI
  //
  // Description: This function is used to initialize the Scrapbook.
  //
  public function RegisterAPI() {
    //
    // Reference global variables that we will use.
    //
    global $cct_API;

    //
    // This is for registering the API post type.
    //
    $result = register_post_type( 'API', array(
        'public'=> true,
        'supports' => array(
          'title',
          'editor',
          'page-attributes'         // This is required to get hierachical to work!
        ),
        'hierarchical' => true,
        'capability_type' => 'page',
        'query_var' => 'APIs',
        'permalink_epmask' => EP_PERMALINK,
        'rewrite' => array(
          'slug' => 'API',
          'with_front' => false
        ),
        'labels' => array(
          'name' => 'APIs',
          'singular_name' => 'API',
          'add_new' => 'Add New API',
          'add_new_item' => 'Add New API',
          'edit_item' => 'Edit API',
          'new_item' => 'New API',
          'view_item' => 'View API',
          'search_items' => 'Search APIs',
          'not_found' => 'API not found',
          'not_found_in_trash' => 'No APIs are in the Trash',
          'parent' => 'Parent API'
        ),
        'menu_icon' => plugins_url( "images", __FILE__ ).'/API.png'
      ) 
    );
  }

  //
  // Function:          RegisterAPI
  //
  // Description: This function is used to register the API post type.
  //
  public function RunAPI() {
    //
    // Reference global variables that we will use.
    //
    global $wpdb;

    //
    // Stop WordPress post formatting for these type of post.
    //
    $this->StopPostFormating();

    //
    // Register the post type.
    //
    $this->RegisterAPI();

    //
    // Now, we need to see if we are currently serving an API request.
    //
    $slug = $_SERVER["REQUEST_URI"];
    $apistr = preg_split( '/API/', $slug );

    //
    // If the preg_split array is larger than one, then it is an API page.
    //
    if ( count( $apistr ) > 1 ) {
      //
      // Split out the parts of the address line.
      //
      $page = explode( '/', $apistr[1] );

      //
      // Get the post for the API page.
      //
      $query = new WP_Query( array( 'post_type' => 'API', 'name' => $page[1] ) );

      //
      // If found, evaluate it and give it to the user.
      //
      if ( $query->have_posts() ) {
        //
        // Get the post information into the helper functions.
        //
        $query->the_post();

        //
        // The the body of the post that has the PHP script. Get it for evaluation.
        //
        $script = get_the_content();
      
        //
        // Evaluate the script and echo the results.
        //
        echo eval( $script );
      }

      //
      // Exit. We do not want the normal page stuff.
      //
      exit();
    }
  }

  //
  // Function:          StopPostFormating
  //
  // Description:       This function is used to stop WordPress form formating posts.
  //
  public function StopPostFormating() {
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
  }

  //
  // Function:          plugin_help
  //
  // Description:       This function is for setting up the help files for this plugin.
  //
  public function plugin_help() {
    //
    // Get the current screen context.
    //
    $screen = get_current_screen();
    if(strcasecmp($screen->post_type,"api") == 0) {
      //
      // Stop WordPress post formatting for these type of post.
      //
      $this->StopPostFormating();

      //
      // Set up the help documentation.
      //
      $screen->add_help_tab( array(
         'id'      => 'api-intro',
         'title'   => 'API Page',
         'content' => <<<EOT
<h3>WebAPI Pages</h3>
<p>The Web API pages are for creating Web based APIs for your Website. Anything in these 
pages will be interpreted and used as a PHP script. All WordPress functions are usable inside 
of these pages, but no theme formatting or header generation will be done. This is so you can 
create JSON code for sending to the requestor, or a totally unique page from the rest of the site. 
This can be used to create special webapps. Do not use shortcodes as they have been disabled.</p>

<p>If you find that your code is littered with &lt;p&gt;&lt;/p&gt; markings, you will need to turn 
off WordPress's automatic formatting. There is a <a href='http://www.customct.com/Tutorial/stopping-wordpress-auto-formating/'>tutorial</a> for doing just that.</p>

<p>You can find more documentation for the WebAPI plugin at <a href='http://www.customct.com/documentation/api-page/'>Custom Computer Tools</a>, along 
with a <a href='http://www.customct.com/forum/web-api/'>forum for sharing ideas</a>, <a href='http://www.customct.com/documentation/api-page/webapi-faq/'>a FAQ sheet</a>, and more <a href='http://www.customct.com/webapi-tutorials/'>tutorials</a>.
EOT
       ));
   
       //
       // Set the contents for the sidebar in the help section.
       //
       $screen->set_help_sidebar( <<<EOT
<p>For more great themes, plugins, help files, and tutorials, please visit our web site
<a href='http://customct.com'>Custom Computer Tools</a>.</p>    
EOT
                               );
     }
  }
}

//
// Class:           CCT_API_admin
//
// Description:     This class ecapsulates all the functions for the CCT API plugin for the admin
//                  side of WordPress.
//
class CCT_API_admin extends CCT_API {
}

//
// If this is the admin pages, then load that information as well.  This
// keeps the base plugin stuff for just the user side from taking so
// long to process unnessassary admin page stuff.  We also do the basic
// action functions assignment at this point.
//
if ( is_admin() ) {
  //
  // Create an instance of the administrator object.
  //
  $cct_API = new CCT_API_admin();
} else {
  //
  // Create an instance of the user object.
  //
  $cct_API = new CCT_API();
}

//
// Register the different hooks.
//
register_activation_hook( __FILE__, array( &$cct_API, 'on_activate' ) );
register_deactivation_hook( __FILE__, array( &$cct_API, 'on_deactivate' ) );
register_uninstall_hook( __FILE__, array( &$cct_API, 'on_uninstall' ) );

//
// Now, Initialize the class variable.
//
$cct_API->init( $pagename );

//
// End of the PHP
//
?>
