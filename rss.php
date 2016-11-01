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