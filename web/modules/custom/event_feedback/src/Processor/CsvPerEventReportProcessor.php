<?php

namespace Drupal\event_feedback\Processor;

use Drupal\node\Entity\Node;

class CsvPerEventReportProcessor
{
    public function process(array $dbData, array $headerDetails, array $footer, array $webformElements, Node $node)
    {
        $formattedData = $this->formatData($dbData, $headerDetails['indexes'], $headerDetails['default'], $webformElements);
        return $this->buildData($headerDetails['header'], $formattedData, $footer, $headerDetails['indexes'], $node);
    }

    protected function formatData(array $dbData, array $columnIndexes, array $defaultValue, array $webformElements)
    {
        $submissionRows = [];

        foreach ($dbData as $submission) {
            $sid = $submission['sid'];
            $name = $submission['name'];
            $value = $submission['value'];
            $rowIndex = $columnIndexes[$name];

            if (!isset($submissionRows[$sid])) {
                $submissionRows[$sid] = $defaultValue;
            }

            if ($name === 'why_choose_event') {
                $submissionRows[$sid][$rowIndex] .= ($submissionRows[$sid][$rowIndex] === '' ? '' : ', ') . $webformElements[$name]['#options'][$value];
            } else if ($name === 'event_like_rating') {
                $submissionRows[$sid][$rowIndex] = $value;
            } else {
                $submissionRows[$sid][$rowIndex] = $webformElements[$name]['#options'][$value];
            }
        }

        return array_values($submissionRows);
    }

    protected function buildData($header, $body, $footer, $columnIndexes, $node)
    {
        $data = [];
        $intendedOutcomes = $this->_getIndentedOutcomes($node);
        $header[$columnIndexes['event_intended_to']] .= ", {$intendedOutcomes}?";
        $data[] = $header;
        $data = array_merge($data, $body);
        $data[] = $footer;
        return $data;
    }

    protected function _getIndentedOutcomes($event)
    {
        $intendedOutcomeField = $event->get('field_intended_outcomes');
        $intendedOutcomeValue = $intendedOutcomeField->getValue();
        $fieldSettings = $intendedOutcomeField->getDataDefinition()->getSettings();
        $allowedValues = $fieldSettings['allowed_values'];

        $intendedOutcomes = [];

        foreach ($intendedOutcomeValue as $value) {
            $intendedOutcomes[] = $allowedValues[$value['value']];
        }

        return implode(' | ', $intendedOutcomes);
    }
}
