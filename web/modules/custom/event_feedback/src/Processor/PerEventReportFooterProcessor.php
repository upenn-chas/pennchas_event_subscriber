<?php

namespace Drupal\event_feedback\Processor;

/**
 * Per event report footer processor
 */
class PerEventReportFooterProcessor
{
    public function process(array $dbData, array $columnIndexes, $total, array $webformElements)
    {
        $formattedData = $this->formatData($dbData, $columnIndexes, $webformElements);
        $data = [];
        $data[$columnIndexes['event_like_rating']] = round($formattedData[$columnIndexes['event_like_rating']]['value'] / $total, 1) . '';
        $data[$columnIndexes['event_intended_to']] = implode(', ', $formattedData[$columnIndexes['event_intended_to']]['value']);
        $data[$columnIndexes['why_choose_event']] = implode(', ', $formattedData[$columnIndexes['why_choose_event']]['value']);
        ksort($data);
        return array_merge([$total], $data);
    }

    protected function formatData(array $dbData, array $columnIndexes, $webformElements)
    {
        $data = [];

        foreach ($dbData as $ele) {
            $name = $ele['name'];
            $value = $ele['value'];
            $count = $ele['value_count'];

            if (!isset($columnIndexes[$name])) {
                continue;
            }

            $i = $columnIndexes[$name];
            $entry = &$data[$i];

            switch ($name) {
                case 'event_like_rating':
                    $entry['value'] = ($entry['value'] ?? 0) + ((int) $value * $count);
                    break;

                case 'event_intended_to':
                case 'why_choose_event':
                    $optionValue = $webformElements[$name]['#options'][$value] ?? $value;

                    if (!isset($entry)) {
                        $entry = ['freq' => $count, 'value' => [$optionValue]];
                    } else {
                        if ($entry['freq'] < $count) {
                            $entry = ['freq' => $count, 'value' => [$optionValue]];
                        } elseif ($entry['freq'] === $count) {
                            $entry['value'][] = $optionValue;
                        }
                    }
                    break;
            }
        }

        return $data;
    }
}
