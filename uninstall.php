<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Clean up any plugin-specific settings or options
delete_option( 'short_news_custom_css' );

// Delete all short_post entries
$args = array(
    'post_type'   => 'short_post',
    'numberposts' => -1,
);
$posts = get_posts( $args );

foreach ( $posts as $post ) {
    wp_delete_post( $post->ID, true );
}
