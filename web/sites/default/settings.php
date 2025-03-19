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
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
$settings['config_sync_directory'] = '../config/';
ini_set('memory_limit', '-1');


// TODO: Remove below config from here and add them in settings.local.php file on server.

$config['recaptcha_v3.settings']['site_key'] = '6LezWaAqAAAAAJe89jUpHevuVFBmn2A4JIJcfMuP';
$config['recaptcha_v3.settings']['secret_key'] = '6LezWaAqAAAAADn_uR9jf-B4a86kj5ZVtIP4OPtF';

$config['symfony_mailer.mailer_transport.smtp']['configuration']['user'] = 'smtp-relay/collegehouses.upenn.edu';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['pass'] = 'oktad;Kneb63GlovUthid';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['host'] = 'smtp-relay.upenn.edu';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['port'] = 25;

if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  if($_ENV['PANTHEON_ENVIRONMENT'] == 'dev'){
      $config['search_api.server.pantheon_search']['backend_config']['connector'] = 'pantheon';
  }
}

$settings['config_exclude_modules'] = ['simplesamlphp_auth'];

$login_url = '/saml_login?destination=' . $_SERVER['REQUEST_URI'];
return new Symfony\Component\HttpFoundation\RedirectResponse($login_url);