<?php

namespace Drupal\event_feedback\Builder;

use Drupal\webform\Entity\Webform;
use Exception;
use InvalidArgumentException;

class HeaderBuilder
{
    public function buildHeader(string $webformId, string $headerType)
    {
        return match ($headerType) {
            'report' => $this->reportHeader($webformId),
            'csv' => $this->csvHeader($webformId),
            default => throw new InvalidArgumentException("'{$headerType}' is invlaid header type.")
        };
    }

    protected function reportHeader(string $webformId)
    {
        $webformElements = $this->getWebformElements($webformId);
        $header = [
            [
                [
                    'title' => t('House(s)'),
                    'cspan' => 0,
                ],
                [
                    'title' => t('Event'),
                    'cspan' => 0,
                ],
                [
                    'title' => t('Respondants'),
                    'cspan' => 0
                ],
            ],
            [
                [
                    'title' => '',
                    'cspan' => 0,
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                ]
            ]
        ];
        $columnIndexes = [
            'houses' => 0,
            'event' => 1,
            'repondants' => 2
        ];
        $defaultRowValues = [
            '',
            '',
            0
        ];
        $colIndex = count($defaultRowValues);

        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $header[0][] = [
                    'title' => $ele['#title'],
                    'cspan' => count($ele['#options'])
                ];
                foreach ($ele['#options'] as $optKey => $opt) {
                    $columnIndexes["{$key}:{$optKey}"] = $colIndex;
                    $defaultRowValues[$colIndex] = 0;
                    $header[1][$colIndex++] = [
                        'title' => $opt,
                        'cspan' => 0
                    ];
                }
            }
        }
        $header[0][$colIndex] = [
            'title' => t('Event Report'),
            'cspan' => 0,
        ];
        $header[1][$colIndex] = [
            'title' => '',
            'cspan' => 0,
        ];
        $columnIndexes['event_report'] = $colIndex;
        $defaultRowValues[$colIndex++] = '';
        
        $header[0][$colIndex] = [
            'title' => t('Event Evaluation'),
            'cspan' => 0,
        ];
        $header[1][$colIndex] = [
            'title' => '',
            'cspan' => 0,
        ];
        $columnIndexes['event_evaluation'] = $colIndex;
        $defaultRowValues[$colIndex] = '';

        return [
            'header' => $header,
            'indexes' => $columnIndexes,
            'default' => $defaultRowValues
        ];
    }

    protected function csvHeader(string $webformId)
    {
        $webformElements = $this->getWebformElements($webformId);
        $header = [
            [
                'House(s)',
                'Event',
                'Respondants'
            ],
            [
                '',
                '',
                ''
            ]
        ];
        $columnIndexes = [
            'houses' => 0,
            'event' => 1,
            'repondants' => 2
        ];
        $defaultRowValues = [
            '',
            '',
            0
        ];
        $colIndex = count($defaultRowValues);

        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $flag = true;
                foreach ($ele['#options'] as $optKey => $opt) {
                    if ($flag) {
                        $flag = false;
                        $header[0][$colIndex] = $ele['#title'];
                    } else {
                        $header[0][$colIndex] = '';
                    }
                    $columnIndexes["{$key}:{$optKey}"] = $colIndex;
                    $defaultRowValues[$colIndex] = 0;
                    $header[1][$colIndex++] = $opt;
                }
            }
        }
        
        $header[0][$colIndex] = t('Event Report');
        $header[1][$colIndex] = '';
        $columnIndexes['event_report'] = $colIndex;
        $defaultRowValues[$colIndex++] = '';

        $header[0][$colIndex] = t('Event Evaluation');
        $header[1][$colIndex] = '';
        $columnIndexes['event_evaluation'] = $colIndex;
        $defaultRowValues[$colIndex] = '';
        

        return [
            'header' => $header,
            'indexes' => $columnIndexes,
            'default' => $defaultRowValues
        ];
    }

    protected function getWebformElements(string $webformId)
    {
        $webform = Webform::load($webformId);
        if (!$webform) {
            throw new Exception("'{$webformId}' is invalid.");
        }
        return $webform->getElementsOriginalDecoded();
    }
}
