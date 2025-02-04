<?php

namespace Drupal\event_feedback\Processor;

use Drupal\Core\Url;

class CsvReportProcessor
{
    public function process(array $dbData, array $headeDetails)
    {
        $formattedSubmissionData = $this->formatSubmissionData($dbData['submissions'], $headeDetails['indexes'], $headeDetails['default']);
        return $this->buildData($headeDetails['header'], $formattedSubmissionData[0], $dbData['events'], $formattedSubmissionData[1], $headeDetails['indexes']);
    }

    protected function formatSubmissionData($submissions, $columnIndexes, $defaultValues)
    {
        $submissionRows = [];
        $footer = $defaultValues;
        foreach ($submissions as $submission) {
            $nid = $submission['entity_id'];
            $rowIndex = $columnIndexes["{$submission['name']}:{$submission['value']}"];
            if (!isset($submissionRows[$nid])) {
                $submissionRows[$nid] = $defaultValues;
            }
            $submissionRows[$nid][$rowIndex] += $submission['count'];
            $footer[$rowIndex] += $submission['count'];
        }
        return [
            0 => $submissionRows,
            1 => $footer
        ];
    }

    protected function buildData(array $header, array $submissions, array $events, array $footer, array $columnIndexes)
    {
        $data = $header;
        foreach ($events as $event) {
            $nid = $event['nid'];
            $row = $submissions[$nid];
            $row[$columnIndexes['houses']] = $event['houses'];
            $row[$columnIndexes['event']] = $event['title'];
            $row[$columnIndexes['repondants']] = $event['respondant'];
            $row[$columnIndexes['event_report']] = Url::fromRoute('view.event_survey_report.page_1', ['node' => $nid], ['absolute' => true])->toString();
            $row[$columnIndexes['event_evaluation']] = $event['evaluated'] !== NULL ? Url::fromRoute('event_evaluation_operation_option', ['node' => $nid], ['absolute' => true])->toString() : '';
            $data[] = $row;
            $footer[2] += $row[2];
        }
        $footer[1] = 'Totals';
        $data[] = $footer;
        return $data;
    }
}
