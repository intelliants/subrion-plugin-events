<?php
//##copyright##

if ($iaView->getRequestType() == iaView::REQUEST_HTML)
{
	$eventId = explode('-', $iaCore->requestPath[0]);
	$eventId = end($eventId);

	if (empty($eventId) || !is_numeric($eventId))
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
	}

	$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN);

	$item = $iaEvent->get(array('id' => $eventId), 0, 1);

	if (empty($item))
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
	}

	$item = array_shift($item);

	iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

	$eventOwner = empty($item['member_id']) ? false : $iaCore->factory('users')->getInfo($item['member_id']);
	$iaView->assign('eventOwner', $eventOwner);

	if (iaUsers::hasIdentity() && $item['member_id'] == iaUsers::getIdentity()->id)
	{
		$pageActions[] = array('icon' => 'icon-edit',
			'title' => iaLanguage::get('edit'),
			'url' => IA_URL . 'events/edit/' . $item['id'] . '/',
			'classes' => 'btn-info'
		);

		$pageActions[] = array('icon' => 'icon-remove',
			'title' => iaLanguage::get('delete'),
			'url' => IA_URL . 'events/delete/' . $item['id'] . '/',
			'classes' => 'btn-danger'
		);

		$iaView->set('actions', $pageActions);
	}

	$openGraph = array(
		'title' => $item['title'],
		'url' => IA_SELF,
		'description' => substr(iaSanitize::html(iaSanitize::tags($item['description'])), 0, 200) . '...'
	);
	if ($item['image'])
	{
		$openGraph['image'] = IA_CLEAR_URL . 'uploads/' . $item['image'];
	}

	$iaView->set('og', $openGraph);

	$iaView->title($item['title']);
	$iaView->assign('item', $item);

	$iaView->display('view');
}