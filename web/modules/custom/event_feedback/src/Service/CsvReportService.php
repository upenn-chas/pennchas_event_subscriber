<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Builder\HeaderBuilder;
use Drupal\event_feedback\Processor\CsvReportProcessor;
use Drupal\event_feedback\Repository\ReportRepository;

/**
 * Service to build the data for participant survey report export
 */
class CsvReportService
{
    protected ReportRepository $repository;
    protected CsvReportProcessor $processor;
    protected HeaderBuilder $headerBuilder;

    public function __construct(
        ReportRepository $repository,
        CsvReportProcessor $processor,
        HeaderBuilder $headerBuilder
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
        $this->headerBuilder = $headerBuilder;
    }

    public function buildReport(string $webformId, array $filters, $page = -1, $length = 10)
    {
        $groups = \Drupal::service('pennchas_common.option_group')->options('house1', false);
        $submissions = $this->repository->getSubmissions($webformId, $filters, array_keys($groups), $page, $length);
        $headerDetails = $this->headerBuilder->buildHeader($webformId, 'csv');
        $data = $this->processor->process($submissions, $headerDetails);

        return $data;
    }
}
