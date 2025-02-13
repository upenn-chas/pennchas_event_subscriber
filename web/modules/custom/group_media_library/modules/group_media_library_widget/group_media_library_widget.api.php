<?php

/**
 * @file
 * Hooks provided by the group media library widget module.
 */

/**
 * Alters group finders that are required for the media library widget.
 *
 * @param array $finders
 *   The finders.
 */
function hook_group_media_library_widget_group_finders_alter(array &$finders): void {
  $finders[] = 'custom_group_finder_id';
}
