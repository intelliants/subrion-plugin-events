<?php
if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $events = [];
    $iaEvent = $iaCore->factoryPlugin('events');
    if ($iaView->blockExists('future_events')) {
        $limit = $iaCore->get('events_number_future', 10);
        $entries = $iaEvent->getFuture($limit);

        $events['future'] = $entries;
    }

    if ($iaView->blockExists('past_events')) {
        $limit = $iaCore->get('events_number_past', 5);
        $entries = $iaEvent->getPast($limit);

        $events['past'] = $entries;
    }

    if ($iaView->blockExists('event_categories')) {
        $events['categories'] = $iaEvent->getCategories();
    }

    $iaView->assign('events', $events);

    $iaView->iaSmarty->ia_add_media(['files' => 'moment, datepicker'], $iaView->iaSmarty);
}