<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Processor\PerEventReportFooterProcessor;
use Drupal\event_feedback\Repository\ReportRepository;
use Drupal\webform\Entity\Webform;

class PerEventReportService
{
    protected ReportRepository $repository;
    protected PerEventReportFooterProcessor $processor;

    public function __construct(
        ReportRepository $repository,
        PerEventReportFooterProcessor $processor
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
    }

    public function buildFooterData(int $eventId, string $webformId, array $indexes)
    {
        $webformElements = $this->getWebformElements($webformId);
        $submissions = $this->repository->getEventSubmissionSummary($eventId, $webformId);
        $totalSubmission = $this->repository->getTotalEventSubmissions($eventId, $webformId);
        return $this->processor->process($submissions['data'], $indexes, $totalSubmission ?? 1, $webformElements);
    }
    
    protected function getWebformElements(string $webformId)
    {
        $webform = Webform::load($webformId);
        return $webform->getElementsOriginalDecoded();
    }
}
