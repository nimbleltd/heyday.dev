<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_2');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'c*nf~m66E7JBQqy#p<5:(0#zC7,ze!pyiV{Te?zm@Ig$$_I%^Lk70x9UB+r!sW1(');
define('SECURE_AUTH_KEY',  'H1n[?)?Y&X!-GY[Wl%+!_9`86vqWesN(Y1;6}7G6s+L->);QmF$73]PV!Y+W{O+M');
define('LOGGED_IN_KEY',    'qMa=E9!>J:/-P=KBD8MVGL`6eqkJ]xDs_<01DOt?T?t~~(!E3Y69&(#``E_=V.B~');
define('NONCE_KEY',        'dj:@p]Aw>B]V$}CU>r_v+sPvl`H@8*QP]PYSY^GYIvPUG|V1|q=__~jPqYsp{k<z');
define('AUTH_SALT',        'd1z)v~}U-?Mr~Om,Av7`N*$,(MFacQ@fvY;[8KoeSHxPV8bdZ(wPt3^RO9^6SjnS');
define('SECURE_AUTH_SALT', 'x Ik+>@8ywQYBs4NkAS3R&ly_S.igbQpu3+p%M~%L#w.c{v?H<N]>yK.Y^=n{3ii');
define('LOGGED_IN_SALT',   'L$gK.S9V4?(|yQe/BdM7Wp NaZp`A^Of`&E5=W4kW8PcMIuJ}Fu7E<:[6{$TBtM3');
define('NONCE_SALT',       '%Z<Jl4^33hr|[bg$g7NI?3*oKY@Zh&|_E&&$2#$uiVvSvo?ve<o0]/ATXMlp-6Ft');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
