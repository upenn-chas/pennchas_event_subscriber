<?php 

namespace Drupal\event_feedback\Trait;

use Drupal\webform\Entity\Webform;
use Exception;

trait ReportWebformTrait {
    /**
     * Get the webform elements
     * 
     * @var string $webformId
     */

    protected function getWebform(string $webformId)
    {
        $webform = Webform::load($webformId);
        if (!$webform) {
            throw new Exception("'{$webformId}' is invalid.");
        }
        return $webform;
    }
}