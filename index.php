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

$iaDb->setTable('events');
$iaUtil = iaCore::util();
$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_MODULE);
$baseUrl = IA_URL . 'events';
$iaUsers = $iaCore->factory('users');

if (iaView::REQUEST_JSON == $iaView->getRequestType()) {
    if (isset($_GET['action']) && $_GET['action'] == 'get_by_date') {
        $date = $_GET['date'];

        $data = $iaEvent->getByDate($date, 2);
        /*
                foreach($data as $key => $item)
                {
                    $data[$key]['date'] = date($iaEvent->getDateFormat(), strtotime($item['date']));
                    $data[$key]['date_end'] = date($iaEvent->getDateFormat(), strtotime($item['date_end']));
                }
        */
        $iaView->jsonp($data);
    }
}

if (iaView::REQUEST_HTML == $iaView->getRequestType()) {
    $limit = $iaCore->get('events_number_default', 10);
    $page = max(1, isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 0);
    $start = ($page - 1) * $limit;

    $paginator = [
        'limit' => $limit,
        'template' => IA_URL . 'events/?page={page}',
        'total' => 0
    ];

    $pageActions[] = [
        'icon' => 'plus-circle',
        'title' => iaLanguage::get('add_new_event'),
        'url' => IA_URL . 'events/add/'
    ];

    $pageActions[] = [
        'icon' => 'rss',
        'title' => '',
        'url' => IA_URL . 'events/events.xml',
        'classes' => 'btn-warning'
    ];

    if ('delete' == $pageAction) {
        if (iaUsers::hasIdentity()) {
            $eventId = intval($iaCore->requestPath[0]);
            if (empty($eventId)) {
                return $iaView->errorPage(iaLanguage::get('invalid_parameters'));
            } else {
                $stmt = iaDb::printf("`id` = ':id' AND `member_id` = ':owner'",
                    ['id' => $eventId, 'owner' => iaUsers::getIdentity()->id]);
                $iaDb->delete($stmt);

                $iaUtil->redirect(iaLanguage::get('thanks'), iaLanguage::get('event_deleted'),
                    IA_URL . 'profile/events/');
            }
        } else {
            return iaView::accessDenied();
        }
    }

    if (isset($iaCore->requestPath[0])) {
        switch (true) {
            case ('date' == $iaCore->requestPath[0]):

                $offset = ['year' => 1, 'month' => 2, 'day' => 3];
                $date = [
                    $offset['year'] => intval($iaCore->requestPath[1]),
                    $offset['month'] => intval($iaCore->requestPath[2]),
                    $offset['day'] => intval($iaCore->requestPath[3])
                ];
                if (!checkdate($date[$offset['month']], $date[$offset['day']], $date[$offset['year']])) {
                    return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('invalid_date_specified'));
                }
                $stmt = sprintf('%d-%02d-%02d', $date[$offset['year']], $date[$offset['month']], $date[$offset['day']]);
                $events = $iaEvent->getByDate($stmt, 1000);

                $title = sprintf('%02d %s %d', $date[$offset['day']],
                    iaLanguage::get('month' . $date[$offset['month']]), $date[$offset['year']]);

                iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');
                iaBreadcrumb::replaceEnd($title, IA_SELF);

                $iaView->title(iaLanguage::getf('events_on_date', ['date' => $title]));

                break;

            case ($category = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($iaCore->requestPath[0], 'slug'),

                $iaEvent->getCategoriesTable())):

                iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');
                iaBreadcrumb::replaceEnd($category['title_' . $iaCore->language['iso']], IA_SELF);

                $iaView->set('events_category_id', $category['id']);
                $iaView->title($category['title_' . $iaCore->language['iso']]);

                $events = $iaEvent->get(['category_id' => $category['id']], $start, $limit);

                break;

            default:

                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
        }
    } elseif ('event_my' == $iaView->name()) {
        if (!iaUsers::hasIdentity()) {
            return iaView::accessDenied();
        }

        $events = $iaEvent->get(['member_id' => iaUsers::getIdentity()->id], $start, $limit, false, true, true);
    } elseif ('event_search' == $iaView->name()) {
        if (!isset($_GET['term'])) {
            return iaView::errorPage(iaLanguage::get('no_search_term_provided'));
        }

        $term = strip_tags($_GET['term']);

        if (empty($term)) {
            return iaView::errorPage(iaLanguage::get('no_search_term_provided'));
        }

        $stmt = iaDb::printf("CONCAT_WS(e.`title_:lang`, e.`description_:lang`, e.`venue`) LIKE '%:term%'",
            ['term' => iaSanitize::sql($term), 'lang' => $iaCore->language['iso']]);
        $events = $iaEvent->get([], $start, $limit, $stmt);

        iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

        $paginator['template'] = IA_URL . 'events/search/?term=' . $term . '&page={page}';

        $iaView->assign('term', $term);
    } else {

        $events = $iaEvent->get([], $start, $limit, false, false);

    }

    $paginator['total'] = $iaEvent->foundRows;

    $iaView->assign('items', $events);
    $iaView->assign('paginator', $paginator);

    $iaView->set('actions', $pageActions);

    $iaView->display('index');
}
//Added code to display correct XML / RSS => rss.php can be left out now
//Also added event start & end date
if (iaView::REQUEST_XML == $iaView->getRequestType()) {

    $output = [
        'title' => $iaCore->get('site') . ' :: ' . $iaView->title(),
        'description' => ' ',
        'link' => $baseUrl,
        'item' => []
    ];

    //Add default Feed Image displayed in RSS Readers
    //You can add your own by replacing rss.png by other image using same name
    $output['image'][] = [
        'title' => $iaCore->get('site') . ' :: ' . $iaView->title(),
        'url' => IA_CLEAR_URL . 'plugins/events/templates/front/img/rss.png',
        'link' => $baseUrl
    ];

    $limit = $iaCore->get('events_number_rss', 10);
    $entries = $iaEvent->get([], 0, $limit);

    foreach ($entries as $entry) {

        //Create nice Event Multi-Language Description with Location, Start/End Date & Image included
        $desc = '';
        $desc .= iaLanguage::get('venue') . ': ' . $entry["venue"] . '&lt;br&gt; ';
        $desc .= iaLanguage::get("date_start") . ': ' . $entry["date"] . '&lt;br&gt;';
        $desc .= iaLanguage::get("date_end") . ': ' . $entry["date_end"] . '&lt;br&gt;';
        if ($entry['image'] != '') {
            //Let's add the event image as well, if used
            $desc .= '<p><img src="' . IA_CLEAR_URL . 'uploads/' . $entry["image"] . '"/></p>';
        }
        $desc .= iaSanitize::tags($entry["description"]);

        $output['item'][] = [
            'title' => iaSanitize::tags($entry['title']),
            'pubDate' => date('D, d M Y H:i:s O'),
            'guid' => $entry['url'],
            'description' => $desc
        ];
    }

    $iaView->assign('channel', $output);
}

$iaDb->resetTable();