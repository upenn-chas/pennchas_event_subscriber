<?php

namespace Drupal\pennchas_form_alter\Cron;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\Entity\Node;
use Drupal\pennchas_form_alter\Util\Constant;

class UpdateEndsOnDate
{
    protected $logger;

    public function __construct(LoggerChannelFactoryInterface $logger)
    {
        $this->logger = $logger->get(get_class());
    }

    public function updateEndsOnDate()
    {

        $query = \Drupal::entityQuery('node')
            ->accessCheck(false)
            ->condition('type', Constant::NODE_RESERVE_ROOM)
            ->condition('field_event_ends_on', NULL, 'IS NULL');

        $nodeIds = $query->execute();

        if ($nodeIds) {
            $nodes = Node::loadMultiple($nodeIds);
            foreach ($nodes as $node) {
                try {
                    $endsOn = $this->getEventEndsOn($node);
                    $node->set('field_event_ends_on', $endsOn);
                    $node->setNewRevision(FALSE);
                    $node->save();
                    $this->logger->info('Update ends on date: ' . $node->getTitle());
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), $e->getTrace());
                }
            }
        } else {
            $this->logger->debug('No node found.');
        }
    }

    protected function getEventEndsOn(Node $node)
    {
        $eventSchedules = $node->get('field_event_schedule')->getValue();
        $count = count($eventSchedules);
        $eventLastDay = $eventSchedules[$count - 1];
        return $eventLastDay['end_value'];
    }
}
