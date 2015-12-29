<?php
//##copyright##

$iaDb->setTable('events');

$iaUtil = iaCore::util();
$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN, 'common', 'event');

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (isset($_GET['action']) && $_GET['action'] == 'get_by_date')
	{
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

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	$iaView->title(iaLanguage::get('page_title_events'));

	$limit = $iaCore->get('events_number_default', 10);
	$page = max(1, isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 0);
	$start = ($page - 1) * $limit;

	$paginator = array(
		'limit' => $limit,
		'template' => IA_URL . 'events/?page={page}',
		'total' => 0
	);

	$pageActions[] = array(
		'icon' => 'plus-circle',
		'title' => iaLanguage::get('add_new_event'),
		'url' => IA_URL . 'events/add/'
	);

	$pageActions[] = array(
		'icon' => 'rss',
		'title' => '',
		'url' => IA_URL . 'events/rss/',
		'classes' => 'btn-warning'
	);

	if ('delete' == $pageAction)
	{
		if (iaUsers::hasIdentity())
		{
			$eventId = intval($iaCore->requestPath[0]);
			if (empty($eventId))
			{
				return $iaView->errorPage(iaLanguage::get('invalid_parameters'));
			}
			else
			{
				$stmt = iaDb::printf("`id` = ':id' AND `member_id` = ':owner'", array('id' => $eventId, 'owner' => iaUsers::getIdentity()->id));
				$iaDb->delete($stmt);

				$iaUtil->redirect(iaLanguage::get('thanks'), iaLanguage::get('event_deleted'), IA_URL . 'profile/events/');
			}
		}
		else
		{
			return iaView::accessDenied();
		}
	}

	if (isset($iaCore->requestPath[0]))
	{
		switch (true)
		{
			case ('date' == $iaCore->requestPath[0]):
				$offset = array('year' => 1, 'month' => 2, 'day' => 3);
				$date = array(
					$offset['year'] => intval($iaCore->requestPath[1]),
					$offset['month'] => intval($iaCore->requestPath[2]),
					$offset['day'] => intval($iaCore->requestPath[3])
				);
				if (!checkdate($date[$offset['month']], $date[$offset['day']], $date[$offset['year']]))
				{
					return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('invalid_date_specified'));
				}

				$stmt = sprintf('%d-%02d-%02d', $date[$offset['year']], $date[$offset['month']], $date[$offset['day']]);
				$events = $iaEvent->getByDate($stmt, 1000);

				$title = sprintf('%02d %s %d', $date[$offset['day']], iaLanguage::get('month' . $date[$offset['month']]), $date[$offset['year']]);

				iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');
				iaBreadcrumb::replaceEnd($title, IA_SELF);

				$title = iaDb::printf(iaLanguage::get('events_on_date'), array('date' => $title));
				$iaView->title($title);

				break;

			case ($category = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($iaCore->requestPath[0], 'slug'), $iaEvent->getCategoriesTable())):
				iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');
				iaBreadcrumb::replaceEnd($category['title'], IA_SELF);

				$iaView->set('events_category_id', $category['id']);
				$iaView->title($category['title']);

				$events = $iaEvent->get(array('category_id' => $category['id']), $start, $limit);

				break;

			default:
				return iaView::errorPage(iaView::ERROR_NOT_FOUND);
		}
	}
	elseif ('event_my' == $iaView->name())
	{
		if (!iaUsers::hasIdentity())
		{
			return iaView::accessDenied();
		}

		$events = $iaEvent->get(array('member_id' => iaUsers::getIdentity()->id), $start, $limit, false, true, true);
	}
	elseif ('event_search' == $iaView->name())
	{
		if (!isset($_GET['term']))
		{
			return iaView::errorPage(iaLanguage::get('no_search_term_provided'));
		}

		$term = strip_tags($_GET['term']);

		if (empty($term))
		{
			return iaView::errorPage(iaLanguage::get('no_search_term_provided'));
		}

		$stmt = iaDb::printf("CONCAT(t1.`title`, t1.`description`, t1.`venue`) LIKE '%:term%'", array('term' => iaSanitize::sql($term)));
		$events = $iaEvent->get(array(), $start, $limit, $stmt);

		iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

		$paginator['template'] = IA_URL . 'events/search/?term=' . $term . '&page={page}';

		$iaView->assign('term', $term);
	}
	else
	{
		$events = $iaEvent->get(array(), $start, $limit, false, false);
	}

	$paginator['total'] = $iaDb->foundRows();

	$iaView->assign('items', $events);
	$iaView->assign('paginator', $paginator);

	$iaView->set('actions', $pageActions);

	$iaView->display('index');
}

$iaDb->resetTable();