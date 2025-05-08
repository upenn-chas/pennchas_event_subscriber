<?php

namespace Drupal\pennchas_form_alter\Hook\Trait;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
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
        $this->renderLayoutBuilderContent($node);
    }

    protected function renderLayoutBuilderContent(Node $node)
    {
        $layoutBuilderContent = '';
        if ($node->hasField('layout_builder__layout')) {
            $layout = $node->get('layout_builder__layout')->getValue();
            $build = [];

            foreach ($layout as $sectionArr) {
                $section = $sectionArr['section'];
                foreach ($section->getComponents() as $component) {
                    if ($component instanceof SectionComponent) {
                        $build[] = $component->toRenderArray();
                    }
                }
            }

            $renderer = \Drupal::service('renderer');
            $rendered_text = $renderer->renderPlain($build);
            $plain_text = strip_tags($rendered_text);
            $plain_text = preg_replace('/[^a-zA-Z0-9\s]/', '',preg_replace('/\s{2,}/', ' ', str_replace("\n", ' ', $plain_text)));

            // Store in the text field.
            $node->set('field_rendered_layout', $plain_text);
        }
        return $layoutBuilderContent;
    }
}
