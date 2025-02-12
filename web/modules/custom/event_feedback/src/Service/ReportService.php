<?php

namespace Drupal\event_feedback\Service;

use Drupal\event_feedback\Builder\HeaderBuilder;
use Drupal\event_feedback\Processor\ReportProcessor;
use Drupal\event_feedback\Repository\ReportRepository;

/**
 * Service to build the data for participant survey report
 */
class ReportService
{
    protected ReportRepository $repository;
    protected ReportProcessor $processor;
    protected HeaderBuilder $headerBuilder;

    public function __construct(
        ReportRepository $repository,
        ReportProcessor $processor,
        HeaderBuilder $headerBuilder
    ) {
        $this->repository = $repository;
        $this->processor = $processor;
        $this->headerBuilder = $headerBuilder;
    }

    public function buildReport(string $webformId, array $filters, $page = 0, $length = 10)
    {
        $groups = \Drupal::service('pennchas_common.option_group')->options('house1', false);
        $submissions = $this->repository->getSubmissions($webformId, $filters, array_keys($groups), $page, $length);
        $headerDetails = $this->headerBuilder->buildHeader($webformId, 'report');
        $data = $this->processor->process($submissions, $headerDetails);

        return [
            $headerDetails['header'],
            $data,
            $submissions['total'],
            count($headerDetails['default'])
        ];
    }
}
