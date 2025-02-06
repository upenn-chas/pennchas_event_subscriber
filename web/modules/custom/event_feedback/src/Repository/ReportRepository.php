<?php

namespace Drupal\event_feedback\Repository;

use Drupal\Core\Database\Connection;

/**
 * Common repository to get feedback data for reports
 */
class ReportRepository
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getSubmissions(string $webformId, array $filters, $page = 0, $length = 10)
    {
        $eventQuery = $this->connection->select('webform_submission', 'ws');
        $eventQuery->innerJoin('node_field_data', 'n', 'ws.entity_id = n.nid');
        $eventQuery->leftJoin('group_relationship_field_data', 'grfd', 'grfd.entity_id=n.nid');
        $eventQuery->leftJoin('groups_field_data', 'gfd', 'gfd.id=grfd.gid');
        $eventQuery->leftJoin('node__field_intended_audience', 'nfia', 'nfia.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_intended_outcomes', 'nfio', 'nfio.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_event_priority', 'nfep', 'nfep.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_participants', 'nfp', 'nfp.entity_id = n.nid');
        $eventQuery->leftJoin('node__field_actual_program_attendance', 'nfapa', 'nfapa.entity_id = n.nid');
        $eventQuery->fields('n', ['title', 'nid']);
        $eventQuery->addField('nfapa', 'field_actual_program_attendance_value', 'evaluated');
        $eventQuery->addExpression('GROUP_CONCAT(DISTINCT gfd.label)', 'houses');
        $eventQuery->addExpression('COUNT(DISTINCT ws.sid)', 'respondant');
        $eventQuery->condition('ws.webform_id', $webformId, '=');
        $eventQuery->groupBy('ws.entity_id');
        $eventQuery->orderBy('ws.created', 'DESC');


        if (isset($filters['gid']) && $filters['gid'] !== '_all') {
            $eventQuery->condition('grfd.gid', $filters['gid']);
        }

        if (isset($filters['type']) && $filters['type'] !== '_all') {
            $eventQuery->condition('nfia.field_intended_audience_value', $filters['type']);
        }

        if (isset($filters['participants']) && $filters['participants'] !== '_all') {
            $eventQuery->condition('nfp.field_participants_value', $filters['participants']);
        }

        if (isset($filters['outcome']) && $filters['outcome'] !== '_all') {
            $eventQuery->condition('nfio.field_intended_outcomes_value', $filters['outcome']);
        }

        if (isset($filters['goal_area']) && $filters['goal_area'] !== '_all') {
            $eventQuery->condition('nfep.field_event_priority_target_id', $filters['goal_area']);
        }

        if (isset($filters['submit_from']) && $filters['submit_from']) {
            $startFrom = strtotime($filters['submit_from'] . ' 00:00:00');
            $eventQuery->condition('ws.created', $startFrom, '>=');
        }

        if (isset($filters['submit_to']) && $filters['submit_to']) {
            $startTo = strtotime($filters['submit_to'] . ' 23:59:59');
            $eventQuery->condition('ws.created', $startTo, '<=');
        }
        $eventsCountQuery = $eventQuery->countQuery();
        $eventsCount = $eventsCountQuery->execute()->fetchCol();

        if (count($eventsCount) > 0 && !$eventsCount[0]) {
            return [
                'events' => [],
                'submissions' => [],
                'total' => 0
            ];
        }
        $eventsCount = $eventsCount[0];

        if ($page > -1) {
            $eventQuery->range($length * $page, $length);
        }

        $events = $eventQuery->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $eventsIds = array_column($events, 'nid');

        $query = $this->connection->select('webform_submission_data', 'wsd');
        $query->innerJoin('webform_submission', 'ws', 'wsd.sid = ws.sid');
        $query->fields('ws', ['entity_id']);
        $query->fields('wsd', ['name', 'value']);
        $query->addExpression('COUNT(DISTINCT wsd.sid)', 'count');
        $query->condition('ws.webform_id', $webformId, '=');
        $query->condition('ws.entity_id', $eventsIds, 'IN');
        $query->groupBy('ws.entity_id');
        $query->groupBy('wsd.name');
        $query->groupBy('wsd.value');

        $submissionData = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'events' => $events,
            'submissions' => $submissionData,
            'total' => $eventsCount
        ];
    }

    public function getTotalEventSubmissions(int $eventId, string $webformId)
    {
        $countQuery = $this->connection->select('webform_submission', 'ws');
        $countQuery->condition('ws.webform_id', $webformId);
        $countQuery->condition('ws.entity_id', $eventId);
        $countQuery = $countQuery->countQuery();
        $count = $countQuery->execute()->fetchCol();

        return (int) $count[0];
    }

    public function getEventSubmissionSummary(int $eventId, string $webformId)
    {
        $query = $this->connection->select('webform_submission', 'ws');
        $query->innerJoin('webform_submission_data', 'wsd', 'wsd.sid = ws.sid');
        $query->condition('ws.webform_id', $webformId);
        $query->condition('ws.entity_id', $eventId);
        $query->fields('wsd', ['name', 'value',]);
        $query->addExpression('GROUP_CONCAT(wsd.sid)', 'sids');
        $query->addExpression('count(wsd.value)', 'value_count');
        $query->groupBy('wsd.name');
        $query->groupBy('wsd.value');
        $query->orderBy('ws.created', 'DESC');

        $submissionData = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $submissionData,
        ];
    }

    public function getEventSubmissions(int $eventId, string $webformId)
    {
        $query = $this->connection->select('webform_submission', 'ws');
        $query->innerJoin('webform_submission_data', 'wsd', 'wsd.sid = ws.sid');
        $query->condition('ws.webform_id', $webformId);
        $query->condition('ws.entity_id', $eventId);
        $query->fields('wsd', ['name', 'value', 'sid']);
        $query->orderBy('ws.created', 'DESC');

        $submissionData = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'data' => $submissionData,
        ];
    }
}
