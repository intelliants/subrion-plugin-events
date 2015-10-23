<?php
//##copyright##

$iaDb->setTable('events');

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	switch ($iaView->name())
	{
		case 'event_edit':

			if (isset($iaCore->requestPath[0]) && is_numeric($iaCore->requestPath[0]))
			{
				$stmt = sprintf('`id` = %d', (int)$iaCore->requestPath[0]);
				$item = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, $stmt);

				if (empty($item))
				{
					return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('item_is_not_specified'));
				}

				$item['date'] = substr($item['date'], 0, -3);
				$item['date_end'] = substr($item['date_end'], 0, -3);
				$item['member'] = $iaDb->one('username', '`id` = ' . $item['member_id'], iaUsers::getTable());
			}
			else
			{
				return iaView::errorPage(iaLanguage::get('item_is_not_specified'));
			}
			break;

		case 'event_add':
			$item = array(
				'title' => '',
				'date' => '',
				'date_end' => '',
				'venue' => '',
				'description' => '',
				'repeat' => 'none',
				'status' => 'active',
				'member' => iaUsers::getIdentity()->username,
				'sponsored' => 0,
				'sponsored_plan_id' => 0
			);
	}

	$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN, 'common', 'event');

	$iaPlan = $iaCore->factory('plan');
	$plans = $iaPlan->getPlans($iaEvent->getItemName());
	$iaView->assign('plans', $plans);

	if (isset($_POST['save']))
	{
		$messages = array();

		$title = iaSanitize::html($_POST['title']);

		if (empty($title))
		{
			$messages[] = iaLanguage::get('title_is_empty');
		}

		$iaUtil = iaCore::util();
		$description = $iaUtil->safeHTML($_POST['description']);

		if (empty($description))
		{
			$messages[] = iaLanguage::get('description_is_empty');
		}

		if (!array_key_exists($_POST['repeat'], $iaEvent->getRepeatOptions()))
		{
			$messages[] = iaLanguage::get('incorrect_repeat_value');
		}

		if (!array_key_exists($_POST['status'], $iaEvent->getStatusOptions()))
		{
			$messages[] = iaLanguage::get('incorrect_status');
		}

		$item['title'] = $title;
		$item['date'] = $_POST['date'];
		$item['date_end'] = $_POST['date_end'];
		$item['description'] = $description;
		$item['venue'] = iaSanitize::tags($_POST['venue']);
		$item['repeat'] = $_POST['repeat'];
		$item['status'] = $_POST['status'];
		$item['sponsored'] = (int)$_POST['sponsored'];
		$item['sponsored_plan_id'] = (int)$_POST['sponsored_plan_id'];

		if ($_POST['member'])
		{
			$accountName = iaSanitize::sql($_POST['member']);
			$accountId = $iaDb->one('`id`', "`username` = '$accountName' OR `fullname` = '$accountName'", 'accounts');
			if (empty($accountId))
			{
				$messages[] = iaLanguage::get('incorrect_owner');
			}
			else
			{
				$item['member_id'] = $accountId;
			}
		}
		else
		{
				$item['member_id'] = iaUsers::getIdentity()->id;
		}

		if (empty($messages))
		{
			unset($item['member']);

			switch ($iaView->name())
			{
				case 'event_edit':
					$iaDb->update($item);
					$iaView->setMessages(iaLanguage::get('event_updated'), iaView::SUCCESS);

					break;
				case 'event_add':
					$item['id'] = $iaDb->insert($item);
					$iaView->setMessages(iaLanguage::get('event_added'), iaView::SUCCESS);
			}

			if (isset($_POST['goto']))
			{
				$url = IA_ADMIN_URL . 'events/';
				$goto = array(
					'add'	=> $url . 'add/',
					'list'	=> $url,
					'stay'	=> $url . 'edit/' . $item['id'] . '/',
				);

				iaUtil::post_goto($goto);
			}
			else
			{
				iaUtil::go_to(IA_ADMIN_URL . 'events/edit/' . $item['id'] . '/');
			}
		}

		$iaView->setMessages($messages);
	}

	iaBreadcrumb::add(iaLanguage::get('events'), IA_ADMIN_URL . 'events/');

	$iaView->assign('repeat', $iaEvent->getRepeatOptions());
	$iaView->assign('status', $iaEvent->getStatusOptions());
	$iaView->assign('item', $item);

	$iaView->display('manage');
}

$iaDb->resetTable();