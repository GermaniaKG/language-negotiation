<?php
/**
 * Test suite bootstrap.
 *
 * 1. Tries to include Composer vendor/autoload.php; dies if it does not exist.
 * 2. Loads Dotenv.
 */

$root_path = dirname(__DIR__);

// ---------------------------------------------------------
//   1. Composer Autoloader
// ---------------------------------------------------------

$autoloader = $root_path . '/vendor/autoload.php';
if (!is_readable( $autoloader )) {
    die(sprintf("\nMissing Composer's Autoloader '%s'; run 'composer update' first.\n\n", $autoloader));
}


