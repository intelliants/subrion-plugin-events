<?php
//##copyright##

$iaDb->setTable('events');

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	$iaEvent = $iaCore->factoryPlugin('events', iaCore::ADMIN, 'event');

	switch ($pageAction)
	{
		case iaCore::ACTION_READ:
			$params = array();
			if (isset($_GET['text']) && $_GET['text'])
			{
				$stmt = '(`title` LIKE :text OR `body` LIKE :text)';
				$iaDb->bind($stmt, array('text' => '%' . $_GET['text'] . '%'));

				$params[] = $stmt;
			}

			$output = $iaEvent->gridRead($_GET,
				array('title', 'member_id', 'date', 'date_end', 'status'),
				array('status' => 'equal'),
				$params
			);

			break;

		case iaCore::ACTION_EDIT:
			$output = $iaEvent->gridUpdate($_POST);

			break;

		case iaCore::ACTION_DELETE:
			$output = $iaEvent->gridDelete($_POST);
	}

	$iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	if (iaCore::ACTION_ADD == $pageAction || iaCore::ACTION_EDIT == $pageAction)
	{
		switch ($pageAction)
		{
			case iaCore::ACTION_EDIT:
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

			case iaCore::ACTION_ADD:
				$item = array(
					'title' => '',
					'category_id' => 0,
					'date' => '',
					'date_end' => '',
					'venue' => '',
					'latitude' => '',
					'longitude' => '',
					'description' => '',
					'image' => '',
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
			$item['category_id'] = (int)$_POST['category_id'];
			$item['date'] = $_POST['date'];
			$item['date_end'] = $_POST['date_end'];
			$item['description'] = $description;
			$item['venue'] = iaSanitize::tags($_POST['venue']);
			$item['repeat'] = $_POST['repeat'];
			$item['status'] = $_POST['status'];
			$item['sponsored'] = (int)$_POST['sponsored'];
			$item['sponsored_plan_id'] = (int)$_POST['sponsored_plan_id'];
			$item['latitude'] = $_POST['latitude'] ? $_POST['latitude'] : $item['latitude'];
			$item['longitude'] = $_POST['longitude'] ? $_POST['longitude'] : $item['longitude'];

			if ($_POST['member'])
			{
				$accountName = iaSanitize::sql($_POST['member']);
				$accountId = $iaDb->one('`id`', "`username` = '$accountName' OR `fullname` = '$accountName'", iaUsers::getTable());

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

			if (isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'])
			{
				$iaPicture = $iaCore->factory('picture');

				$path = iaUtil::getAccountDir();
				$file = $_FILES['image'];
				$token = iaUtil::generateToken();
				$info = array(
					'image_width' => 1000,
					'image_height' => 750,
					'thumb_width' => 250,
					'thumb_height' => 250,
					'resize_mode' => iaPicture::CROP
				);

				if ($image = $iaPicture->processImage($file, $path, $token, $info))
				{
					if ($item['image']) // it has an already assigned image
					{
						$iaPicture = $iaCore->factory('picture');
						$iaPicture->delete($item['image']);
					}

					$item['image'] = $image;
				}
			}

			if (empty($messages))
			{
				unset($item['member']);

				switch ($pageAction)
				{
					case iaCore::ACTION_EDIT:
						$iaDb->update($item);
						$iaView->setMessages(iaLanguage::get('event_updated'), iaView::SUCCESS);

						break;
					case iaCore::ACTION_ADD:
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

		$options = array('list' => 'go_to_list', 'add' => 'add_another_one', 'stay' => 'stay_here');
		$iaView->assign('goto', $options);

		$iaView->assign('categories', $iaEvent->getCategoryOptions());
		$iaView->assign('repeat', $iaEvent->getRepeatOptions());
		$iaView->assign('status', $iaEvent->getStatusOptions());
		$iaView->assign('item', $item);

		$iaView->display('form-events');
	}
	else
	{
		$iaView->grid('_IA_URL_plugins/events/js/admin/events');
	}
}

$iaDb->resetTable();