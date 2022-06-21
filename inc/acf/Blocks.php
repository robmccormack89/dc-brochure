<?php
namespace Rmcc;
use Timber\Timber;
use Timber\Post;
use Timber\PostQuery;

array_unshift(
  Timber::$dirname, 
  'views/blocks',
  'views/blocks/featured-items-row-section',
  'views/blocks/slider-section',
  'views/blocks/testimonials-section',
  'views/blocks/buttons',
);

class Blocks {
  
  public function __construct() {
    add_action('acf/init', array($this, 'register_blocks'));
    add_action('enqueue_block_assets', array($this, 'acf_blocks_editor_scripts')); // use 'enqueue_block_editor_assets' for backend-only
    add_filter('style_loader_tag', array($this, 'preconnects_filter'), 10, 2);
  }
  
  /*
  Register blocks & enqueue scripts
  */
  
  public function register_blocks() {
    
    if(!function_exists('acf_register_block')) return;
    
    /*
    Sections (with inners)
    */
    
    acf_register_block(array( // half & half section 
      
      // *required
      'name' => 'half_and_half_section',
      'title' => 'Half & Half section',
      
      // the callback function
      'render_callback' => array($this, 'half_and_half_section_render_callback'),
      
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', 'center', ''), 
        'align_text' => false,
        'align_content' => true, 
        'full_height' => false, 
        // 'mode' => false,
        'jsx' => true
      ),
      
      // the defaults for various block settings
      'align' => 'full',
      'align_content' => 'center',
      
      // category & icon
      'category' => 'design',
      'icon' => 'align-pull-right',
      
      // keywords by which to search for the block
      'keywords' => array('half', 'section', 'rmcc'),
      
    ));
    
