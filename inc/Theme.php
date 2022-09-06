<?php

/**
*
* the Theme main class
*
* @package Rmcc_Theme
*
*/

// namespace & use
namespace Rmcc;
use Timber\Timber;
use Twig\Extra\String\StringExtension;

// Define paths to Twig templates
Timber::$dirname = array(
  'views',
  'views/archive',
  'views/parts',
  'views/single',
);

// set the $autoescape value
Timber::$autoescape = false;

// Define Theme Child Class
class Theme extends Timber {
  
  public function __construct() {
    parent::__construct();
    
    /**
    *
    * Class properties & globals
    *
    */
    global $configs;
    $this->configs = $configs;
    $this->logo_width = '223';
    $this->logo_height = '36';
    
    /**
    *
    * theme & twig
    *
    */
    add_action('after_setup_theme', array($this, 'theme_supports'));
    add_filter('timber/context', array($this, 'add_to_context'));
    add_filter('timber/twig', array($this, 'add_to_twig'));
    add_action('init', array($this, 'register_post_types'));
    add_action('init', array($this, 'register_taxonomies'));
    add_action('init', array($this, 'register_widget_areas'));
    add_action('init', array($this, 'register_navigation_menus'));
    add_action('enqueue_block_assets', array($this, 'theme_enqueue_assets')); // use 'theme_enqueue_assets' for frontend-only
    add_action('enqueue_block_assets', array($this, 'enqueue_google_fonts'));
    
    /**
    *
    * remove <p> tags from archive descriptions & other stuff
    *
    */
    remove_filter('term_description', 'wpautop');
    remove_filter('the_content', 'wpautop');
    remove_filter('the_excerpt', 'wpautop');
    remove_filter('widget_text_content', 'wpautop');
    remove_filter('widget_custom_html', 'wpautop' , 10, 3 );
    
    /**
    *
    * svg support
    *
    */
    add_filter('wp_check_filetype_and_ext', array($this, 'check_filetype'), 10, 4);
    add_filter('upload_mimes', array($this, 'cc_mime_types'));
    add_action('admin_head', array($this, 'fix_svg'));
    
    /**
    *
    * Yoast breadcrumbs
    *
    */
    if(yoast_breadcrumb_enabled()) add_filter('wpseo_breadcrumb_separator', array($this, 'filter_wpseo_breadcrumb_separator'), 10, 1);
        
    /**
    *
    * Theme's CSS classes
    *
    */
    add_filter('nav_menu_css_class', array($this, 'special_nav_class'), 10, 2);
    if($configs['theme_preloader']) add_filter('body_class', array($this, 'add_body_classes'));
    
    /**
    *
    * Disable comments
    *
    */
    if($configs['theme_post_comments']) {
      add_filter('comments_array', array($this, 'disable_comments_hide_existing_comments'), 10, 2);
      add_action('admin_menu', array($this, 'disable_comments_admin_menu'));
      add_action('admin_init', array($this, 'disable_comments_admin_menu_redirect'));
      add_action('admin_init', array($this, 'disable_comments_dashboard'));
      add_action('init', array($this, 'disable_comments_admin_bar'));
    }
    
    /**
    *
    * Removes sticky posts from main loop
    * this function fixes issue of duplicate posts on archive
    * see https://wordpress.stackexchange.com/questions/225015/sticky-post-from-page-2-and-on
    *
    */
    add_action('pre_get_posts', array($this, 'remove_stickies_from_main_loop'));
    
    // custom taxonomy base
    add_filter( 'post_type_link', array($this, 'add_tax_base_to_service_permalinks'), 1, 3 );
    
  }
  
  // custom taxonomy base
  public function add_tax_base_to_service_permalinks($post_link, $id = 0){
    $post = get_post($id);  
    if (is_object($post)){
      $terms = wp_get_object_terms($post->ID, 'service-type');
      if( $terms ){
        return str_replace('%service-type%' , $terms[0]->slug , $post_link);
      }
    }
    return $post_link;  
  }
  
