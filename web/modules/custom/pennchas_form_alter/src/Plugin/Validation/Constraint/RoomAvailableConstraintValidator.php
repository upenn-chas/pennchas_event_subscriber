<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\smart_date_recur\Entity\SmartDateRule;
use Drupal\user\Entity\Role;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that the submitted reservation is available.
 *
 */
class RoomAvailableConstraintValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint) {
        $roomId = (int) $this->context->getRoot()->get('field_room')->getString();
        $schedule = $value->getValue();
        if(!$this->isBookingAvailable($roomId, $schedule)) {
            $this->context->addViolation($constraint->noSlotAvailble);
        }

    }

    public function isBookingAvailable($roomId, $schedule)
    {
        $schedule = $this->generateOccurence($schedule, 'node', 'reserve_room', 'field_name', 12);

        $query = \Drupal::database()->select('node__field_event_schedule', 'fs');
        $query->fields('fs', ['field_event_schedule_value', 'field_event_schedule_end_value', 'entity_id']);
        $query->fields('ms', ['moderation_state']);
        $query->fields('fr', ['field_room_target_id']);
        $query->innerJoin('node__field_room', 'fr', 'fs.entity_id = fr.entity_id');
        $query->leftJoin('content_moderation_state_field_data', 'ms', 'fr.entity_id = ms.content_entity_id');
        $query->condition('fr.bundle', 'reserve_room');
        $query->condition('fr.field_room_target_id', $roomId);
        $query->condition('ms.moderation_state', ['published', 'pending'], 'IN');
        $conditionsGroup = $query->orConditionGroup();

        foreach ($schedule as $item) {
            $conditionsGroup->condition(
                $query->orConditionGroup()
                    ->condition(
                        $query->andConditionGroup()
                            ->condition('fs.field_event_schedule_value', $item['value'], '<=')
                            ->condition('fs.field_event_schedule_end_value', $item['value'], '>')
                    )
                    ->condition(
                        $query->andConditionGroup()
                            ->condition('fs.field_event_schedule_value', $item['end_value'], '<')
                            ->condition('fs.field_event_schedule_end_value', $item['end_value'], '>')
                    )
            );
        }
        $query->condition($conditionsGroup);
        $result = $query->execute()->fetchAll();

        return (bool) count($result);
    }

    private function generateOccurence(&$values, $entity_type, $bundle, $field_name, $month_limit)
    {
        $for_cloning = [];
        foreach ($values as $index => &$item) {
            // Keep track of the original position for sorting later.
            $item['_original_delta'] = $index;
            if (empty($item['value']) || empty($item['repeat'])) {

                $item['start'] = date('Y-m-d H:i', $item['value']);
                $item['end'] = date('Y-m-d H:i', $item['end_value']);
                continue;
            }

            // Format provided values to be rrule-compatible.
            $rrule_values = [
                'freq'        => $item['repeat'],
                'start'       => $item['value'],
                'end'         => $item['end_value'],
                'entity_type' => $entity_type,
                'bundle'      => $bundle,
                'field_name'  => $field_name,
                'parameters'  => '',
            ];
            $limit = '';
            if ($item['repeat-end'] == 'COUNT') {
                $limit = $item['repeat-end-count'];
            } elseif ($item['repeat-end'] == 'UNTIL') {
                $limit = $item['repeat-end-date'];
            }
            if ($item['repeat-end'] && $limit) {
                $limit_safe = new FormattableMarkup(':type=:limit', [
                    ':type' => $item['repeat-end'],
                    ':limit' => $limit,
                ]);
                $rrule_values['limit'] = $limit_safe->__toString();
                $rrule_values['unlimited'] = FALSE;
                $before = NULL;
            } else {
                $rrule_values['limit'] = '';
                $rrule_values['unlimited'] = TRUE;
                $before = strtotime('+' . (int) $month_limit . ' months');
            }
            if (!empty($item['interval']) || is_array($item['repeat-advanced'])) {
                $params = [];
                if (!empty($item['interval']) && $item['interval'] > 1) {
                    $interval_safe = new FormattableMarkup('INTERVAL=:interval', [':interval' => $item['interval']]);
                    $params['interval'] = $interval_safe->__toString();
                }
                // Only parse appropriate advanced options based on selected frequency.
                switch ($rrule_values['freq']) {
                    case 'MINUTELY':
                        // Use the array of day checkboxes if one of them is checked.
                        if (!empty($item['repeat-advanced']['restrict-minutes']['byminute']) && is_array($item['repeat-advanced']['restrict-minutes']['byminute'])) {
                            $selected = [];
                            foreach ($item['repeat-advanced']['restrict-minutes']['byminute'] as $value) {
                                if ($value) {
                                    $selected[] = $value;
                                }
                            }
                            if ($selected) {
                                $by_minute_safe = new FormattableMarkup('BYMINUTE=:byminute', [
                                    ':byminute' => implode(',', $selected),
                                ]);
                                $params['by_minute'] = $by_minute_safe->__toString();
                            }
                        }

                    case 'HOURLY':
                        // Use the array of day checkboxes if one of them is checked.
                        if (!empty($item['repeat-advanced']['restrict-hours']['byhour']) && is_array($item['repeat-advanced']['restrict-hours']['byhour'])) {
                            $selected = [];
                            foreach ($item['repeat-advanced']['restrict-hours']['byhour'] as $value) {
                                if ($value) {
                                    $selected[] = $value;
                                }
                            }
                            if ($selected) {
                                $by_hour_safe = new FormattableMarkup('BYHOUR=:byhour', [
                                    ':byhour' => implode(',', $selected),
                                ]);
                                $params['by_hour'] = $by_hour_safe->__toString();
                            }
                        }

                    case 'DAILY':
                    case 'WEEKLY':
                        // Use the array of day checkboxes if one of them is checked.
                        if (!empty($item['repeat-advanced']['byday']) && is_array($item['repeat-advanced']['byday']) && array_sum(array_map('is_string', $item['repeat-advanced']['byday']))) {
                            // Remove any zero values.
                            $selected = [];
                            foreach ($item['repeat-advanced']['byday'] as $value) {
                                if ($value) {
                                    $selected[] = $value;
                                }
                            }
                            $by_day_safe = new FormattableMarkup('BYDAY=:byday', [
                                ':byday' => implode(',', $selected),
                            ]);
                            $params['by_day'] = $by_day_safe->__toString();
                        }
                        break;

                    case 'MONTHLY':
                    case 'YEARLY':
                        if (!empty($item['repeat-advanced']['which'])) {
                            if (empty($item['repeat-advanced']['weekday'])) {
                                $by_day_safe = new FormattableMarkup('BYMONTHDAY=:which', [
                                    ':which' => $item['repeat-advanced']['which'],
                                ]);
                                $params['by_day'] = $by_day_safe->__toString();
                            } else {
                                // Weekday(s) specified so make the condition appropriately.
                                if (strpos($item['repeat-advanced']['weekday'], ',')) {
                                    // A comma means a special format for multiple days allowed.
                                    $pattern = 'BYDAY=:day;BYSETPOS=:which';
                                } else {
                                    $pattern = 'BYDAY=:which:day';
                                }
                                $by_day_safe = new FormattableMarkup($pattern, [
                                    ':which' => $item['repeat-advanced']['which'],
                                    ':day' => $item['repeat-advanced']['weekday'],
                                ]);
                                $params['by_day'] = $by_day_safe->__toString();
                            }
                        }
                        if ($rrule_values['freq'] == 'YEARLY') {
                            $by_month_safe = new FormattableMarkup('BYMONTH=:which', [
                                ':which' => \Drupal::service('date.formatter')->format($rrule_values['start'], 'custom', 'n'),
                            ]);
                            $params['by_month'] = $by_month_safe->__toString();
                        }
                        break;
                }
                $rrule_values['parameters'] = implode(';', $params);
            }


            // New rrule, so construct object.
            $rrule = SmartDateRule::create($rrule_values);

            // Ensure the rrule timezone matches the configured timezone on the field,
            // if set.
            if (isset($item['timezone'])) {
                $rrule->setTimezone($item['timezone']);
            }
            // Generate instances.
            $instances = $rrule->getRuleInstances($before);

            // Make additional field deltas for the generated instances.
            $for_cloning[$index] = $instances;
        }
        // Now process field values that should be cloned.
        foreach ($for_cloning as $index => $instances) {
            // Now process the generated instances.
            // Use the submitted values as a template.
            $new_item = $values[$index];
            // Replace the first instance, in case there's an override.
            unset($values[$index]);

            foreach ($instances as $rrule_index => $instance) {
                $new_item['value'] = $instance['value'];
                $new_item['end_value'] = $instance['end_value'];
                $new_item['duration'] = ($instance['end_value'] - $instance['value']) / 60;
                $new_item['rrule_index'] = $rrule_index;
                $values[] = $new_item;
            }
        }
        $values = smart_date_array_orderby($values, '_original_delta', SORT_ASC, 'value', SORT_ASC);

        return $values;
    }
}