    acf_register_block(array( // cover section 
    
      // *required
      'name' => 'cover_section',
      'title' => 'Cover section',
    
      // the callback function
      'render_callback' => array($this, 'cover_section_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', 'center', ''), 
        'align_text' => false,
        'align_content' => false,
        'full_height' => true,
        // 'mode' => false,
        'jsx' => true
      ),
    
      // the defaults for various block settings
      'align' => 'full',
      'full_height' => true,
      'mode' => 'preview',
    
      // category & icon
      'category' => 'design',
      'icon' => 'cover-image',
    
      // keywords by which to search for the block
      'keywords' => array('cover', 'section', 'rmcc'),
    
    ));
    
    acf_register_block(array( // cta section 
    
      // *required
      'name' => 'cta_section',
      'title' => 'Call-to-action section',
    
      // the callback function
      'render_callback' => array($this, 'cta_section_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', 'center', ''), 
        'align_text' => false,
        'align_content' => false,
        'full_height' => false,
        // 'mode' => false,
        'jsx' => true
      ),
    
      // the defaults for various block settings
      'align' => 'full',
    
      // category & icon
      'category' => 'design', // what category the lock will be in 
      'icon' => 'editor-aligncenter', // icon used for the block (dashicons)
    
      // keywords by which to search for the block
      'keywords' => array('cta', 'call', 'to', 'action', 'section', 'rmcc'),
    
    ));
    
    acf_register_block(array( // Highlight card section 
    
      // *required
      'name' => 'highlight_card_section',
      'title' => 'Highlight card section',
    
      // the callback function
      'render_callback' => array($this, 'highlight_card_section_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', 'center', ''), 
        'align_text' => false, 
        'align_content' => true, 
        'full_height' => false, 
        // 'mode' => false,
        'jsx' => true
      ),
    
      // the defaults for various block settings
      'align' => 'full',
      'align_text' => 'left',
      'align_content' => 'center',
    
      // category & icon
      'category' => 'design',
      'icon' => 'align-center',
    
      // keywords by which to search for the block
      'keywords' => array('highlight', 'card', 'section', 'rmcc'),
    
    ));
    
    /*
    Swiper & Repeater sections
    */
    
    acf_register_block(array( // Slider 
    
      // *required
      'name' => 'slider_section',
      'title' => 'Slider section',
    
      // the callback function
      'render_callback' => array($this, 'slider_section_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', 'center', ''), 
        'align_text' => true, 
        'align_content' => true, 
        'full_height' => true, 
        // 'mode' => false
      ),
    
      // the defaults for various block settings
      'align' => 'full',
      'align_text' => 'center',
      'align_content' => 'center',
    
      // category & icon
      'category' => 'design',
      'icon' => 'slides',
    
      // keywords by which to search for the block
      'keywords' => array('slider', 'section', 'rmcc'),
    
    ));
    
    acf_register_block(array( // Testimonials 
    
      // *required
      'name' => 'testimonials_section',
      'title' => 'Testimonials & Ratings section',
    
      // the callback function
      'render_callback' => array($this, 'testimonials_section_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('center'), 
        'align_text' => true, 
        'align_content' => false, 
        'full_height' => false, 
        // 'mode' => false
      ),
    
      // the defaults for various block settings
      'align' => 'center', 
      'align_text' => 'center', 
    
      // category & icon
      'category' => 'design',
      'icon' => 'editor-quote',
    
      // keywords by which to search for the block
      'keywords' => array('testimonials', 'rating', 'section', 'rmcc'),
    
    ));
    
    /*
    Buttons
    */
    
    acf_register_block(array( // video popup button 
    
      // *required
      'name' => 'video_popup_button',
      'title' => 'Video popup button',
    
      // the callback function
      'render_callback' => array($this, 'video_popup_button_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => false, 
        'align_text' => true, 
        'align_content' => false, 
        'full_height' => false, 
        // 'mode' => false
      ),
    
      // the defaults for various block settings
      'align_text' => 'center',
    
      // category & icon
      'category' => 'design',
      'icon' => 'youtube',
    
      // keywords by which to search for the block
      'keywords' => array('video', 'popup', 'button', 'rmcc'),
    
    ));
    
    acf_register_block(array( // gallery button 
    
      // *required
      'name' => 'gallery_button',
      'title' => 'Gallery button',
    
      // the callback function
      'render_callback' => array($this, 'gallery_button_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => false, 
        'align_text' => true, 
        'align_content' => false, 
        'full_height' => false, 
        // 'mode' => false
      ),
    
      // the defaults for various block settings
      'align_text' => 'left',
    
      // category & icon
      'category' => 'design',
      'icon' => 'format-gallery',
    
      // keywords by which to search for the block
      'keywords' => array('gallery', 'button', 'rmcc'),
    
    ));
    
    /*
    Featured items - row
    */
    
    acf_register_block(array( // Featured items row section 
    
      // *required
      'name' => 'featured_items_row',
      'title' => 'Featured items row',
    
      // the callback function
      'render_callback' => array($this, 'featured_items_row_render_callback'),
    
      // what block settings does this block allow
      'supports' => array(
        'align' => array('full', 'wide', ''), 
        'align_text' => false, 
        'align_content' => 'matrix', 
        'full_height' => false, 
        // 'mode' => false
      ),
    
      // the defaults for various block settings
      'align' => 'full',
      'align_content' => 'center center',
    
      // category & icon
      'category' => 'design',
      'icon' => 'sticky',
    
      // keywords by which to search for the block
      'keywords' => array('featured', 'items', 'row', 'rmcc'),
    
    ));
      
  }
  public function acf_blocks_editor_scripts() {
    
    // swiper
    wp_enqueue_style(
      'swiper',
      get_template_directory_uri() . '/assets/css/lib/swiper-bundle.min.css'
    );
    wp_enqueue_script(
      'swiper-js',
      get_template_directory_uri() . '/assets/js/lib/swiper-bundle.min.js',
      '',
      '1.0.0',
      false
    );

    wp_enqueue_style(
      'base-theme',
      get_template_directory_uri() . '/assets/css/base.css'
    );
    wp_enqueue_script(
      'base-theme',
      get_template_directory_uri() . '/assets/js/base.js',
      '',
      '',
      false
    );
    
    // preconnects
    wp_enqueue_style('picsum-preconnect', 'https://picsum.photos', '', null);
    wp_enqueue_style('lorem-picsum-preconnect', 'https://i.picsum.photos', '', null);
    wp_enqueue_style('picsum-prefetch', 'https://picsum.photos', '', null);
    wp_enqueue_style('lorem-picsum-prefetch', 'https://i.picsum.photos', '', null);
  
  }
  public function preconnects_filter($html, $handle) {
    if ($handle === 'picsum-preconnect') {
      return str_replace("rel='stylesheet'",
        "rel='preconnect'", $html);
    }
    if ($handle === 'lorem-picsum-preconnect') {
      return str_replace("rel='stylesheet'",
        "rel='preconnect'", $html);
    }
    if ($handle === 'picsum-prefetch') {
      return str_replace("rel='stylesheet'",
        "rel='dns-prefetch'", $html);
    }
    if ($handle === 'lorem-picsum-prefetch') {
      return str_replace("rel='stylesheet'",
        "rel='dns-prefetch'", $html);
    }
    return $html;
  }
  
  /*
  Sections (with inners)
  */
  
  public function half_and_half_section_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('half-and-half-section.twig', $context);
  }
  public function cover_section_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('cover-section.twig', $context);
  }
  public function cta_section_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('cta-section.twig', $context);
  }
  public function highlight_card_section_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('highlight-card-section.twig', $context);
  }
  
  /*
  Swiper & Repeater sections
  */
  
  public function slider_section_render_callback($block, $content = '', $is_preview = false) { 
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;

    Timber::render('slider-section.twig', $context);
  }
  public function testimonials_section_render_callback($block, $content = '', $is_preview = false) { 
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('testimonials-section.twig', $context);
  }
  
  /*
  Buttons
  */
  
  public function gallery_button_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
  
    Timber::render('gallery-button.twig', $context);
  }
  public function video_popup_button_render_callback($block, $content = '', $is_preview = false) {
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
    
    Timber::render('video-popup-button.twig', $context);
  }
  
  /*
  Featured items - row
  */
  
  public function featured_items_row_render_callback($block, $content = '', $is_preview = false) { // 'featured_items' -> $context['items'] 
    $context = Timber::context();
    $context['block'] = $block;
    $context['fields'] = get_fields();
    $context['is_preview'] = $is_preview;
  
    // join to the selected post to the repeater field item, if it exists. can be found at .post of featured_items item
    if($context['fields'] && $context['fields']['featured_items']){
      $items = null;
      foreach($context['fields']['featured_items'] as $item){
        if($item['select_post_object']){
          $item['post'] = new Post($item['select_post_object']);
        }
        $items[] = $item;
      }
      $context['items'] = $items;
    }
  
    Timber::render('featured-items-row-section.twig', $context);
  }

}