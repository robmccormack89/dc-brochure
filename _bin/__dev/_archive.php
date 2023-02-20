<?php
/**
 * The template for displaying general archive pages
 *
 * @package Rmcc_Theme
 */

// namespace & use
namespace Rmcc;
use Timber\PostQuery;
use Timber\User;
use Timber\Helper;

// set templates variable as an array
$templates = array('archive.twig', 'blog.twig', 'base.twig');

// set the context
$context = Theme::context();

// set some context vars
$posts = new PostQuery(); // archive posts

// author archives
if (is_author()) {
  if (isset( $wp_query->query_vars['author'])) {
  	$author = new User( $wp_query->query_vars['author'] );
  	$context['author'] = $author;
  	$title  = 'Author Archives: ' . $author->name();
  }
}

// date archives
elseif (is_day()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('D M Y');
}

elseif (is_month()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('M Y');
}

elseif (is_year()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('Y');
}

elseif (is_tag()) {
  $title = single_tag_title('', false);
  $term_key = 'tag';
  $term_value = get_queried_object_id();
  array_unshift( $templates, 'archive-' . get_query_var( 'tag' ) . '.twig' );
}

elseif (is_category()) {
  $title = single_cat_title( '', false );
  $term_key = 'cat';
  $term_value = get_queried_object_id();
	array_unshift( $templates, 'archive-' . get_query_var( 'cat' ) . '.twig', 'category.twig' );
}

// post_type archives
elseif (is_post_type_archive()) {
  $title = post_type_archive_title( '', false );
	array_unshift( $templates, 'archive-' . get_post_type() . '.twig' );
}

// pre posts stuff (top of page)
$_the_stickies = array(
  'post_type' => get_post_type(), // for post_type archives
  'post_status' => 'publish',
  'post__in'   => get_option('sticky_posts'),
);
if($term_key && $term_value) $_the_stickies[$term_key] = $term_value; // add $term_key & $term_value to the query if they exist (for category & tag archives)
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
  $all_posts = get_posts($posts);
  $first_stickies = array_slice($all_posts, 0, 1);
  if(!empty($first_stickies)){
    foreach ($first_stickies as $item) {
      $item = Helper::convert_wp_object($item);
      $first_stickies_ids_array[] = $item->id;
    }
    $context['first_stickies'] = new PostQuery($first_stickies_ids_array);
  }

}

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

// & render the templates with the context
Theme::render($templates, $context);
