<?php

namespace Drupal\pennchas_form_alter\Util;


final class Constant
{
    const NODE_RESERVE_ROOM = 'reserve_room';
    const NODE_PROGRAM_COMMUNITY = 'program_community';
    const NODE_EVENT = 'chas_event';
    const NODE_ROOM = 'room';
    const NODE_NOTICES = 'notices';

    const MOD_STATUS_PUBLISHED = 'published';
    const MOD_STATUS_DRAFT = 'draft';
    const MOD_STATUS_DELETE = 'delete';

    /**
     * CHAS community event
     * 
     * @var string
     */
    const EVT_COMMUNITY_EVENT = 'community_event';

    /**
     * CHAS house event
     * 
     * @var string
     */
    const EVT_HOUSE_EVENT = 'house_event';

    /**
     * CHAS floor event
     * 
     * @var string
     */
    const EVT_FLOOR_EVENT = 'floor_event';

    /**
     * Moderator permission
     * 
     * @var string
     */
    const PERMISSION_MODERATION = 'use editorial transition publish';

    /**
     * Email template machine name for moderator alert for new incoming request
     * @var string
     */
    const RESERVER_ROOM_EMAIL_MODERATOR_ALERT = 'et_reserve_room_moderation';

    /**
     * Email template machine name for approved reserve room
     * @var string
     */
    const RESERVER_ROOM_EMAIL_APPROVED = 'et_room_reservation_approved';

    /**
     * Email template machine name for new reserve room request
     * @var string
     */
    const RESERVER_ROOM_EMAIL_CREATED = 'et_room_reservation_created';

    /**
     * Email template machine name for new reserve room created by moderator
     * @var string
     */
    const RESERVER_ROOM_EMAIL_MODERATOR_CREATED = 'et_room_resv_created_approved';

    /**
     * Email template machine name for reserve room moderation flow
     * @var string
     */
    const RESERVER_ROOM_EMAIL_MODERATION = 'et_room_reservation_state_change';

    /**
     * Email template machine name for moderator alert for new event
     * @var string
     */
    const EVENT_EMAIL_MODERATOR_ALERT = 'et_new_event_mod_notification';

    /**
     * Email template machine name for approved event
     * @var string
     */
    const EVENT_EMAIL_APPROVED = 'et_event_approved';

    /**
     * Email template machine name for new event request
     * @var string
     */
    const EVENT_EMAIL_CREATED = 'et_new_event_host_notification';

    /**
     * Email template machine name for new event created by moderator
     * @var string
     */
    const EVENT_EMAIL_MODERATOR_CREATED = 'et_new_event_md_hst_notification';

    /**
     * Email template machine name for event moderation flow
     * @var string
     */
    const EVENT_EMAIL_MODERATION = 'et_moderation_state_change_notif';

    /**
     * Email template machine name for new event request
     * @var string
     */
    const NOTICE_EMAIL_CREATED = 'et_notice_event_created';
}
