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
     * @var string $headerType
     * @var array $webformElements
     */
    public function buildHeader(string $headerType, array $webformElements)
    {
        return match ($headerType) {
            'report' => $this->reportHeader($webformElements),
            'csv' => $this->csvHeader($webformElements),
            'csvEvent' => $this->csvEvent($webformElements),
            'webform' => $this->webformHeader($webformElements),
            'csvWebform' => $this->webformCSVHeader($webformElements),
            default => throw new InvalidArgumentException("'{$headerType}' is invlaid header type.")
        };
    }

    /**
     * Build a header for the participant survey report
     * 
     * @var array $webformElements
     */
    protected function reportHeader(array $webformElements)
    {
        $header = [
            [
                [
                    'title' => t('Event Report'),
                    'cspan' => 0,
                    'class' => 'event-feedback--th-1'
                ],
                [
                    'title' => t('House(s)'),
                    'cspan' => 0,
                    'class' => 'event-feedback--th-1'
                ],
                [
                    'title' => t('Event'),
                    'cspan' => 0,
                    'class' => 'event-feedback--th-1'
                ],
                [
                    'title' => t('Respondants'),
                    'cspan' => 0,
                    'class' => 'event-feedback--th-1'
                ],
            ],
            [
                [
                    'title' => '',
                    'cspan' => 0,
                    'class' => 'event-feedback--th-2'
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                    'class' => 'event-feedback--th-2'
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                    'class' => 'event-feedback--th-2'
                ],
                [
                    'title' => '',
                    'cspan' => 0,
                    'class' => 'event-feedback--th-2'
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
                        'cspan' => 0,
                        'class' => 'event-feedback--th-2 opt'
                    ];
                }
            }
        }

        $header[0][$colIndex] = [
            'title' => t('Event Evaluation'),
            'cspan' => 0,
            'class' => 'event-feedback--th-1'
        ];
        $header[1][$colIndex] = [
            'title' => '',
            'cspan' => 0,
            'class' => 'event-feedback--th-2'
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
     * @var array $webformElements
     */
    protected function csvHeader(array $webformElements)
    {
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
     * @var array $webformElements
     */
    protected function csvEvent(array $webformElements)
    {
        $header = [];
        $columnIndexes = [];
        $defaultRowValues = [
            0,
            '',
            []
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
     * Build a header for the webform report
     * 
     * @var array $webformElements
     */
    protected function webformHeader(array $webformElements)
    {
        $header = [
            [
                'title' => 'Sr. No.',
                'cspan' => 0,
                'class' => 'event-feedback--th-2 opt'
            ],
            [
                'title' => 'Submission ID',
                'cspan' => 0,
                'class' => 'event-feedback--th-2 opt'
            ],
            [
                'title' => 'Created',
                'cspan' => 0,
                'class' => 'event-feedback--th-2 opt'
            ],
            [
                'title' => 'User',
                'cspan' => 0,
                'class' => 'event-feedback--th-2 opt'
            ],
            [
                'title' => 'IP Address',
                'cspan' => 0,
                'class' => 'event-feedback--th-2 opt'
            ],
        ];
        $columnIndexes = [
            'serial' => 0,
            'sid' => 1,
            'created' => 2,
            'user' => 3,
            'remote_addr' => 4
        ];

         $defaultRowValues = [
            '','','','',''
        ];

        $colIndex = 5;
        unset($webformElements['actions']);
        foreach ($webformElements as $key => $ele) {
            if (!isset($ele['#title'])) {
                continue;
            }
            if ($ele['#type'] === 'webform_wizard_page') {
                foreach ($ele as $key2 => $ele2) {
                    if (is_array($ele2)) {
                        foreach ($ele2 as $key3 => $ele3) {
                            if (!isset($ele3['#title'])) {
                                continue;
                            }
                            $header[$colIndex] = [
                                'title' => $ele3['#title'],
                                'cspan' => 0,
                                'class' => 'event-feedback--th-2 opt'
                            ];
                            $defaultRowValues[$colIndex] = [];
                            $columnIndexes[$key3] = $colIndex++;
                        }
                    }
                }
            } else {
                $header[$colIndex] = [
                    'title' => $ele['#title'],
                    'cspan' => 0,
                    'class' => 'event-feedback--th-2 opt'
                ];

                $defaultRowValues[$colIndex] = [];
                $columnIndexes[$key] = $colIndex++;
            }
        }

        return [
            'header' => $header,
            'indexes' => $columnIndexes,
            'default' => $defaultRowValues
        ];
    }

    /**
     * Build a header for the webform report CSV
     * 
     * @var array $webformElements
     */
    protected function webformCSVHeader(array $webformElements)
    {
        $header = [
            t('Sr. No.'),
            t('Submission ID'),
            t('Created'),
            t('User'),
            t('IP Address')
        ];
        $columnIndexes = [
            'serial' => 0,
            'sid' => 1,
            'created' => 2,
            'user' => 3,
            'remote_addr' => 4
        ];

        $colIndex = 5;
        unset($webformElements['actions']);
        foreach ($webformElements as $key => $ele) {
            if (!isset($ele['#title'])) {
                continue;
            }
            if ($ele['#type'] === 'webform_wizard_page') {
                foreach ($ele as $key2 => $ele2) {
                    if (is_array($ele2)) {
                        foreach ($ele2 as $key3 => $ele3) {
                            if (!isset($ele3['#title'])) {
                                continue;
                            }
                            $header[$colIndex] = $ele3['#title'];
                            $columnIndexes[$key3] = $colIndex++;
                        }
                    }
                }
            } else {
                $header[$colIndex] = $ele['#title'];
                $columnIndexes[$key] = $colIndex++;
            }
        }

        return [
            'header' => $header,
            'indexes' => $columnIndexes
        ];
    }
}
