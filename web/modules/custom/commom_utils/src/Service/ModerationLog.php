<?php

namespace Drupal\common_utils\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\RendererInterface;

class ModerationLog
{
    /**
     * Database connection.
     * 
     * @var \Drupal\Core\Database\Connection
     */
    protected Connection $connection;
    
    /**
     * The date formatter.
     * 
     * @var \Drupal\Core\Datetime\DateFormatter
     */
    protected DateFormatter $formatter;

    /**
     * The renderer.
     *
     * @var \Drupal\Core\Render\RendererInterface
     */
    protected RendererInterface $renderer;

    private const TABLE_ATTRIBUTES = [
        'class' => ['table', 'cols-9']
    ];


    /**
     * Constructs a moderation log service.
     *
     * @param \Drupal\Core\Database\Connection $connection
     *   The database connection.
     * @param \Drupal\Core\Datetime\DateFormatter $formatter
     *   The date formatter.
     * @param \Drupal\Core\Render\RendererInterface $renderer
     *   The renderer.
     */

    public function __construct(Connection $connection, DateFormatter $formatter, RendererInterface $renderer)
    {
        $this->connection = $connection;
        $this->formatter = $formatter;
        $this->renderer = $renderer;
    }

    /**
     * Builds and returns a rendered table for the moderation log .
     */
    public function buildModerationLogTable(int $nid)
    {
        $header = ['Sr. No.', 'State', 'Message', 'Moderated At', 'Moderator'];
        $logs = $this->getNodeModerationData($nid);

        $rows = [];
        foreach ($logs as $index => $log) {
            $rows[] = [
                $index + 1,
                $log['moderation_state'],
                $log['revision_log'],
                $this->formatter->format($log['revision_timestamp']),
                $log['name'],
            ];
        }

        $table = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
            '#attributes' => self::TABLE_ATTRIBUTES
        ];

        return $rows ? $this->renderer->render($table) : '';
    }

    /**
     * Fetches moderation data for the given node ID.
     */
    public function getNodeModerationData(int $nid)
    {
        $query = $this->connection->select('content_moderation_state_field_revision', 'cmsfr');
        $query->fields('cmsfr', ['moderation_state']);
        $query->fields('nr', ['revision_log', 'revision_timestamp']);
        $query->fields('ufd', ['name']);
        $query->innerJoin('node_revision', 'nr', 'cmsfr.content_entity_revision_id = nr.vid');
        $query->innerJoin('users_field_data', 'ufd', 'nr.revision_uid = ufd.uid');
        $query->condition('cmsfr.content_entity_id', $nid, '=');
        $query->condition('cmsfr.moderation_state', 'draft', '!=');
        $query->orderBy('cmsfr.revision_id');

        return $query->distinct()->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
}
