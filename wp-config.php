<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'portfoliowc' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '6GGtp&e6l1D21lc2=DYNbIVm>{+Pw|>:`K7tk#Wx~ bvQ)5+RR<Eb%-J~VI-zfVp' );
define( 'SECURE_AUTH_KEY',  'RxIeq;D-;2p[`1TGy^K~>|~!^;;6W1]gIeAVIDb&4?p~%!)x=mWVph?>dVo;92_!' );
define( 'LOGGED_IN_KEY',    's}J(75_}K2S,n~|&Q^g)wyH&a_DGG~)2#ANN)g?:H8{PqD:SQRmopL7~naE%#ph3' );
define( 'NONCE_KEY',        'RgS@l^:BLz~5QMc<E9Z+*T7en{~(&=Ktq/Z-e/_m4qzdsbIC~0d:59tXk{miY#~o' );
define( 'AUTH_SALT',        'S7U*, LI~P0+q>e]kexuAU<Wzg 6G}Tuz`rWSY*B?@)UsOTw=!B/5F#/:[FZUr/t' );
define( 'SECURE_AUTH_SALT', 'hRqaK8<_QA,gNzJJ)8)mq!T9p4SKh]iC|Rtc_WfJS4RVAPL`ZN@QZUGm#E$0?THr' );
define( 'LOGGED_IN_SALT',   '9[^#sU3&@r*-mw^vy1C]7zo+9/q2{<#G=c@0Od9wxhi7?]Y|jpk^+b.^`JvS~=!f' );
define( 'NONCE_SALT',       '8Wrn^MKqi3,8w7b{/{(n+0zUR=+%2)<#3:KThI#uFkOG@o9VA.yp$i4K.6(c`=VW' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
