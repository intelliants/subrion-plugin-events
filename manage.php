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

$iaEvent = $iaCore->factoryPlugin(IA_CURRENT_PLUGIN, 'common', 'event');

if ($iaView->getRequestType() != iaView::REQUEST_HTML)
{
	return;
}

$iaDb->setTable('events');

iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

if ('event_edit' == $iaView->name())
{
	if (isset($iaCore->requestPath[0]) && is_numeric($iaCore->requestPath[0]))
	{
		$stmt = sprintf('`id` = %d', $iaCore->requestPath[0]);
		$item = $iaDb->row('*', $stmt);

		$item['date'] = substr($item['date'], 0, -3);
		$item['date_end'] = substr($item['date_end'], 0, -3);

		if (empty($item))
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
		}

		$iaView->assign('item', $item);
	}
	else
	{
		return iaView::errorPage(iaView::ERROR_NOT_FOUND, iaLanguage::get('event_not_found'));
	}
}
else
{
	if (!iaUsers::hasIdentity())
	{
		return iaView::accessDenied(iaLanguage::getf('events_submission_is_not_allowed_for_guests', array('base_url' => IA_URL)));
	}

	$iaPlan = $iaCore->factory('plan');
	$iaView->assign('plans', $iaPlan->getPlans($iaEvent->getItemName()));

	$date = date($iaEvent->getDateFormat());

	$item = array(
		'title' => '',
		'category_id' => 0,
		'date' => $date,
		'date_end' => $date,
		'venue' => '',
		'description' => 'Detailed description of event goes here...', // TODO: Is it needed to be translated?
		'repeat' => 'none'
	);
}

if (isset($_POST['create']))
{
	$messages = array();

	if (!iaUsers::hasIdentity() && !iaValidate::isCaptchaValid())
	{
		$messages[] = iaLanguage::get('confirmation_code_incorrect');
	}

	$item['title'] = iaSanitize::html($_POST['title']);

	if (empty($item['title']))
	{
		$messages[] = iaLanguage::get('title_is_empty');
	}

	$iaUtil = iaCore::util();
	$item['description'] = $iaUtil->safeHTML($_POST['description']);

	if (empty($item['description']))
	{
		$messages[] = iaLanguage::getf('field_is_empty', array('field' => iaLanguage::get('description')));
	}

	if (!array_key_exists($_POST['repeat'], $iaEvent->getRepeatOptions()))
	{
		$messages[] = iaLanguage::get('incorrect_repeat_value');
	}

	$item['category_id'] = (int)$_POST['category_id'];
	$item['date'] = $_POST['date'];
	$item['date_end'] = $_POST['date_end'];
	$item['repeat'] = $_POST['repeat'];
	$item['venue'] = iaSanitize::tags($_POST['venue']);
	$item['status'] = $iaCore->get('events_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_INACTIVE;
	$item['member_id'] = (int)iaUsers::getIdentity()->id;

	if (empty($messages))
	{
		if (isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'])
		{
			$iaPicture = $iaCore->factory('picture');

			$info = array(
				'image_width' => 1000,
				'image_height' => 750,
				'thumb_width' => 250,
				'thumb_height' => 250,
				'resize_mode' => iaPicture::CROP
			);

			if ($image = $iaPicture->processImage($_FILES['image'], iaUtil::getAccountDir(), iaUtil::generateToken(), $info))
			{
				if ($item['image']) // it has an already assigned image
				{
					$iaPicture = $iaCore->factory('picture');
					$iaPicture->delete($item['image']);
				}

				$item['image'] = $image;
			}
		}

		if (isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'])
		{
			if ($_POST['id'] != $iaCore->requestPath[0])
			{
				return iaView::errorPage(iaVIew::ERROR_INTERNAL, iaLanguage::get('internal_error'));
			}
			$result = $iaDb->update($item);
			$iaView->setMessages(iaLanguage::get('event_updated'), iaVIew::SUCCESS);
		}
		else
		{
			$item['id'] = $iaDb->insert($item);

			if (!iaUsers::hasIdentity() && $iaCore->get('new_event_notification'))
			{
				$iaMailer = $iaCore->factory('mailer');
				$iaMailer->loadTemplate('new_event_notification');
				$iaMailer->setReplacements(array('url' => '<a href="' . IA_ADMIN_URL . 'events' . '">' . IA_ADMIN_URL . 'events' . '</a>'));
				$iaMailer->sendToAdministrators();
			}
			if ($iaCore->get('events_auto_approval'))
			{
				$messages = iaLanguage::get('event_added');
			}
			else
			{
				$messages = iaLanguage::get('event_waiting_for_approval');
			}

			if (isset($_POST['plan_id']) && $_POST['plan_id'])
			{
				$plan = $iaPlan->getPlanById($_POST['plan_id']);

				$item['sponsored'] = 1;
				$item['sponsored_plan_id'] = $plan['id'];
				$iaDb->update($item);

				if ($plan['cost'] > 0)
				{
					$url = $iaPlan->prePayment($iaEvent->getItemName(), $item, $plan['id']);
					iaUtil::redirect(iaLanguage::get('redirect'), $messages, $url);
				}
				else
				{
					$iaView->setMessages($messages, iaView::SUCCESS);
				}
			}
			else
			{
				$iaView->setMessages($messages, iaView::SUCCESS);
			}
		}
		if (!iaUsers::hasIdentity() && !$iaAcl->isAccessible('event_edit'))
		{
			iaUtil::go_to(iaDb::printf(':rootevents/', array('root' => IA_URL)));
		}
		else
		{
			iaUtil::go_to(iaDb::printf(':rootevents/edit/:id/', array('root' => IA_URL, 'id' => $item['id'])));
		}
	}
	else
	{
		$iaView->setMessages($messages);
	}
}

$iaView->assign('categories', $iaEvent->getCategoryOptions());
$iaView->assign('repeat', $iaEvent->getRepeatOptions());
$iaView->assign('item', $item);

$iaView->display('manage');

$iaDb->resetTable();