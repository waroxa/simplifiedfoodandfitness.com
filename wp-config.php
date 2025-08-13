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
define( 'DB_NAME', 'dbrmgz9gujouw9' );

/** Database username */
define( 'DB_USER', 'u0ikygqupaxda' );

/** Database password */
define( 'DB_PASSWORD', '4iwx5buqeu99' );

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
define( 'AUTH_KEY',          'aCAlHPY%][}S0t3hxDH6c~bV`0h*o[8:?@9r&N-rNzKhBM%}3*n]5nwIA8mYn+aj' );
define( 'SECURE_AUTH_KEY',   'Q=>~~^,EzGk]*<vs{<>e,u;3`L{cOw00%-(;4w%&nF3(ja-hdymOw9Y*~QvXuPZ-' );
define( 'LOGGED_IN_KEY',     'cCnkLHjD ~RYkwJE5H-NbE0_J#P3!Ko|0R|RCW+-s.`dAXzU$jbKkk-e)<9f:)yO' );
define( 'NONCE_KEY',         'vDeDkrUWjv>V]h;&1tH+2,&]t4Dzc,6U|fyIt_K|l.enxn[2ip$iknH! F4`&_jv' );
define( 'AUTH_SALT',         ';Ci^(dO.A:4_ZT)$nbkqo-Y(?O:AWX(O?D`L69D1)EXB(?Pw<R~VHsL:3>5R;=^h' );
define( 'SECURE_AUTH_SALT',  'DRfH1k;lBIavkx(DK2_4pE3t+H(=S0M799xJ0+7)(*O2oLpMPgtS]f2?o1Oq,O9q' );
define( 'LOGGED_IN_SALT',    '[]PeY4/Z`a.2f1TROXve:%UqYo.U${8vMm`#s!w5y5-pmQgVqOtgd]z@ ;0}[TTE' );
define( 'NONCE_SALT',        'xgtjF8c+>oFP|wR6~xJt]e&I#3)%<WDr/+@&&ocx6w!SleTMfd@[EVT8z |*nY=*' );
define( 'WP_CACHE_KEY_SALT', 'B}tr8AP]Vh&6o>=B9buq[1I:?Fu4G/I?T`YH](TzQ[]%xR9}`,$n.}Mt%$K,;%3:' );
define( 'WP_DEBUG', false);
define( 'WP_DEBUG_LOG', false);

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'avn_';


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
