<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2017 Intelliants, LLC <http://www.intelliants.com>
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
    $iaField = $iaCore->factory('field');
    $iaEvent = $iaCore->factoryPlugin(IA_CURRENT_MODULE);

    $iaDb->setTable($iaEvent::getTable());
    $listing = [];
    iaBreadcrumb::add(iaLanguage::get('events'), IA_URL . 'events/');

    switch ($pageAction) {
        case iaCore::ACTION_DELETE:
        case iaCore::ACTION_EDIT:
            $id = (int)(isset($_GET['id']) ? $_GET['id'] : end($iaCore->requestPath));
            if (!$id) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            $listing = $iaEvent->getById($id);

            if (empty($listing)) {
                return iaView::errorPage(iaView::ERROR_NOT_FOUND);
            }

            if ($listing['member_id'] != iaUsers::getIdentity()->id) {
                return iaView::accessDenied(iaLanguage::get('you_have_to_be_owner_to_edit'));
            }

            break;
    }

    if (isset($_POST['create'])) {
        $error = false;
        $messages = [];

        list($item, $error, $messages) = $iaField->parsePost($iaEvent->getItemName(), $listing);

        if (!iaUsers::hasIdentity() && !iaValidate::isCaptchaValid()) {
            $error = true;
            $messages[] = iaLanguage::get('confirmation_code_incorrect');
        }

        if ($error) {
            $listing = $item;
            $listing['status'] = $_POST['status'];

            $iaView->setMessages($messages);
        } else {

            if (iaCore::ACTION_ADD == $pageAction) {
                $item['status'] = $iaCore->get('events_auto_approval') ? iaCore::STATUS_ACTIVE : iaCore::STATUS_INACTIVE;
                $item['category_id'] = (int)$_POST['category_id'];
                $item['id'] = $iaEvent->insert($item);
                $result = (bool)$item['id'];
                $messages[] = iaLanguage::get('listing_successfully_submitted');

            } else {
                if (isset($_POST['status']) && $listing['status'] != iaCore::STATUS_INACTIVE) {
                    $item['status'] = iaSanitize::sql($_POST['status']);
                }

                $item['category_id'] = (int)$_POST['category_id'];
                $item['venue'] = iaSanitize::tags($_POST['venue']);
                $item['id'] = $listing['id'];

                $result = $iaEvent->update($item, $item['id']);
                $messages[] = iaLanguage::get('listing_successfully_updated');
            }

            $listing = $iaEvent->getById($item['id'], false);

            $url = $iaEvent->url('view', $item);

            if (isset($_POST['plan_id']) && $_POST['plan_id'] && $_POST['plan_id'] != $listing['sponsored_plan_id']) {
                $plan = $iaPlan->getById((int)$_POST['plan_id']);
                if ($plan['cost'] > 0) {
                    $url = $iaPlan->prePayment($iaEvent->getItemName(), $item, $plan['id']);
                } else {
                    $iaTransaction = $iaCore->factory('transaction');
                    $transactionId = $iaTransaction->create(null, 0, $iaAuto->getItemName(), $item, '',
                        (int)$_POST['plan_id'], true);
                    $transaction = $iaTransaction->getBy('sec_key', $transactionId);
                    $iaPlan->setPaid($transaction);
                }
            }
            $iaCore->startHook('phpAddItemAfterAll', [
                'type' => iaCore::FRONT,
                'listing' => $item['id'],
                'item' => $iaEvent->getItemName(),
                'data' => $item,
                'old' => $listing
            ]);

            if (!iaUsers::hasIdentity() && $iaCore->get('new_event_notification')) {
                $iaMailer = $iaCore->factory('mailer');
                $iaMailer->loadTemplate('new_event_notification');
                $iaMailer->setReplacements(['url' => '<a href="' . IA_ADMIN_URL . 'events' . '">' . IA_ADMIN_URL . 'events' . '</a>']);
                $iaMailer->sendToAdministrators();
            }

            if ($iaCore->get('events_auto_approval')) {
                $messages = iaLanguage::get('event_added');
            } else {
                $messages = iaLanguage::get('event_waiting_for_approval');
            }

            $iaView->setMessages($messages, $error ? iaView::ERROR : iaView::SUCCESS);

            iaUtil::go_to($url);
        }
    }
    $sections = $iaField->getTabs($iaEvent->getItemName(), $listing);

    $iaView->assign('categories', $iaEvent->getCategoryOptions());
    $iaView->assign('item', $listing);
    $iaView->assign('sections', $sections);

    $iaView->display('manage');
}