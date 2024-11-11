<?php
/**
 * Plugin Name: Fast Short News
 * Plugin URI: https://wordpress.org/plugins/fast-short-news
 * Description: A plugin to fast post and display short news updates on the front-end page or widget.
 * Version: 1.0.0
 * Author: codnloc
 * Author URI: http://codnloc.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fast-short-news
 * Domain Path: /languages
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register Custom Post Type for Short News
function fst_sn_create_short_news_cpt() {
    $args = array(
        'labels' => array(
            'name' => __( 'Short News', 'fast-short-news' ),
            'singular_name' => __( 'Short News', 'fast-short-news' ),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array( 'editor', 'author' ),
        'show_in_rest' => true,
    );
    register_post_type( 'short_post', $args );
}
add_action( 'init', 'fst_sn_create_short_news_cpt' );

// Remove the default "Post updated" message
function fst_sn_redirect_post_location( $location, $post_id ) {
    // Check if this is our post type
    if ( get_post_type( $post_id ) === 'short_post' ) {
        // Redirect to the edit page for this post
        return admin_url( 'edit.php?post_type=short_post' );
    }
    return $location;
}
add_filter( 'redirect_post_location', 'fst_sn_redirect_post_location', 10, 2 );


// Display short news posts using a shortcode
function fst_sn_display_short_news_posts( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => 20,
    ), $atts, 'short_news' );

    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'      => 'short_post',
        'posts_per_page' => $atts['posts_per_page'],
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $query = new WP_Query( $args );

    // Display short posts
    $output = '<div class="short-news">';
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $author_name = get_the_author();
            $post_date = get_the_date();
            $post_time = get_the_time();

            $output .= '<div class="short-news-item">';
            $output .= '<p>' . esc_html( get_the_content() ) . '</p>';

            // Separate date and time into different divs
            $output .= '<div class="short-news-date">' . esc_html( $post_date ) . '</div>';
            $output .= '<div class="short-news-time">' . esc_html( $post_time ) . '</div>';
            $output .= '<div class="short-news-author">' . esc_html( $author_name ) . '</div>';
            $output .= '</div> ';
        }

        // Pagination
        $big = 999999999; // need an unlikely integer
        $output .= '<div class="short-news-pagination">';
        $output .= paginate_links( array(
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, $paged ),
            'total'     => $query->max_num_pages,
        ));
        $output .= '</div>';

        wp_reset_postdata();
    } else {
        $output .= '<p>' . __( 'No short news posts found.', 'fast-short-news' ) . '</p>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode( 'short_news', 'fst_sn_display_short_news_posts' );


// Display short news posts using a shortcode
function fst_sn_display_latest_short_news_posts( $atts ) {
    $atts = shortcode_atts( array(
        'posts_per_page' => 10,
    ), $atts, 'short_news' );

    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'      => 'short_post',
        'posts_per_page' => $atts['posts_per_page'],
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );

    $query = new WP_Query( $args );

    // Display short posts
    $output = '<div class="latest-short-news">';
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();

            $author_name = get_the_author();
            $post_date = get_the_date();
            $post_time = get_the_time();

            $output .= '<div class="latest-short-news-item">';
			$output .= '<span class="latest-short-news-time">' . esc_html( $post_time ) . '  </span>';
            $output .= '<span class="latest-short-news-content">' . esc_html( get_the_content() ) . '</span>';
            $output .= '</div> ';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>' . __( 'No short news posts found.', 'fast-short-news' ) . '</p>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode( 'latest_short_news', 'fst_sn_display_latest_short_news_posts' );


// Enqueue custom CSS
function fst_sn_short_news_enqueue_styles() {
	if (!is_admin()) {
		wp_enqueue_style( 'short-news-style', plugin_dir_url( __FILE__ ) . 'assets/css/short-news.css' ,array(),'1.0.0' );
	}
}
add_action( 'wp_enqueue_scripts', 'fst_sn_short_news_enqueue_styles' );

// Filter to show content instead of title in the admin list
function fst_sn_short_news_columns( $columns ) {
    unset( $columns['title'] );
    $columns['content'] = __( 'Content', 'fast-short-news' );
    return $columns;
}
add_filter( 'manage_short_post_posts_columns', 'fst_sn_short_news_columns' );

// Display content in the custom column
function fst_sn_short_news_custom_column( $column, $post_id ) {
    if ( 'content' === $column ) {
        $content = get_the_content( $post_id );
        echo esc_html( $content );
    }
}
add_action( 'manage_short_post_posts_custom_column', 'fst_sn_short_news_custom_column', 10, 2 );

// Register the uninstallation hook
function fst_sn_short_news_uninstall() {
    // Optionally, remove any stored options or data if necessary
}
register_uninstall_hook( __FILE__, 'fst_sn_short_news_uninstall' );
