<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <https://intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link https://subrion.org/
 *
 ******************************************************************************/

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $events = [];
    $iaEvent = $iaCore->factoryModule('event', 'events');
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
