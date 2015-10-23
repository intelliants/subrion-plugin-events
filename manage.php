<?php
//##copyright##

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
			return iaView::errorPage(iaView::ERROR_NOT_FOUND, _('event_not_found'));
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
	$iaPlan = $iaCore->factory('plan');
	$iaView->assign('plans', $iaPlan->getPlans($iaEvent->getItemName()));

	$date = date($iaEvent->getDateFormat());

	$item = array(
		'title' => '',
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
		$messages[] = iaLanguage::get('description_is_empty');
	}

	if (!array_key_exists($_POST['repeat'], $iaEvent->getRepeatOptions()))
	{
		$messages[] = iaLanguage::get('incorrect_repeat_value');
	}

	$item['date'] = $_POST['date'];
	$item['date_end'] = $_POST['date_end'];
	$item['repeat'] = $_POST['repeat'];
	$item['venue'] = iaSanitize::tags($_POST['venue']);
	$item['status'] = $iaCore->get('events_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_INACTIVE;
	$item['member_id'] = (int)iaUsers::getIdentity()->id;

	if (empty($messages))
	{
		if (isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'])
		{
			if ($_POST['id'] != $iaCore->requestPath[0])
			{
				return iaView::errorPage(iaVIew::ERROR_INTERNAL, iaLanguage::get('internal_error'));
			}
			$iaDb->update($item);
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
					$iaView->setMessages($messages, 'success');
				}
			}
			else
			{
				$iaView->setMessages($messages, 'success');
			}
		}
		if (!iaUsers::hasIdentity() && !$iaAcl->isAccessible('event_edit'))
		{
			iaUtil::go_to($iaEvent->printf(':rootevents/', array('root' => IA_URL)));
		}
		else
		{
			iaUtil::go_to($iaEvent->printf(':rootevents/edit/:id/', array('root' => IA_URL, 'id' => $item['id'])));
		}
	}
	else
	{
		$iaView->setMessages($messages);
	}
}

$iaView->assign('repeat', $iaEvent->getRepeatOptions());
$iaView->assign('item', $item);

$iaView->display('manage');

$iaDb->resetTable();