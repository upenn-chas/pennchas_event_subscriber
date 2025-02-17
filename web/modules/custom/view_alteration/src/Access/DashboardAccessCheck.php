<?php

namespace Drupal\view_alteration\Access;

use Drupal\common_utils\Access\AccessCheck;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\Routing\Route;

class DashboardAccessCheck implements AccessInterface
{

    protected $viewUnpublishedContent = [
        'view any unpublished content',
        'view own unpublished content'
    ];

    protected $groupMemberPermission = [
        'my_events' => ['view unpublished group_node:chas_event entity', 'view own unpublished group_node:chas_event entity'],
        // 'reserve_room' => 'view unpublished group_node:reserve_room entity'
    ];

    /**
     * Access checker.
     * 
     * @var \Drupal\common_utils\Checker\AccessCheck
     */
    protected $accessChecker;

    public function __construct(AccessCheck $accessChecker)
    {
        $this->accessChecker = $accessChecker;
    }

    /**
     * Checks access for report view.
     * 
     * @param \Symfony\Component\Routing\Route $route
     *   The route to check against.
     *
     * @return \Drupal\Core\Access\AccessResultInterface
     *   
     */
    public function access(Route $route)
    {
        $permission = $route->getOption('permission') ?? NULL;
        if (!$permission) {
            return AccessResult::forbidden();
        }
        return AccessResult::allowedIf($this->accessChecker->check($permission));
    }
}
