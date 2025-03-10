<?php

namespace Drupal\view_alteration\Access;

use Drupal\common_utils\Access\ModeratorCheck;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\Routing\Route;

class ModeratorAccessCheck implements AccessInterface
{

    /**
     * Access checker.
     * 
     * @var \Drupal\common_utils\Checker\ModeratorCheck
     */
    protected $accessChecker;

    public function __construct(ModeratorCheck $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * Checks access for moderator view.
     * 
     * @param \Symfony\Component\Routing\Route $route
     *   The route to check against.
     *
     * @return \Drupal\Core\Access\AccessResultInterface
     *   
     */
    public function access(Route $route)
    {
        $nid = \Drupal::routeMatch()->getParameter('node');
        if ($nid) {
            $node = Node::load($nid);
            if ($node) {
                return AccessResult::allowedIf($this->accessChecker->checkForEntity($node));
            }
        }
        return AccessResult::forbidden();
    }
}
