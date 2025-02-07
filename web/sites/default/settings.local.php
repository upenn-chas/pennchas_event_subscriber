<?php

/**
 * Database settings:
 *
 * The $databases array specifies the database connection or
 * connections that Drupal may use.  Drupal is able to connect
 * to multiple databases, including multiple types of databases,
 * during the same request.
 *
 * One example of the simplest connection array is shown below. To use the
 * sample settings, copy and uncomment the code below between the @code and
 * @endcode lines and paste it after the $databases declaration. You will need
 * to replace the database username and password and possibly the host and port
 * with the appropriate credentials for your database system.
 *
 * The next section describes how to customize the $databases array for more
 * specific needs.
 *
 * @code
 * $databases['default']['default'] = [
 *   'database' => 'database_name',
 *   'username' => 'sql_username',
 *   'password' => 'sql_password',
 *   'host' => 'localhost',
 *   'port' => '3306',
 *   'driver' => 'mysql',
 *   'prefix' => '',
 *   'collation' => 'utf8mb4_general_ci',
 * ];
 * @endcode
 */

 $databases['default']['default'] = [
    'database' => 'penchas_feb',
    'username' => 'root',
    'password' => 'Admin@1234',
    'host' => 'localhost',
    'port' => '3306',
    'driver' => 'mysql',
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
  ];
$config['recaptcha_v3.settings']['site_key'] = '6LeJHJ8qAAAAAApHFIjYa8lypKlaVbf1RxsgdzCj';
$config['recaptcha_v3.settings']['secret_key'] = '6LeJHJ8qAAAAALyeRIub9_k4cmszXvAPOkV2Azc6';

$config['symfony_mailer.mailer_transport.smtp']['configuration']['user'] = 'smtp-relay/collegehouses.upenn.edu';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['pass'] = 'oktad;Kneb63GlovUthid';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['host'] = 'smtp-relay.upenn.edu';
$config['symfony_mailer.mailer_transport.smtp']['configuration']['port'] = 25;

$config['system.logging']['error_level'] = 'verbose';
$settings['hash_salt'] = '8f9a3427cd8d1237e9a745ce1738bc0ea9237f0a7b6b2d45d4c8b443404f02c7';
