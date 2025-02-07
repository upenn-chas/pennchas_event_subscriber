<?php

namespace Drupal\event_feedback\Processor;

use Drupal\Core\Url;

/**
 * Participant survey report data processor
 */
class ReportProcessor
{
    public function process(array $dbData, array $headerDetails)
    {
        $formattedSubmissionData = $this->formatSubmissionData($dbData['submissions'], $headerDetails['indexes'], $headerDetails['default']);
        $data = [];
        $columnIndexes = $headerDetails['indexes'];
        $title = t('Link');
        foreach ($dbData['events'] as $event) {
            $nid = $event['nid'];
            $row = $formattedSubmissionData[$nid];
            $row[$columnIndexes['houses']] = $event['houses'];
            $row[$columnIndexes['event']] = $event['title'];
            $row[$columnIndexes['repondants']] = $event['respondant'];
            $perEventReportUrl = Url::fromRoute('view.event_survey_report.page_1', ['node' => $nid])->toString();
            $row[$columnIndexes['event_report']] = [
                '#markup' => "<a href='{$perEventReportUrl}' target='_blank'>{$title}</a>"
            ];
            if ($event['evaluated'] !== NULL) {
                $evaluationUrl = Url::fromRoute('event_evaluation_operation_option', ['node' => $nid])->toString();
                $row[$columnIndexes['event_evaluation']] =  [
                    '#markup' => "<a href='{$evaluationUrl}' target='_blank'>{$title}</a>"
                ];
            } else {
                $row[$columnIndexes['event_evaluation']] = '';
            }
            $data[] = $row;
        }

        return $data;
    }

    protected function formatSubmissionData($submissions, $columnIndexes, $defaultValues)
    {
        $formattedData = [];
        foreach ($submissions as $submission) {
            $nid = $submission['entity_id'];
            $rowIndex = $columnIndexes["{$submission['name']}:{$submission['value']}"];
            if (!isset($formattedData[$nid])) {
                $formattedData[$nid] = $defaultValues;
            }
            $formattedData[$nid][$rowIndex] += $submission['count'];
        }
        return $formattedData;
    }
}
