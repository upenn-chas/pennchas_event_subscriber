<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Builder\HeaderBuilder;
use Drupal\event_feedback\Processor\WebformReportProcessor;
use Drupal\event_feedback\Repository\ReportRepository;
use Drupal\event_feedback\Trait\ReportWebformTrait;

/**
 * Service to build the data for webform report
 */
class WebformReportService
{
    use ReportWebformTrait;

    protected ReportRepository $repository;
    protected WebformReportProcessor $processor;
    protected HeaderBuilder $headerBuilder;

    public function __construct(
        ReportRepository $repository,
        WebformReportProcessor $processor,
        HeaderBuilder $headerBuilder
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
        $this->headerBuilder = $headerBuilder;
    }

    public function buildReport(string $webformId, $page = 0, $length = 10)
    {
        $webform = $this->getWebform($webformId);
        $webformElements = $webform->getElementsOriginalDecoded();
        
        $submissions = $this->repository->getWebformSubmissions($webformId, $page, $length);
        $headerDetails = $this->headerBuilder->buildHeader('webform', $webformElements);
        $webformTitle = $webform->label();
        $data = $this->processor->process($submissions, $headerDetails, ($page * $length) + 1, $webformElements);

    
        return [
            $headerDetails['header'],
            $data,
            $submissions['total'],
            count($headerDetails['header']),
            $webformTitle
        ];
    }

}
