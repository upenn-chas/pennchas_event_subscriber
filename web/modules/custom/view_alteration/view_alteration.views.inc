<?php

function view_alteration_views_data_alter(array &$data)
{
    if (isset($data['node'])) {
        $data['node']['moderate_content'] = [
            'title' => t('Link to moderate Content'),
            'help' => t('Custom field. Provide the link for the content moderation.<br><b>Note:</b> Supported content types: <i>chas_event</i> and <i>reserve_room</i> only.'),
            'field' => [
                'id' => 'moderate_content',
            ]
        ];

        $data['node']['evaluate_content'] = [
            'title' => t('Link to evalute Content'),
            'help' => t('Custom field. Provide the link for the content evalution.<br><b>Note:</b> Supported content types: <i>chas_event</i> only.'),
            'field' => [
                'id' => 'evaluate_content',
            ]
        ];
    }
}
