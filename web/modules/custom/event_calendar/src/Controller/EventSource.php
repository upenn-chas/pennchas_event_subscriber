<?php

namespace Drupal\event_calendar\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventSource extends ControllerBase
{

    public function build($group, Request $request)
    {
        $isAjax = (bool) $request->query->get('ajax');
        if (!$isAjax) {
            throw new AccessDeniedHttpException();
        }

        $startDate = date('Y-m-d 00:00:00', strtotime($request->query->get('start')));
        $endDate = date('Y-m-d 23:59:59', strtotime($request->query->get('end')));

        $view = Views::getView('event_calendar');

        $display = 'page_1';
        $view->initDisplay();
        $view->setArguments([
            $group
        ]);
        $view->setDisplay($display);
        $view->setExposedInput([
            'field_event_schedule_value' => $startDate,
            'field_event_schedule_end_value' => $endDate
        ]);


        $view->preExecute();
        $view->execute($display);
        $content = $view->render($display);
        \Drupal::service('renderer')->renderRoot($content);
        $calendarOptions = json_decode($content['#attached']['drupalSettings']['fullCalendarView'][0]['calendar_options'], true);
  
        return new JsonResponse($calendarOptions['events']);
    }
}
