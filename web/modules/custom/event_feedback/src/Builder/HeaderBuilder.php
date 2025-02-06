<?php

namespace Drupal\event_feedback\Builder;

use Drupal\webform\Entity\Webform;
use Exception;
use InvalidArgumentException;

/**
 * Build the header for different occasions.
 */
class HeaderBuilder
{
    /**
     * Facade function. Return the header
     * 
     * @var string $webformId
     * @var string $headerType
     */
    public function buildHeader(string $webformId, string $headerType)
    {
        return match ($headerType) {
            'report' => $this->reportHeader($webformId),
            'csv' => $this->csvHeader($webformId),
            'csvEvent' => $this->csvEvent($webformId),
            default => throw new InvalidArgumentException("'{$headerType}' is invlaid header type.")
        };
    }

    /**
     * Build a header for the participant survey report
     * 
     * @var string $webformId
     */
    protected function reportHeader(string $webformId)
    {
        $webformElements = $this->getWebformElements($webformId);
        $header = [
            [
                [
                    'title' => t('Event Report'),
                    'cspan' => 0,
                ],
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
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                ]
            ]
        ];
        $columnIndexes = [
            'event_report' => 0,
            'houses' => 1,
            'event' => 2,
            'repondants' => 3
        ];
        $defaultRowValues = [
            '',
            '',
            '',
            0
        ];
        $colIndex = count($defaultRowValues);

        foreach ($webformElements as $key => $ele) {
            if ($ele['#type'] === 'radios' || $ele['#type'] === 'checkboxes') {
                $header[0][] = [
                    'title' => $key === 'event_intended_to' ? 'Achieved intended outcomes?' : $ele['#title'],
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

    /**
     * Build a header for the participant survey report export data
     * 
     * @var string $webformId
     */
    protected function csvHeader(string $webformId)
    {
        $webformElements = $this->getWebformElements($webformId);
        $header = [
            [
                t('Event Report'),
                t('House(s)'),
                t('Event'),
                t('Respondants')
            ],
            [
                '',
                '',
                '',
                ''
            ]
        ];
        $columnIndexes = [
            'event_report' => 0,
            'houses' => 1,
            'event' => 2,
            'repondants' => 3
        ];
        $defaultRowValues = [
            '',
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
                        $header[0][$colIndex] = $key === 'event_intended_to' ? 'Achieved intended outcomes?' : $ele['#title'];
                    } else {
                        $header[0][$colIndex] = '';
                    }
                    $columnIndexes["{$key}:{$optKey}"] = $colIndex;
                    $defaultRowValues[$colIndex] = 0;
                    $header[1][$colIndex++] = $opt;
                }
            }
        }

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

    /**
     * Build a header for the per event survey report export data
     * 
     * @var string $webformId
     */

    protected function csvEvent(string $webformId)
    {
        $webformElements = $this->getWebformElements($webformId);
        $header = [];
        $columnIndexes = [];
        $defaultRowValues = [
            0,
            '',
            ''
        ];

        $colIndex = 0;
        unset($webformElements['actions']);
        foreach ($webformElements as $key => $ele) {
            $header[$colIndex] = $ele['#title'];
            $columnIndexes[$key] = $colIndex++;
        }

        return [
            'header' => $header,
            'indexes' => $columnIndexes,
            'default' => $defaultRowValues
        ];
    }

    /**
     * Get the webform elements
     * 
     * @var string $webformId
     */

    protected function getWebformElements(string $webformId)
    {
        $webform = Webform::load($webformId);
        if (!$webform) {
            throw new Exception("'{$webformId}' is invalid.");
        }
        return $webform->getElementsOriginalDecoded();
    }
}
