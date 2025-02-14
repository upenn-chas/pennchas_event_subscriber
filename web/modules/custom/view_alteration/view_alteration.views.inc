<?php

function view_alteration_views_data_alter(array &$data)
{
    if (isset($data['node'])) {
        $data['node']['moderate_content'] = [
            'title' => t('Link to moderate Content'),
            'help' => t('Provides a moderate link for content.'),
            'field' => [
                'id' => 'moderate_content',
            ]
        ];
    }
}
