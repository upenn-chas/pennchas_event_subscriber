<?php

namespace Drupal\event_feedback\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Pager\PagerManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\event_feedback\Plugin\Form\FilterForm;
use Drupal\event_feedback\Plugin\Form\WebformReportForm;
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

        $intendedOutcomeValue = \Drupal::service('pennchas_common.field_values_label')->values($node, 'field_intended_outcomes');
        $eventIntendedElements = $webform->getElementsDecoded();
        $eventIntendedElements['event_intended_to']['#title'] = $eventIntendedElements['event_intended_to']['#title'] . ': ' . implode(' | ', $intendedOutcomeValue);

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
        $filterForm = \Drupal::formBuilder()->getForm(new FilterForm());
        $reportData = $this->reportData($request, $filterForm, $page);

        $reportData = $this->renderer->render($reportData);

        $title = $this->t('Participations Survey Report');

        return [
            '#theme' => 'report_page',
            '#breadcrumbTitle' => $title,
            '#title' => $title,
            '#data' => $reportData
        ];
    }

    public function reportData($filters, $form, $page = 0)
    {
        $rows = $this->buildTable($filters, $page, true);
        $total = $rows['total'];
        unset($rows['total']);
        $rows = $this->renderer->render($rows);
        \Drupal::service('pager.manager')->createPager($total, $this->pageLength);
        $header = NULL;
        if ($total > 0) {
            $header = [
                '#markup' => '<a target="_blank" class="views-display-link" id="export-btn" href="' . Url::fromRoute('event_feedback.report_export')->toString() . '">' . $this->t('Export') . '</a>',
            ];
        }
        return [
            '#theme' => 'report_container',
            '#exposed' => $form,
            '#rows' => $rows,
            '#header' => $header,
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

    public function webformReport()
    {
        $webformId = \Drupal::request()->query->get('wid');
        $pageTitle = $this->t('Webform Report');
        $webformReportData = '';
        if ($webformId) {
            $page = \Drupal::request()->query->get('page', 0);
            $page = $page < 0 ? 0 : $page;
            $length = $this->pageLength;

            [$header, $rows, $total, $ccount, $title] = \Drupal::service('event_feedback.webform_report_service')->buildReport($webformId, $page, $length);
            $pageTitle .= ' - '. $title;
            $rows = [
                '#theme' => 'report_table',
                '#table_header' => [$header],
                '#ccount' => $ccount,
                '#table_body' => $rows
            ];

            $rows = $this->renderer->render($rows);
            \Drupal::service('pager.manager')->createPager($total, $length);

            $header = [
                '#markup' => '<span></span>'
            ];
            if ($total > 0) {
                $header['#markup'] = '<a target="_blank" class="views-display-link" id="export-btn" href="' . Url::fromRoute('event_feedback.webform_report_export', ['webformId' => $webformId])->toString() . '">' . $this->t('Export') . '</a>';
            }

            $reportData = [
                '#theme' => 'report_container',
                '#rows' => $rows,
                '#header' => $header,
                '#pager' => ['#type' => 'pager']
            ];

            $webformReportData = $this->renderer->render($reportData);
        }
        $form = \Drupal::formBuilder()->getForm(new WebformReportForm($webformId));

        return [
            '#theme' => 'report_page',
            '#breadcrumbTitle' => $this->t('Webform Report'),
            '#title' => $pageTitle,
            '#preHeader' => $form,
            '#data' => $webformReportData
        ];
    }

    public function reportExport()
    {
        $request = \Drupal::requestStack()->getSession()->get('participantsSurvey', []);
        $data = \Drupal::service('event_feedback.csv_report_service')->buildReport($this->eventFeedbackWebformId, $request, -1);

        $csvFile = new StreamedResponse();
        $csvFile->setCallback(function () use ($data) {
            $handler = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($handler, $row);
            }
            fclose($handler);
        });

        $csvFile->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $csvFile->headers->set('Content-Disposition', 'attachment; filename="participant_survey.csv"');
        return $csvFile;
    }

    public function perEventReportExport(Node $node)
    {
        $data = \Drupal::service('event_feedback.csv_per_event_report_service')->build($node->id(), $this->eventFeedbackWebformId, $node);

        $csvFile = new StreamedResponse();
        $csvFile->setCallback(function () use ($data) {
            $handler = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($handler, $row);
            }
            fclose($handler);
        });

        $csvFile->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $csvFile->headers->set('Content-Disposition', 'attachment; filename="event_survey_report.csv"');
        return $csvFile;
    }

    public function webformReportExport(string $webformId)
    {
        $data = \Drupal::service('event_feedback.csv_webform_report_service')->buildReport($webformId);
        $csvFile = new StreamedResponse();
        $csvFile->setCallback(function () use ($data) {
            $handler = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($handler, $row);
            }
            fclose($handler);
        });

        $csvFile->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $csvFile->headers->set('Content-Disposition', 'attachment; filename="'. $webformId .'.csv"');
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
