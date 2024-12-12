<?php

namespace Drupal\pennchas_form_alter\Util;


final class Constant
{
    const NODE_RESERVE_ROOM = 'reserve_room';
    const NODE_ROOM = 'room';

    const MOD_STATUS_PUBLISHED = 'published';
    const MOD_STATUS_DRAFT = 'draft';
    const MOD_STATUS_DELETE = 'delete';

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



}
