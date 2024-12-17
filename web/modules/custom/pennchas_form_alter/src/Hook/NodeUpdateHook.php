<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRelationshipInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Hook\Trait\EntityHookTrait;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeUpdateHook
{
    use EntityHookTrait;

    function handle(NodeInterface $node)
    {
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_EVENT) {
            $this->handleEvent($node);
        }
    }

    protected function handleEvent(Node $node): void
    {
        $result = $this->processHouseChangeData($node);

        if ($result['new']) {
            $houses = Group::loadMultiple($result['new']);
            foreach ($houses as $house) {
                $existingRelationship = $house->getRelationshipsByEntity($node);
                if (empty($existingRelationship)) {
                    $house->addRelationship($node, 'group_node:' . $node->getType());
                }
            }
        }
        if ($result['remove']) {
            $houses = Group::loadMultiple($result['remove']);
            foreach ($houses as $house) {
                $relationships = $house->getRelationshipsByEntity($node);
                if (!empty($relationships)) {
                    array_walk($relationships, function (GroupRelationshipInterface $rel) {
                        $rel->delete();
                    });
                }
            }
        }
    }

    protected function processHouseChangeData(Node $node)
    {
        $existingHouses = array_column($node->original->get('field_groups')->getValue(), 'target_id');
        $newHouses = array_column($node->get('field_groups')->getValue(), 'target_id');

        $existingHouses = array_flip($existingHouses);

        $newHousesRel = [];
        $houseRelDel = [];

        foreach ($newHouses as $houseId) {
            if (isset($existingHouses[$houseId])) {
                unset($existingHouses[$houseId]);
            } else {
                $newHousesRel[] = $houseId;
            }
        }

        if ($existingHouses) {
            $houseRelDel = array_flip($existingHouses);
        }

        return [
            'new' => $newHousesRel,
            'remove' => $houseRelDel
        ];
    }
}