  /**
  *
  * Removes sticky posts from main loop
  *
  */
  
  public function remove_stickies_from_main_loop($q) {
    
    // Only target the blog page // Only target the main query
    if ($q->is_home() && $q->is_main_query()) {
      
      // Remove sticky posts
      $q->set('ignore_sticky_posts', 1);
  
      // Get the sticky posts array
      $stickies = get_option('sticky_posts');
  
      // Make sure we have stickies before continuing, else, bail
      if (!$stickies) {
        return;
      }
  
      // Great, we have stickies, lets continue
      // Lets remove the stickies from the main query
      $q->set('post__not_in', $stickies);
  
      // Lets add the stickies to page one via the_posts filter
      if ($q->is_paged()) {
        return;
      }
  
      add_filter('the_posts', function ($posts, $q) use ($stickies) {
        
        // Make sure we only target the main query
        if (!$q->is_main_query()) {
          return $posts;
        }
  
        // Get the sticky posts
        $args = [
          'posts_per_page' => count($stickies),
          'post__in'       => $stickies
        ];
        $sticky_posts = get_posts($args);
  
        // Lets add the sticky posts in front of our normal posts
        $posts = array_merge($sticky_posts, $posts);
  
        return $posts;
          
      }, 10, 2);
      
    }
    
  }
  
  /**
  *
  * Disable Wordpress comments from backend & posts etc
  *
  */
  
