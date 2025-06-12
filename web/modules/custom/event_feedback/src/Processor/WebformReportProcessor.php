<?php

namespace Drupal\event_feedback\Processor;

use Drupal\Core\Url;
use Drupal\webform\Entity\Webform;

/**
 * Webform report data processor
 */
class WebformReportProcessor
{
    public function process(array $dbData, array $headerDetails, int $serial, array $webformElements)
    {
        $formattedSubmissionData = $this->formatData($dbData['submissions'], $headerDetails['indexes'], $headerDetails['default'], $serial, $webformElements);
        return  $formattedSubmissionData;
    }

    protected function formatData(array $dbData, array $columnIndexes, array $defaultValues, int $serial, array $webformElements)
    {
        $submissionRows = [];

        foreach ($dbData as $submission) {
            $sid = $submission['sid'];
            $name = $submission['name'];
            $value = $submission['value'];

            if (!isset($columnIndexes[$name])) {
                continue;
            }

            $rowIndex = $columnIndexes[$name];
            if (!isset($submissionRows[$sid])) {
                $submissionRows[$sid] = $defaultValues;
                $submissionRows[$sid][$columnIndexes['serial']] = $serial++;
                $submissionRows[$sid][$columnIndexes['sid']] = $submission['sid'];
                $submissionRows[$sid][$columnIndexes['created']] = $submission['created'];
                $submissionRows[$sid][$columnIndexes['user']] = $submission['user'];
                $submissionRows[$sid][$columnIndexes['remote_addr']] = $submission['remote_addr'];
            }
            $row = &$submissionRows[$sid];

            $row[$rowIndex][] = $webformElements[$name]['#options'][$value] ?? $value;
        }
        foreach ($submissionRows as &$row) {
            foreach ($row as $key => &$value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
            }
            $row[$columnIndexes['created']] = \Drupal::service('date.formatter')->format($row[$columnIndexes['created']], 'report_date_and_time');
            ksort($row);
        }

        return array_values($submissionRows);
    }
}
