<?php
//##copyright##

$limit = $iaCore->get('events_number_rss', 10);

$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN);
$events = $iaEvent->get(array(), 0, $limit);

if ($events)
{
	foreach ($events as $key => $item)
	{
		$events[$key]['description'] = iaSanitize::tags($item['description']);
	}
}

header('Content-Type: text/xml;');

$iaView->assign('image', array(
	'link' => IA_URL,
	'logo' => IA_TPL_URL . '/img/logo.gif',
	'title' => iaLanguage::get('events') . $iaCore->get('suffix')
));

$iaView->assign('items', $events);

$iaView->disableLayout();
$iaView->display('rss');