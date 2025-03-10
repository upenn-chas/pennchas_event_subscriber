<?php

namespace Drupal\view_alteration\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\group\Access\GroupPermissionHandlerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\PermissionHandlerInterface;
use Drupal\views\Attribute\ViewsAccess;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Custom access plugin for moderator
 *
 * @ingroup views_access_plugins
 * 
 * @ViewsAccess(
 *   id = "moderator_access",
 *   title = @Translation("Moderator Access"),
 *   help = @Translation("Access will be granted to users with the moderator permission.")
 * )
 * 
 */
class ModeratorAccess extends AccessPluginBase implements CacheableDependencyInterface
{
    /**
     * {@inheritdoc}
     */
    protected $usesOptions = FALSE;


    /**
     * {@inheritdoc}
     */
    public function alterRouteDefinition(Route $route)
    {
        $route->setRequirement('_moderator_access', 'view_alteration.moderator_access::access');
    }


    /**
     * {@inheritdoc}
     */
    public function summaryTitle()
    {
        return $this->t('Moderators');
    }

    /**
     * {@inheritdoc}
     */
    public function access(AccountInterface $account)
    {
        $nid = \Drupal::routeMatch()->getParameter('node');
        if ($nid) {
            $node = Node::load($nid);
            if ($node) {
                return \Drupal::service('pennchas_common.moderator_access_check')->checkForEntity($node);
            }
        }
        return FALSE;
    }


    /**
     * {@inheritdoc}
     */
    public function getCacheMaxAge()
    {
        return Cache::PERMANENT;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheContexts()
    {
        return ['user.permissions'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTags()
    {
        return [];
    }
}
