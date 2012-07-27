<?php
//
// File:         CCT_API.php
//
// Author:       Richard Guay
//
/**
 *
 *
 * @package WP_Scrapbook
 * @version 1.0
 */
/*
Plugin Name: Custom Computer Tools API plugin
Plugin URI: http://www.customct.com/API/
Description:  This plugin is for creating custom API for your website using WordPress. Do more trying to get information form the database yourself for your web API. Just use WordPress PHP commands and create your very own API. Can also be used to make custom web pages with no theming. Great for building a webapp into your website.
Author: Richard Guay
Version: 1.0
Author URI: http://customct.com/about/richardguay
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
          'editor'
        ),
        'query_var' => 'APIs',
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
          'not_found_in_trash' => 'No APIs are in the Trash'
        ),
        'menu_icon' => plugins_url( "images", __FILE__ ).'/API.png'
      ) 
    );
  }

  public function RunAPI() {
    //
    // Reference global variables that we will use.
    //
    global $wpdb;

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
// Now, Initialize the class variable.
//
$cct_API->init( $pagename );

//
// End of the PHP
//
?>
