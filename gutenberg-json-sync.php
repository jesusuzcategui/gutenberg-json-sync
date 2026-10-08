<?php
/**
 * Plugin Name: Gutenberg JSON Sync
 * Description: Saves Site Editor templates to the active theme and sends changed files to Nodus Jet.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Author: Jesus Uzcategui
 * License: GPL-2.0-or-later
 * Text Domain: gutenberg-json-sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sync a saved template or template part to the active theme.
 *
 * @param WP_Post         $post    Saved template post.
 * @param WP_REST_Request $request REST request.
 * @param bool            $creating Whether the template was created.
 */
function gutenberg_json_sync_template_to_theme_file( $post, $request, $creating ) {
	if (
		! current_user_can( 'edit_theme_options' ) ||
		! has_filter( 'commit_from_plugin' ) ||
		! in_array( $post->post_type, array( 'wp_template', 'wp_template_part' ), true ) ||
		! is_string( $post->post_content )
	) {
		return;
	}

	$template = get_block_template( get_stylesheet() . '//' . $post->post_name, $post->post_type );
	if ( ! $template || get_stylesheet() !== $template->theme ) {
		return;
	}

	$slug = sanitize_file_name( $post->post_name );
	if ( '' === $slug || $slug !== $post->post_name ) {
		return;
	}

	$directory = trailingslashit( get_stylesheet_directory() ) . ( 'wp_template' === $post->post_type ? 'templates' : 'parts' );
	if ( ! is_dir( $directory ) && ! wp_mkdir_p( $directory ) ) {
		return;
	}

	$file_path = trailingslashit( $directory ) . $slug . '.html';
	if ( is_file( $file_path ) && file_get_contents( $file_path ) === $post->post_content ) {
		return;
	}

	$temp_path = tempnam( $directory, '.gutenberg-' );
	if ( false === $temp_path ) {
		return;
	}

	$file_mode = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;
	if (
		false === file_put_contents( $temp_path, $post->post_content, LOCK_EX ) ||
		! chmod( $temp_path, $file_mode ) ||
		! rename( $temp_path, $file_path )
	) {
		wp_delete_file( $temp_path );
		return;
	}

	apply_filters(
		'commit_from_plugin',
		null,
		get_stylesheet_directory(),
		sprintf( 'Updated %s: %s from Gutenberg', 'wp_template' === $post->post_type ? 'template' : 'template part', $slug ),
		$file_path
	);
}
add_action( 'rest_after_insert_wp_template', 'gutenberg_json_sync_template_to_theme_file', 10, 3 );
add_action( 'rest_after_insert_wp_template_part', 'gutenberg_json_sync_template_to_theme_file', 10, 3 );
