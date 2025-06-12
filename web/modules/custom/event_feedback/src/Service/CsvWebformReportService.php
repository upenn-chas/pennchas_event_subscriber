<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Builder\HeaderBuilder;
use Drupal\event_feedback\Processor\CsvWebformReportProcessor;
use Drupal\event_feedback\Repository\ReportRepository;
use Drupal\event_feedback\Trait\ReportWebformTrait;

/**
 * Service to build the data for webform report CSV
 */
class CsvWebformReportService
{
    use ReportWebformTrait;

    protected ReportRepository $repository;
    protected CsvWebformReportProcessor $processor;
    protected HeaderBuilder $headerBuilder;

    public function __construct(
        ReportRepository $repository,
        CsvWebformReportProcessor $processor,
        HeaderBuilder $headerBuilder
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
        $this->headerBuilder = $headerBuilder;
    }

    public function buildReport(string $webformId, $page = -1, $length = 10)
    {
        $webform = $this->getWebform($webformId);
        $webformElements = $webform->getElementsOriginalDecoded();
        
        $submissions = $this->repository->getWebformSubmissionsData($webformId, $page, $length);
        $headerDetails = $this->headerBuilder->buildHeader('csvWebform', $webformElements);
        $data = $this->processor->process($submissions, $headerDetails, $webformElements);

    
        return $data;
    }

}
