<?php

namespace Drupal\view_alteration\Plugin\views\access;

use Drupal\common_utils\Check\PermissionCheck;
use Drupal\common_utils\Checker\AccessCheck;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Custom plugin to access reports.
 *
 * @ingroup views_access_plugins
 * 
 * @ViewsAccess(
 *   id = "report_access",
 *   title = @Translation("Report Access"),
 *   help = @Translation("Access reports view.")
 * )
 */
class ReportAccess extends AccessPluginBase
{
    /**
     * Report view permission.
     * 
     * @var string
     */
    protected $reportAccessPermission = 'access reports';

    /**
     * {@inheritdoc}
     */
    public function summaryTitle()
    {
        return $this->t('Report access');
    }
    /**
     * {@inheritdoc}
     */
    public function alterRouteDefinition(Route $route)
    {
        $route->setRequirement('_report_access', 'view_alteraion.report_access_handler::access');
        $route->setOption('permission', $this->reportAccessPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function access(AccountInterface $account)
    {
        return \Drupal::service('pennchas_common.access_check')->check($this->reportAccessPermission);
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static($configuration, $plugin_id, $plugin_definition);
    }
}
