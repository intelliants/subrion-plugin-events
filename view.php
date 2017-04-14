<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2016 Intelliants, LLC <http://www.intelliants.com>
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
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

if ($iaView->getRequestType() == iaView::REQUEST_HTML) {
    $eventId = explode('-', $iaCore->requestPath[0]);
    $eventId = end($eventId);

    if (empty($eventId) || !is_numeric($eventId)) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
    }

    $iaEvent = $iaCore->factoryPlugin(IA_CURRENT_MODULE);

    $item = $iaEvent->get(['id' => $eventId], 0, 1, false, true, true);

    if (empty($item)) {
        return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
    }

    $item = array_shift($item);

    iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

    $eventOwner = empty($item['member_id']) ? false : $iaCore->factory('users')->getInfo($item['member_id']);
    $iaView->assign('eventOwner', $eventOwner);

    if (iaUsers::hasIdentity() && $item['member_id'] == iaUsers::getIdentity()->id) {
        $pageActions[] = [
            'icon' => 'icon-edit',
            'title' => iaLanguage::get('edit'),
            'url' => IA_URL . 'events/edit/' . $item['id'] . '/',
            'classes' => 'btn-info'
        ];

        $pageActions[] = [
            'icon' => 'icon-remove',
            'title' => iaLanguage::get('delete'),
            'url' => IA_URL . 'events/delete/' . $item['id'] . '/',
            'classes' => 'btn-danger js-delete-event'
        ];

        $iaView->set('actions', $pageActions);
    }

    $openGraph = [
        'title' => $item['title'],
        'url' => IA_SELF,
        'description' => substr(iaSanitize::html(iaSanitize::tags($item['description'])), 0, 200) . '...'
    ];
    if ($image = $item['image']) {

        $openGraph['image'] = IA_CLEAR_URL . 'uploads/' . $image['path'] . 'thumbnail/' .  $image['file'];
    }

    $iaView->set('og', $openGraph);

    $iaView->title($item['title']);
    $iaView->assign('item', $item);

    $iaView->display('view');
}