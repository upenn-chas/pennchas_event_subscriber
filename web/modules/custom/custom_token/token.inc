<?php
/**
* @file
* File to add custom token.
*/
use Drupal\Core\Render\BubbleableMetadata;
/**
* Implements hook_token_info().
*/
function custom_token_token_info() {
  $types['location'] = [
    'name' => t('Custom token'),
    'description' => t('Define custom tokens.'),
  ];
  $tokens['location_title'] = [
    'name' => t('location title'),
    'description' => t('Token to get current location title.'),
  ];
  return [
    'types' => $types,
    'tokens' => ['location' => $tokens],
  ];
}
/**
* Implements hook_tokens().
*/
function custom_token_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  if ($type == 'location') {
    $nid = \Drupal::request()->query->get('location');
    if ($nid) {
      $node_details = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    }
    foreach ($tokens as $name => $original) {
      // Find the desired token by name.
      switch ($name) {
          case 'location_title':
          // if ($node_details) {
            $replacements[$original] = 'testing'; //$node_details->label();
          // }
          break;
      }
    }
  }
  return $replacements;
}
