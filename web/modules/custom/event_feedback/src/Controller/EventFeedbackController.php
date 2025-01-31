<?php

namespace Drupal\event_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Pager\PagerManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\event_feedback\Plugin\Form\FilterForm;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use PDO;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventFeedbackController extends ControllerBase
{
    /**
     * The event feedback webform machine name.
     *
     * @var string
     */
    protected $eventFeedbackWebformId = 'event_feedback';

    /**
     * The renderer service.
     *
     * @var \Drupal\Core\Render\RendererInterface
     */
    protected RendererInterface $renderer;

    /**
     * Page length.
     *
     * @var int
     */
    protected $pageLength = 10;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('renderer')
        );
    }

    public function feedback(Node $node)
    {
        if ($node->getType() !== 'chas_event') {
            return [
                '#markup' => $this->t('Content type not supported.'),
            ];
        }

        if (!$node->isPublished()) {
            return [
                '#markup' => $this->t('The event is not published.'),
            ];
        }

        $webform = Webform::load($this->eventFeedbackWebformId);

        if (!$webform) {
            return [
                '#markup' => $this->t('Webform not found.')
            ];
        }

        $eventDates = [];
        foreach ($node->get('field_event_schedule')->getValue() as $schedule) {
            $eventDates[] = $schedule['value'];
        }
        array_unique($eventDates);

        if ($this->hasUserAlreadySubmitted($this->eventFeedbackWebformId, $node->id())) {
            return [
                '#theme' => 'event_feedback_page',
                '#node' => $node,
                '#message' => 'You have already submitted your feedback. Thank You!',
                '#eventDates' => $eventDates
            ];
        }

        $eventIntendedElements = $webform->getElementsDecoded();
        $eventIntendedElements['event_intended_to']['#title'] = $eventIntendedElements['event_intended_to']['#title'] . ': ' . str_replace(', ', ' | ', $node->get('field_intended_outcomes')->getString());

        $webform->setElements($eventIntendedElements);
        return [
            '#theme' => 'event_feedback_page',
            '#node' => $node,
            '#eventDates' => $eventDates,
            '#webform' => [
                '#type' => 'webform',
                '#webform' => $webform->id(),
            ],
        ];
    }

    public function report()
    {
        $request = \Drupal::request()->request->all();
        $page = \Drupal::request()->query->get('page', 0);
        $page = $page < 0 ? 0 : $page;

        $rows = $this->buildTable($request, $page, true);
        $total = $rows['total'];
        unset($rows['total']);
        $rows = $this->renderer->render($rows);
        \Drupal::service('pager.manager')->createPager($total, $this->pageLength);
        return [
            '#theme' => 'report_page',
            '#exposed' => \Drupal::formBuilder()->getForm(new FilterForm()),
            '#title' => $this->t('Participations Survey Report'),
            '#rows' => $rows,
            '#header' => [
                '#markup' => '<a target="_blank" class="views-display-link" href="' . Url::fromRoute('event_feedback.report_export')->toString() . '">' . $this->t('Export') . '</a>',
            ],
            '#pager' => ['#type' => 'pager']
        ];
    }

    public function buildTable($filters, $page = 0, $includeTotal = false)
    {
        [$header, $rows, $total, $ccount] = \Drupal::service('event_feedback.report_service')->buildReport($this->eventFeedbackWebformId, $filters, $page, $this->pageLength);

        $tableData = [
            '#theme' => 'report_table',
            '#table_header' => $header,
            '#ccount' => $ccount,
            '#table_body' => $rows
        ];
        if ($includeTotal) {
            $tableData['total'] = $total;
        }

        return $tableData;
    }

    public function reportExport()
    {
        $request = \Drupal::request()->request->all();
        $data = \Drupal::service('event_feedback.csv_report_service')->buildReport($this->eventFeedbackWebformId, $request, -1);

        $csvFile = new StreamedResponse();
        $csvFile->setCallback(function () use ($data) {
            $handler = fopen('php://output', 'w');
            foreach($data as $row) {
                fputcsv($handler, $row);
            }
            fclose($handler);
        });
        
        $csvFile->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $csvFile->headers->set('Content-Disposition', 'attachment; filename="participant_survey.csv"');
        return $csvFile;
    }

    private function hasUserAlreadySubmitted($webformId, $nodeId)
    {
        return (bool) \Drupal::entityQuery('webform_submission')
            ->accessCheck(false)
            ->condition('entity_id', $nodeId)
            ->condition('webform_id', $webformId)
            ->condition('uid', \Drupal::currentUser()->id())
            ->count()->execute();
    }
}
