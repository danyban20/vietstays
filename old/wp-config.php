<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbm4qacqe3n61l' );

/** Database username */
define( 'DB_USER', 'umgziuri8h0zn' );

/** Database password */
define( 'DB_PASSWORD', 'l2wbtbwy4xgo' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '&@UEw0tGNt)P@+**S?0*tS9@0G]x9_K.F-wyC.{ygS@l<Kl,P1d|N Dyr$$TM4Z ' );
define( 'SECURE_AUTH_KEY',   '[=rWc3UhKpTX7DTk`JGzN3VVmYUh>O10nmH{pzSd+|uNP3H d!H@,le/25hl~4&%' );
define( 'LOGGED_IN_KEY',     'g$Fj8a;x?]#ho.#6}~t%aK1g7>kY{,@>`$V+5ix)Y<qM gU`G8PRS2)x|ti-|R;l' );
define( 'NONCE_KEY',         '(HK]$%z[fV{7Eg+g12E%Rs&1wi.Lcn[DOa(*p,}nVmFO9}SK-$HJ7$jDt[F-c_tE' );
define( 'AUTH_SALT',         '!q2,8ZeL!ll%DSM:^}737:4hE-5gl#uIj3C`LA9{l*6U<r_;T1aiBe /GiB[:t>f' );
define( 'SECURE_AUTH_SALT',  'X?$5}k6MF[Y.OPnTQ5:eY4*Ecsp2`:0O`gw9,hYW4UI:qsHmACCtJRzs F1)~k>?' );
define( 'LOGGED_IN_SALT',    'Jme|D8UspOVWsk7Ag6D~9p(iRYAqj46`p`v@z~e.0+w6zQPUsP9(zV AqlwKh@tF' );
define( 'NONCE_SALT',        'pe9AB/pSkNjzfo<;4y#*2!Ci2*oEg3`Y+K:3$gkd*K7~ezX/Z<jY4{JwV]^Sw [H' );
define( 'WP_CACHE_KEY_SALT', 'T&yQ(5YG7z2-c}=FECuFT?Zg=v<i^L3{h}mjSGX!jm0aj&Rq*ao0j0YT84vnwYXP' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'gyh_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
@include_once('/var/lib/sec/wp-settings-pre.php'); // Added by SiteGround WordPress management system
require_once ABSPATH . 'wp-settings.php';
@include_once('/var/lib/sec/wp-settings.php'); // Added by SiteGround WordPress management system
