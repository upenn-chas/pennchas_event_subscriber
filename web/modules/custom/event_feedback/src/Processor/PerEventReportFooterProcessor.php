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
        $data[$columnIndexes['event_like_rating']] = round($formattedData[$columnIndexes['event_like_rating']]['value'] / $total, 1) .'';
        $data[$columnIndexes['event_intended_to']] = implode(', ', $formattedData[$columnIndexes['event_intended_to']]['value']);
        $data[$columnIndexes['why_choose_event']] = implode(', ', $formattedData[$columnIndexes['why_choose_event']]['value']);
        ksort($data);
        return array_merge([$total], $data);
    }

    protected function formatData(array $dbData, array $columnIndexes, $webformElements)
    {
        $data = [];
        foreach ($dbData as $ele) {
            if ($ele['name'] === 'event_like_rating') {
                $i = $columnIndexes['event_like_rating'];
                if (!isset($data[$i])) {
                    $data[$i] = [
                        'value' => (int) $ele['value'] * $ele['value_count']
                    ];
                } else {
                    $data[$i]['value'] += (int) $ele['value'] * $ele['value_count'];
                }
            } else if ($ele['name'] === 'event_intended_to') {
                $i = $columnIndexes['event_intended_to'];
                if (!isset($data[$i])) {
                    $data[$i] = [
                        'freq' => $ele['value_count'],
                        'value' => [$ele['value']]
                    ];
                } else {
                    if ($data[$i]['freq'] < $ele['value_count']) {
                        $data[$i]['freq'] = $ele['value_count'];
                        $data[$i]['value'] = [$webformElements['event_intended_to']['#options'][$ele['value']]];
                    } else if ($data[$i]['freq'] === $ele['value_count']) {
                        $data[$i]['value'][] = $webformElements['event_intended_to']['#options'][$ele['value']];
                    }
                }
            } else if ($ele['name'] === 'why_choose_event') {
                $i = $columnIndexes['why_choose_event'];
                if (!isset($data[$i])) {
                    $data[$i] = [
                        'freq' => $ele['value_count'],
                        'value' => [$webformElements['why_choose_event']['#options'][$ele['value']]]
                    ];
                } else {
                    if ($data[$i]['freq'] < $ele['value_count']) {
                        $data[$i]['freq'] = $ele['value_count'];
                        $data[$i]['value'] = [$webformElements['why_choose_event']['#options'][$ele['value']]];
                    } else if ($data[$i]['freq'] === $ele['value_count']) {
                        $data[$i]['value'][] = $webformElements['why_choose_event']['#options'][$ele['value']];
                    }
                }
            }
        }
        return $data;
    }
}
