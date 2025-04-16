<?php

namespace Drupal\penchas_block_group_role_condition;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Drupal\Core\Render\Markup;
use Drupal\Core\Database\Database;
use Drupal\smart_date_recur\Entity\SmartDateRule;

/**
 * Defines a custom Twig extension.
 */
class TwigExtension extends AbstractExtension
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        // Define a new Twig function.
        return [
            new TwigFunction('user_has_permission', [$this, 'userHasPermission']),
            new TwigFunction('recurring_date_text_decription', [$this, 'getRecurringDateText']),
            new TwigFunction('verify_role_access', [$this, 'verifyRoleAccess']),
            new TwigFunction('get_group_name', [$this, 'getGroupName'], array('is_safe' => array('html'))),
        ];
    }


    public function verifyRoleAccess($field_values)
    {
        if (!isset($field_values['#items'])) {
            // If there are no field values, stop further processing.
            return;
        }
        $current_user = \Drupal::currentUser();
        // Skip processing if the current user is an admin
        if ($current_user->hasRole('administrator')) {
            return true;
        }
        $group_roles_arr = [];
        foreach ($field_values['#items']->getValue() as $item) {
            $group_roles_arr[$item['value']] = $item['value'];
        }

        // $user_roles = $current_user->getRoles(TRUE);  // Get an array of the current user's roles
        foreach($current_user->getRoles(TRUE) as $role) {
            if(isset($group_roles_arr[$role])) {
                return TRUE;
            }
        }
        // Check if user has group roles.
        $connection = Database::getConnection();
        $query = $connection->select('group_relationship_field_data', 'grfd');
        $query->innerJoin('group_relationship__group_roles', 'grgr', 'grfd.id = grgr.entity_id');
        $query->fields('grgr', ['group_roles_target_id'])
            ->condition('grfd.entity_id', $current_user->id(), '=')
            ->condition('grfd.plugin_id', 'group_membership', '=');

        // Execute the query and fetch all results as an associative array.
        $results = $query->distinct()->execute()->fetchAllAssoc('group_roles_target_id');
        if(!empty($results)) {
            foreach($results as $role => $data) {
                if(isset($group_roles_arr[$role])) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function getGroupName()
    {
        $current_uri = \Drupal::request()->getRequestUri();
        $uri_parts = explode('/', $current_uri);
        $uri_parts = array_filter($uri_parts);
        $groupName = '';
        if (isset($uri_parts)) {
            // $variables['group_machine_label'] = $uri_parts['2'];
            $variables['short_name'] = $uri_parts['2'];
            $connection = Database::getConnection();
            $query = $connection->select('groups_field_data', 'hm');
            $query->fields('hm', ['label']);
            $query->condition('hm.id', $uri_parts['2']);
            $group_results = $query->execute()->fetchObject();
            $groupName = $group_results->label;

            return $groupName;
        }
    }

    public function getRecurringDateText($field_values)
    {
        if ($field_values) {
            $smartDate = SmartDateRule::load($field_values);
            $ouput = $smartDate->getTextRule();
            $ouput['#time'] = '';
            $ouput['#time_separator'] = '';
            return \Drupal::service('renderer')->render($ouput);
        }
    }

    public function userHasPermission($permission)
    {
        return \Drupal::service('pennchas_common.access_check')->check($permission);
    }
}
