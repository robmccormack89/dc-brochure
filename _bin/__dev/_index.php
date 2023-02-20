<?php
/**
 * The main blog template file
 *
 * @package Rmcc_Theme
*/

// namespace & use
namespace Rmcc;
use Timber\PostQuery;
use Timber\Post;
use Timber\Helper;
use Timber\Term;

// set templates variable as an array

// $templates = array('blog.twig', 'archive.twig', 'base.twig');
$templates = array( 'index.twig', 'base.twig' );
if (is_home()) array_unshift( $templates, 'archive.twig' ); // if the blog IS NOT the homepage
if (is_home() && is_front_page()) array_unshift( $templates, 'sections.twig' ); // if the blog IS the homepage. if the blog is homepage is static, the controller will be singular.php

// set the context
$context = Theme::context();

// set some context vars
$posts = new PostQuery(); // archive posts

// set the title for the blog page
$post = new Post();
$title = get_the_title($post->id); // title of page itself
if (is_home() && is_front_page()) $title = get_bloginfo('name'); // site title if blog is homepage

if (is_home()){

  // for when the blog ISNT the homepage
  if(!is_front_page()){

    // pre posts stuff (top of page)
    $_the_stickies = array(
      'post_type' => 'post',
    	'post_status' => 'publish',
    	'post__in'   => get_option('sticky_posts'),
    );
    $the_stickies = get_posts($_the_stickies);

    // if stickies exist...
    if(get_option('sticky_posts') && !empty($the_stickies)){

      $first_stickies = array_slice($the_stickies, 0, 1);
      if(!empty($first_stickies)){
        foreach ($first_stickies as $item) {
          $item = Helper::convert_wp_object($item);
          $first_stickies_ids_array[] = $item->id;
        }
        $context['first_stickies'] = new PostQuery($first_stickies_ids_array);
      }

      $remaining_stickies = array_slice($the_stickies, 1);
      if(!empty($remaining_stickies)){
        foreach ($remaining_stickies as $item) {
          $item = Helper::convert_wp_object($item);
          $remaining_stickies_ids_array[] = $item->id;
        }
        $context['remaining_stickies'] = new PostQuery($remaining_stickies_ids_array);
      }

    }

    // else if stickies dont exist...
    else {

      // set the first post (top of page)
      // an offset of 1 is added to the main loop query in Theme
      $all_posts = get_posts($posts);
      // print_r($all_posts);
      $first_stickies = array_slice($all_posts, 0, 1);
      // print_r($first_stickies);
      if(!empty($first_stickies)){
        foreach ($first_stickies as $item) {
          $item = Helper::convert_wp_object($item);
          $first_stickies_ids_array[] = $item->id;
        }
        $context['first_stickies'] = new PostQuery($first_stickies_ids_array);
      }

    }

  }

  // for when the blog IS the homepage
  if(is_front_page()){

    // pre posts stuff (top of page)
    $_the_stickies = array(
      'post_type' => 'post',
    	'post_status' => 'publish',
    	'post__in'   => get_option('sticky_posts'),
    );
    $the_stickies = get_posts($_the_stickies);

    // if stickies exist...
    if(get_option('sticky_posts') && !empty($the_stickies)){

      $first_stickies = array_slice($the_stickies, 0, 2);
      if(!empty($first_stickies)){
        foreach ($first_stickies as $item) {
          $item = Helper::convert_wp_object($item);
          $first_stickies_ids_array[] = $item->id;
        }
        $context['first_stickies'] = new PostQuery($first_stickies_ids_array);
      }

      $remaining_stickies = array_slice($the_stickies, 3);
      if(!empty($remaining_stickies)){
        foreach ($remaining_stickies as $item) {
          $item = Helper::convert_wp_object($item);
          $remaining_stickies_ids_array[] = $item->id;
        }
        $context['remaining_stickies'] = new PostQuery($remaining_stickies_ids_array);
      }

    }

    // terms carousel (black section)
    $cats_args = array(
    	'taxonomy'               => 'category',
    	'hide_empty'             => false,
    	'fields'                 => 'all',
    	'count'                  => true,
    );
    $the_cats = get_terms($cats_args);
    if(!empty($the_cats)){
      foreach ($the_cats as $item) {
        $item = Helper::convert_wp_object($item);
        $data[] = $item;
      }
      $context['the_cats'] = $data;
    }

  }

}

// print_r($posts);

// print_r($context['posts']);

// create the archive object, and fill it
$context['archive'] = (object) [
  // "post" => $post,
  "posts" => $posts,
  "title" => (is_paged()) ? $title . ' - Page ' . get_query_var('paged') : $title,
  "description" => get_the_archive_description(),
  "thumbnail" => [
    "src" => false,
    "alt" => false,
    "caption" => false
  ]
];

// & render the template with the context
Theme::render($templates, $context);
