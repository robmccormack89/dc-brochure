<?php

/**
*
* The main blog posts archive template. This template will display the main blog posts archive, whether set to the homepage or any other page
* This template will also get used when all other template are missing. which shouldnt be the case anyway....
*
* @package Rmcc_Theme
*
*/

// namespace & use
namespace Rmcc;
use Timber\PostQuery;
use Timber\Post;

// set templates variable as an array
$templates = array('blog.twig', 'archive.twig', 'base.twig');

// set the context
$context = Theme::context();

// set some context vars
$context['posts'] = new PostQuery(); // archive posts

// set the title for the blog page
$post = new Post();
$title = get_the_title($post->id); // title of page itself
if (is_home() && is_front_page()) $title = get_bloginfo('name'); // site title if blog is homepage

// set the title var, modified for paging
$context['title'] = (is_paged()) ? $title . ' - Page ' . get_query_var('paged') : $title;
// $context['description'] = get_the_archive_description();

// & render the template with the context
Theme::render($templates, $context);
