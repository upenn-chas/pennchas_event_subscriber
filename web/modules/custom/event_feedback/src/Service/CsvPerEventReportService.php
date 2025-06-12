<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Builder\HeaderBuilder;
use Drupal\event_feedback\Processor\CsvPerEventReportProcessor;
use Drupal\event_feedback\Processor\PerEventReportFooterProcessor;
use Drupal\event_feedback\Repository\ReportRepository;
use Drupal\event_feedback\Trait\ReportWebformTrait;
use Drupal\node\Entity\Node;

/**
 * Service to build the data for per event survey export
 */
class CsvPerEventReportService
{
    use ReportWebformTrait;

    protected ReportRepository $repository;
    protected CsvPerEventReportProcessor $processor;
    protected HeaderBuilder $headerBuilder;
    protected PerEventReportFooterProcessor $footer;

    public function __construct(
        ReportRepository $repository,
        CsvPerEventReportProcessor $processor,
        PerEventReportFooterProcessor $footer,
        HeaderBuilder $headerBuilder
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
        $this->footer = $footer;
        $this->headerBuilder = $headerBuilder;
    }

    public function build(int $eventId, string $webformId, Node $node)
    {
        $webform = $this->getWebform($webformId);
        $webformElements = $webform->getElementsOriginalDecoded();

        $submissions = $this->repository->getEventSubmissions($eventId, $webformId);
        $submissionsFooter = $this->repository->getEventSubmissionSummary($eventId, $webformId);
        $headerDetails = $this->headerBuilder->buildHeader('csvEvent', $webformElements);
        $totalSubmission = $this->repository->getTotalEventSubmissions($eventId, $webformId);
        $footerData = $this->footer->process($submissionsFooter['data'], $headerDetails['indexes'], $totalSubmission, $webformElements);
        array_shift($footerData);
        return $this->processor->process($submissions['data'], $headerDetails, $footerData, $webformElements, $node);
    }

}
