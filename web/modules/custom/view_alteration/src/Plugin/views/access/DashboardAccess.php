<?php

namespace Drupal\view_alteration\Plugin\views\access;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\PermissionHandlerInterface;
use Drupal\views\Attribute\ViewsAccess;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Custom plugin for pending event view access.
 *
 * @ingroup views_access_plugins
 * 
 * @ViewsAccess(
 *   id = "dashboard_access",
 *   title = @Translation("Dashboard Access"),
 *   help = @Translation("Access will be granted to users with the specified permission string.")
 * )
 * 
 */
class DashboardAccess extends AccessPluginBase implements CacheableDependencyInterface
{
    /**
     * {@inheritdoc}
     */
    protected $usesOptions = TRUE;

    /**
     * The permission handler.
     *
     * @var \Drupal\user\PermissionHandlerInterface
     */
    protected $permissionHandler;

    /**
     * Constructs a Permission object.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin ID for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\user\PermissionHandlerInterface $permission_handler
     *   The permission handler.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, PermissionHandlerInterface $permission_handler)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->permissionHandler = $permission_handler;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('user.permissions')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function alterRouteDefinition(Route $route)
    {
        $route->setRequirement('_dashboard_access', 'view_alteration.dashboard_access::access');
        $route->setOption('permission', $this->options['dashboard_perm']);
    }


    /**
     * {@inheritdoc}
     */
    public function summaryTitle()
    {
        $permissions = $this->permissionHandler->getPermissions();
        if (isset($permissions[$this->options['dashboard_perm']])) {
            return $permissions[$this->options['dashboard_perm']]['title'];
        }

        return $this->t($this->options['dashboard_perm']);
    }

    /**
     * {@inheritdoc}
     */
    public function access(AccountInterface $account)
    {
        return \Drupal::service('pennchas_common.access_check')->check($this->options['dashboard_perm']);
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        $options = parent::defineOptions();
        $options['dashboard_perm'] = ['default' => ''];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(&$form, FormStateInterface $form_state)
    {
        parent::buildOptionsForm($form, $form_state);
        $perms = [];
        $permissions = $this->permissionHandler->getPermissions();
        foreach ($permissions as $perm => $perm_item) {
            $provider = $perm_item['provider'];
            if ($provider === 'penchas_custom_token') {
                $perms[$perm] = strip_tags($perm_item['title']);
            }
        }

        $form['dashboard_perm'] = [
            '#type' => 'select',
            '#options' => $perms,
            '#title' => $this->t('Dashboard Permission'),
            '#default_value' => $this->options['dashboard_perm'],
            '#required' => TRUE,
            '#description' => $this->t('Only users with the selected dashboard permission will be able to access this display.'),
        ];
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
