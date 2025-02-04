<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Processor\PerEventReportProcessor;
use Drupal\event_feedback\Repository\ReportRepository;
use Drupal\webform\Entity\Webform;

class PerEventReportService
{
    protected ReportRepository $repository;
    protected PerEventReportProcessor $processor;

    public function __construct(
        ReportRepository $repository,
        PerEventReportProcessor $processor
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
    }

    public function buildFooterData(int $eventId, string $webformId, array $indexes)
    {
        $webformElements = $this->getWebformElements($webformId);
        $submissions = $this->repository->getEventSubmissions($eventId, $webformId);
        $totalSubmission = $this->repository->getTotalEventSubmissions($eventId, $webformId);
        $data = $this->processor->process($submissions['data'], $indexes, $totalSubmission ?? 1, $webformElements);
        

        return $data;
    }
    
    protected function getWebformElements(string $webformId)
    {
        $webform = Webform::load($webformId);
        return $webform->getElementsOriginalDecoded();
    }
}