  public function disable_comments_hide_existing_comments($comments) {
    $comments = array();
    return $comments;
  }
  public function disable_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
  }
  public function disable_comments_admin_menu_redirect() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
      wp_redirect(admin_url()); exit;
    }
  }
  public function disable_comments_dashboard() {
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
  }
  public function disable_comments_admin_bar() {
    if (is_admin_bar_showing()) {
      remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
  }
  
  /**
  *
  * Theme's CSS classes
  *
  */
  
  public function add_body_classes($classes){ // Add some classes to the body classes array 
    if($this->configs['theme_preloader']) array_push($classes, 'no-overflow');
    return $classes;
  }
  public function special_nav_class($classes, $item) { // Add uk-active class to wordpress' active menu items  
    if (in_array('current-menu-item', $classes) ){
      $classes[] = 'uk-active ';
    }
    return $classes;
  }
  
  /**
  *
  * Yoast breadcrumbs - customize the sep icon
  *
  */
  
  public function filter_wpseo_breadcrumb_separator($this_options_breadcrumbs_sep) {
  	return '<i uk-icon="icon: chevron-double-right; ratio: .8"></i>';
  }
  
  /**
  *
  * add svg support
  *
  */
  
  public function check_filetype($data, $file, $filename, $mimes) {
  
    global $wp_version;
    if ($wp_version !== '4.7.1') {
      return $data;
    }
  
    $filetype = wp_check_filetype($filename, $mimes);
  
    return [
      'ext'             => $filetype['ext'],
      'type'            => $filetype['type'],
      'proper_filename' => $data['proper_filename']
    ];
  
  }
  public function cc_mime_types( $mimes ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
  }
  public function fix_svg() {
    echo '<style type="text/css"> .attachment-266x266, .thumbnail img { width: 100%!important; height: auto!important; } </style>';
  }
  
  /**
  *
  * theme & twig setups
  *
  */
  
  public function theme_supports() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
    add_theme_support('post-formats', array(
      'gallery',
      'quote',
      'video',
      'aside',
      'image',
      'link'
    ));
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('html5', array(
      'search-form',
      'comment-form',
      'comment-list',
      'gallery',
      'caption'
    ));
    add_theme_support('custom-logo', array(
      'height' => $this->logo_height,
      'width' => $this->logo_width,
      'flex-width' => true,
      'flex-height' => true
    ));
    load_theme_textdomain('base-theme', get_template_directory() . '/languages');
  }
  public function add_to_twig($twig) {
    $twig->addExtension(new \Twig_Extension_StringLoader());
    $twig->addExtension(new StringExtension());
		return $twig;
  }
  public function add_to_context($context) {
    
    global $configs;
    
    $context['site'] = new \Timber\Site;
    $context['configs'] = $configs;

    // wp customizer logo
    $theme_logo_src = wp_get_attachment_image_url(get_theme_mod('custom_logo') , 'full');
    if($theme_logo_src){
      $context['theme']->logo = (object)[];
      $context['theme']->logo->src = $theme_logo_src;
      $context['theme']->logo->alt = '';
      $context['theme']->logo->w = $this->logo_width;
      $context['theme']->logo->h = $this->logo_height;
    }
    
    // add menus with args
    $context['main_menu'] = new \Timber\Menu('main_menu', array('depth' => 3));
    $context['has_main_menu'] = has_nav_menu('main_menu');
    
    $context['mobile_menu'] = new \Timber\Menu('mobile_menu', array('depth' => 3));
    $context['has_mobile_menu'] = has_nav_menu('mobile_menu');
    
    $context['secondary_menu'] = new \Timber\Menu('secondary_menu', array('depth' => 1));
    $context['has_secondary_menu'] = has_nav_menu('secondary_menu');
    
    $context['contact_menu'] = new \Timber\Menu('contact_menu', array('depth' => 1));
    $context['has_contact_menu'] = has_nav_menu('contact_menu');
    
    $context['coverage_areas'] = new \Timber\Menu('coverage_areas', array('depth' => 1));
    $context['has_coverage_areas'] = has_nav_menu('coverage_areas');
    
    // add sidebars
    $context['sidebar_footer_left'] = Timber::get_widgets('sidebar-footer-left');
    $context['sidebar_footer_right'] = Timber::get_widgets('sidebar-footer-right');
    
    // return context
    return $context;    
    
  }
  public function register_post_types() {
    
    $labels = array(
      'name'                  => _x( 'Services', 'Post Type General Name', 'text_domain' ),
      'singular_name'         => _x( 'Service', 'Post Type Singular Name', 'text_domain' ),
      'menu_name'             => __( 'Services', 'text_domain' ),
      'name_admin_bar'        => __( 'Service', 'text_domain' ),
      'archives'              => __( 'Services', 'text_domain' ),
      'attributes'            => __( 'Item Attributes', 'text_domain' ),
      'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
      'all_items'             => __( 'All Items', 'text_domain' ),
      'add_new_item'          => __( 'Add New Item', 'text_domain' ),
      'add_new'               => __( 'Add New', 'text_domain' ),
      'new_item'              => __( 'New Item', 'text_domain' ),
      'edit_item'             => __( 'Edit Item', 'text_domain' ),
      'update_item'           => __( 'Update Item', 'text_domain' ),
      'view_item'             => __( 'View Item', 'text_domain' ),
      'view_items'            => __( 'View Items', 'text_domain' ),
      'search_items'          => __( 'Search Item', 'text_domain' ),
      'not_found'             => __( 'Not found', 'text_domain' ),
      'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
      'featured_image'        => __( 'Featured Image', 'text_domain' ),
      'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
      'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
      'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
      'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
      'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
      'items_list'            => __( 'Items list', 'text_domain' ),
      'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
      'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
    );
    $args = array(
      'label'                 => __( 'Service', 'text_domain' ),
      'description'           => __( 'The Services we offer', 'text_domain' ),
      'labels'                => $labels,
      'show_in_rest'          => true,
      'supports'              => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes'),
      'hierarchical'          => false,
      'rewrite'               => array('slug' => 'services-repairs/%service-type%'),
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'has_archive'           => 'services-repairs',
      'capability_type'       => 'page',
    );
    register_post_type( 'services-repairs', $args );     
    
  }
  public function register_taxonomies() {
    
    $labels = array(
      'name'                       => _x( 'Service Type', 'Taxonomy General Name', 'text_domain' ),
      'singular_name'              => _x( 'Service Type', 'Taxonomy Singular Name', 'text_domain' ),
      'menu_name'                  => __( 'Service Types', 'text_domain' ),
      'all_items'                  => __( 'All Items', 'text_domain' ),
      'parent_item'                => __( 'Parent Item', 'text_domain' ),
      'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
      'new_item_name'              => __( 'New Item Name', 'text_domain' ),
      'add_new_item'               => __( 'Add New Item', 'text_domain' ),
      'edit_item'                  => __( 'Edit Item', 'text_domain' ),
      'update_item'                => __( 'Update Item', 'text_domain' ),
      'view_item'                  => __( 'View Item', 'text_domain' ),
      'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
      'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
      'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
      'popular_items'              => __( 'Popular Items', 'text_domain' ),
      'search_items'               => __( 'Search Items', 'text_domain' ),
      'not_found'                  => __( 'Not Found', 'text_domain' ),
      'no_terms'                   => __( 'No items', 'text_domain' ),
      'items_list'                 => __( 'Items list', 'text_domain' ),
      'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
    );
    $args = array(
      'labels'                     => $labels,
      'hierarchical'               => true,
      'public'                     => true,
      'show_ui'                    => true,
      'show_admin_column'          => true,
      'show_in_nav_menus'          => true,
      'show_tagcloud'              => false,
    );
    register_taxonomy( 'service-type', array( 'services-repairs'), $args );
    
  }
  public function register_widget_areas() {
    
    register_sidebar(array(
        'name' => esc_html__('Footer Widget Area: Left', 'base-theme'),
        'id' => 'sidebar-footer-left',
        'description' => esc_html__('Footer Widget Area: Left; works best with the current widget only.', 'base-theme'),
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4 class="widget-title uk-h4 uk-text-bold uk-text-primary uk-margin-remove-top">',
        'after_title' => '</h4>'
    ));
    register_sidebar(array(
        'name' => esc_html__('Footer Widget Area: Right', 'base-theme'),
        'id' => 'sidebar-footer-right',
        'description' => esc_html__('Footer Widget Area: Right; works best with the current widget only.', 'base-theme'),
        'before_widget' => '',
        'after_widget' => '',
        'before_title' => '<h4 class="widget-title uk-h4 uk-text-bold uk-text-primary uk-margin-remove-top">',
        'after_title' => '</h4>'
    ));
    
  }
  public function register_navigation_menus() {
    register_nav_menus(array(
      'main_menu' => _x( 'Main Menu', 'Menu locations', 'base-theme' ),
      'secondary_menu' => _x( 'Secondary Menu', 'Menu locations', 'base-theme' ),
      'contact_menu' => _x( 'Contact Menu', 'Menu locations', 'base-theme' ),
      'mobile_menu' => _x( 'Mobile Menu', 'Menu locations', 'base-theme' ),
      'coverage_areas' => _x( 'Coverage Areas', 'Menu locations', 'base-theme' ),
    ));
  }
  public function enqueue_google_fonts() {
    wp_enqueue_style( 'rmcc-google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Sarabun:wght@400;700;800&display=swap', array(), null );
  }
  public function theme_enqueue_assets() {
    
    // theme base scripts  (uikit, lightgallery, fonts-awesome)
    wp_enqueue_script(
      'base-theme',
      get_template_directory_uri() . '/assets/js/base.js',
      '',
      '',
      false
    );
    
    // theme base css (uikit, lightgallery, fonts-awesome)
    wp_enqueue_style(
      'base-theme',
      get_template_directory_uri() . '/assets/css/base.css'
    );
    
    // theme stylesheet (theme)
    wp_enqueue_style(
      'base-theme-styles', get_stylesheet_uri()
    );
    
    // global
    wp_enqueue_script(
      'global',
      get_template_directory_uri() . '/assets/js/global.js',
      '',
      '1.0.0',
      true
    );
    
  }

}