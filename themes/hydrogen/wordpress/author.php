<?php
/**
 * @package   Gantry 5 Theme
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   GNU/GPLv2 and later
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) or die;

/*
 * The template for displaying Author Archive pages
 */

$chooser = new \Gantry\Framework\OutlineChooser;

/** @var \Gantry\Framework\Theme $theme */
$theme = $gantry[ 'theme' ];
$theme->setLayout( $chooser->select() );

global $wp_query;

$context = Timber::get_context();
$context[ 'posts' ] = Timber::get_posts();
$context[ 'pagination' ] = Timber::get_pagination();

if( isset( $authordata ) ) {
	$author = new TimberUser( $authordata->ID );
	$context[ 'author' ] = $author;
	$context[ 'title' ] = 'Author: ' . $author->name();
}

Timber::render( array( 'author.html.twig', 'archive.html.twig', 'index.html.twig' ), $context );
