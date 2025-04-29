<?php

namespace Drupal\url_alteration\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeStorageInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\node\NodeInterface;

/**
 * Class CustomNodeController.
 */
class CustomNodeController extends ControllerBase
{

    /**
     * The date formatter service.
     *
     * @var \Drupal\Core\Datetime\DateFormatterInterface
     */
    protected $dateFormatter;

    /**
     * The renderer service.
     *
     * @var \Drupal\Core\Render\RendererInterface
     */
    protected $renderer;

    /**
     * The entity repository service.
     *
     * @var \Drupal\Core\Entity\EntityRepositoryInterface
     */
    protected $entityRepository;
    
    /**
     * Constructs a NodeController object.
     *
     * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
     *   The date formatter service.
     * @param \Drupal\Core\Render\RendererInterface $renderer
     *   The renderer service.
     * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
     *   The entity repository.
     */
    public function __construct(RendererInterface $renderer)
    {
        // $this->dateFormatter = $date_formatter;
        $this->renderer = $renderer;
        // $this->entityRepository = $entity_repository;
    }



    /**
     * Displays add content links for available content types.
     *
     * Redirects to node/add/[type] if only one content type is available.
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *   A render array for a list of the node types that can be added; however,
     *   if there is only one node type defined for the site, the function
     *   will return a RedirectResponse to the node add page for that one node
     *   type.
     */
    public function addPage()
    {
        $definition = $this->entityTypeManager()->getDefinition('node_type');
        $build = [
            '#theme' => 'node_add_list',
            '#cache' => [
                'tags' => $this->entityTypeManager()->getDefinition('node_type')->getListCacheTags(),
            ],
        ];

        $content = [];

        $types = $this->entityTypeManager()->getStorage('node_type')->loadMultiple();
        uasort($types, [$definition->getClass(), 'sort']);
        // Only use node types the user has access to.
        foreach ($types as $type) {
            if(in_array($type->id(), ['program_community', 'room', 'reserve_room'])) {
                continue;
            }
            $access = $this->entityTypeManager()->getAccessControlHandler('node')->createAccess($type->id(), NULL, [], TRUE);
            if ($access->isAllowed()) {
                $content[$type->id()] = $type;
            }
            $this->renderer->addCacheableDependency($build, $access);
        }

        // Bypass the node/add listing if only one content type is available.
        if (count($content) == 1) {
            $type = array_shift($content);
            return $this->redirect('node.add', ['node_type' => $type->id()]);
        }

        $build['#content'] = $content;

        return $build;
    }
}
