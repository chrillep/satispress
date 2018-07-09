<?php
/**
 * Helper functions
 *
 * @package SatisPress
 * @license GPL-2.0-or-later
 * @since 0.1.0
 */

declare ( strict_types = 1 );

namespace SatisPress;

use SatisPress\Plugin;

/**
 * Retrieve the main plugin instance.
 *
 * @since 0.3.0
 *
 * @return Plugin
 */
function plugin(): Plugin {
	static $instance;
	$instance = $instance ?: new Plugin();
	return $instance;
}

/**
 * Autoload mapped classes.
 *
 * @since 0.3.0
 *
 * @param string $class Class name.
 */
function autoloader_classmap( string $class ) {
	$class_map = array(
		'PclZip' => ABSPATH . 'wp-admin/includes/class-pclzip.php',
	);

	if ( isset( $class_map[ $class ] ) ) {
		require_once $class_map[ $class ];
	}
}

/**
 * Generate a random string.
 *
 * @since 0.3.0
 *
 * @param integer $length Length of the string to generate.
 * @return string
 */
function generate_random_string( int $length = 12 ): string {
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$str = '';
	$max = strlen( $chars ) - 1;
	for ( $i = 0; $i < $length; $i++ ) {
		$str .= substr( $chars, random_int( 0, $max ), 1 );
	}

	return $str;
}

/**
 * Retrieve the authorization header.
 *
 * On certain systems and configurations, the Authorization header will be
 * stripped out by the server or PHP. Typically this is then used to
 * generate `PHP_AUTH_USER`/`PHP_AUTH_PASS` but not passed on. We use
 * `getallheaders` here to try and grab it out instead.
 *
 * From https://github.com/WP-API/OAuth1
 *
 * @return string|null Authorization header if set, null otherwise
 */
function get_authorization_header() {
	if ( ! empty( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
		return stripslashes( $_SERVER['HTTP_AUTHORIZATION'] );
	}

	if ( function_exists( 'getallheaders' ) ) {
		$headers = getallheaders();

		// Check for the authorization header case-insensitively.
		foreach ( $headers as $key => $value ) {
			if ( 'authorization' === strtolower( $key ) ) {
				return $value;
			}
		}
	}

	return null;
}

/**
 * Retrieve the permalink for packages.json.
 *
 * @since 0.2.0
 *
 * @param array $args Optional. Query string parameters. Default is an empty array.
 * @return string
 */
function get_packages_permalink( array $args = null ): string {
	if ( null === $args ) {
		$args = [];
	}

	$permalink = get_option( 'permalink_structure' );
	if ( empty( $permalink ) ) {
		$url = add_query_arg( 'satispress', 'packages.json', home_url( '/' ) );
	} else {
		// Leave off the packages.json if 'base' arg is true.
		$suffix = isset( $args['base'] ) && $args['base'] ? '' : 'packages.json';
		$url    = sprintf( home_url( '/satispress/%s' ), $suffix );
	}

	return $url;
}

/**
 * Retrieve ID for the user being edited.
 *
 * @since 0.3.0
 *
 * @return int
 */
function get_edited_user_id(): int {
	// phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
	return empty( $_GET['user_id'] ) ? get_current_user_id() : (int) $_GET['user_id'];
}

/**
 * Whether a plugin identifier is the main plugin file.
 *
 * Plugins can be identified by their plugin file (relative path to the main
 * plugin file from the root plugin directory) or their slug.
 *
 * This doesn't validate whether or not the plugin actually exists.
 *
 * @since 0.3.0
 *
 * @param string $plugin_file Plugin slug or relative path to the main plugin file.
 * @return boolean
 */
function is_plugin_file( $plugin_file ) {
	return '.php' === substr( $plugin_file, -4 );
}
