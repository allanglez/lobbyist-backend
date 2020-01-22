<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Place the config directory outside of the Drupal root.
 */
$config_directories = array(
  CONFIG_SYNC_DIRECTORY => dirname(DRUPAL_ROOT) . '/config',
);

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}


/**
 * Always install the 'standard' profile to stop the installer from
 * modifying settings.php.
 */
$settings['install_profile'] = 'wxt';
$settings['hash_salt'] = 'wXQrYX4euWmxK6pfedj7sBctqzVUpsRKe8VYpC69q4U';

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'bizont_lobbyist_backend',
  'username' => 'bizont',
  'password' => 'JFVzmttqea5zcdtXyFhbLBhpWRBUrCng',
  'host' => 'localhost',
  'pdo' => array(PDO::ATTR_TIMEOUT => 2.0, PDO::MYSQL_ATTR_COMPRESS => 1),
);
 $settings['update_free_access'] = TRUE;
