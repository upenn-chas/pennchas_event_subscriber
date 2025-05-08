<?php

namespace Drupal\pennchas_form_alter\Hook\Trait;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

trait HousePageHookTrait
{
    protected function handleHousePage(Node $node)
    {
        $parentPageId = (int) $node->get('field_parent_page')->getString();
        $pageRef = null;
        if ($parentPageId) {
            $pageRef = Url::fromRoute('entity.node.canonical', ['node' => $parentPageId])->toString();
            $groupId = (int) $node->get('field_select_house')->getString();
            if ($groupId) {
                $group = Group::load($groupId);
                if ($group) {
                    $shortName = $group->get('field_short_name')->value;
                    if ($shortName && strpos($pageRef, '/' . $shortName) === 0) {
                        $pageRef = substr($pageRef, strlen($shortName) + 1);
                    }
                }
            }
        }
        $node->set('field_group_ref', $pageRef);
    }
}
