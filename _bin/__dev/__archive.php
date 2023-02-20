<?php

/**
*
* The template for displaying all general archive pages (apart from the main blog posts page)
*
* @package Rmcc_Theme
*
*/

// namespace & use
namespace Rmcc;
use Timber\PostQuery;

// set templates variable as an array
$templates = array('archive.twig', 'base.twig');

// set the context
$context = Theme::context();

// set some context vars
$context['posts'] = new PostQuery(); // archive posts

// set $title & modify $templates depending on archive
if (is_day()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('D M Y');
} elseif (is_month()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('M Y');
} elseif (is_year()) {
	$title = _x( 'Archive', 'Archives', 'base-theme' ) . ': ' . get_the_date('Y');
} elseif (is_tag()) {
	$title = single_tag_title('', false);
} elseif (is_category()) {
	$title = single_cat_title('', false);
	array_unshift($templates, 'archive-' . get_query_var('cat') . '.twig', 'category.twig');
}

// set title & description vars
$context['title'] = (is_paged()) ? $title . ' - Page ' . get_query_var('paged') : $title; // title according to paged page
$context['description'] = get_the_archive_description();

// & render the templates with the context
Theme::render($templates, $context);
