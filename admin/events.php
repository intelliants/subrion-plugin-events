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

class iaBackendController extends iaAbstractControllerPluginBackend
{
	protected $_name = 'events';

	protected $_itemName = 'events';

	protected $_gridColumns = array('title', 'member_id', 'date', 'date_end', 'status');
	protected $_gridFilters = array('status' => self::EQUAL);
	protected $_gridQueryMainTableAlias = 'e';

	protected $_phraseAddSuccess = 'event_added';
	protected $_phraseEditSuccess = 'event_updated';
	protected $_phraseGridEntriesDeleted = 'events_deleted';

	protected $_repeatOptions = array('none', 'monthly', 'yearly');
	protected $_statuses = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE);


	public function init()
	{
		$this->_template = 'form-' . $this->getName();
	}

	protected function _modifyGridParams(&$conditions, &$values, array $params)
	{
		if (!empty($params['text']))
		{
			$conditions[] = '(e.`title` LIKE :text OR e.`description` LIKE :text)';
			$values['text'] = '%' . iaSanitize::sql($params['text']) . '%';
		}
	}

	protected function _gridQuery($columns, $where, $order, $start, $limit)
	{
		$sql =
			'SELECT :columns, '
				. 'IF(m.`fullname` != "", m.`fullname`, m.`username`) `owner`, '
				. '1 `update`, 1 `delete` '
			.'FROM `:prefix:table_events` e '
			.'LEFT JOIN `:prefix:table_members` m ON (m.`id` = e.`member_id`) '
			. ($where ? 'WHERE ' . $where . ' ' : '') . $order . ' '
			. 'LIMIT :start, :limit';

		$sql = iaDb::printf($sql, array(
			'columns' => $columns,
			'prefix' => $this->_iaDb->prefix,
			'table_events' => self::getTable(),
			'table_members' => iaUsers::getTable(),
			'start' => $start,
			'limit' => $limit
		));

		return $this->_iaDb->getAll($sql);
	}

	protected function _entryDelete($entryId)
	{
		$row = $this->getById($entryId);

		$result = parent::_entryDelete($entryId);

		if ($result && !empty($row['image']))
		{
			$iaPicture = $this->_iaCore->factory('picture');
			$iaPicture->delete($row['image']);
		}

		return $result;
	}

	protected function _setDefaultValues(array &$entry)
	{
		$entry = array(
			'title' => '',
			'category_id' => 0,
			'date' => '',
			'date_end' => '',
			'venue' => '',
			'latitude' => '',
			'longitude' => '',
			'description' => '',
			'image' => '',
			'repeat' => $this->_repeatOptions[0],
			'status' => iaCore::STATUS_ACTIVE,
			'member_id' => iaUsers::getIdentity()->id,
			'sponsored' => 0,
			'sponsored_plan_id' => 0
		);
	}

	protected function _assignValues(&$iaView, array &$entryData)
	{
		$iaPlan = $this->_iaCore->factory('plan');
		$iaUsers = $this->_iaCore->factory('users');

		$owner = empty($entryData['member_id']) ? iaUsers::getIdentity(true) : $iaUsers->getInfo($entryData['member_id']);
		$entryData['owner'] = $owner['fullname'] . " ({$owner['email']})";

		$plans = $iaPlan->getPlans($this->_itemName);

		$categories = $this->_iaDb->keyvalue(array('id', 'title'), null, 'events_categories');

		$repeatOptions = array();
		foreach ($this->_repeatOptions as $option)
		{
			$repeatOptions[$option] = iaLanguage::get($option);
		}

		$iaView->assign('categories', $categories);
		$iaView->assign('plans', $plans);
		$iaView->assign('repeat', $repeatOptions);
		$iaView->assign('status', $this->_statuses);
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		$title = iaSanitize::html($data['title']);

		if (empty($title))
		{
			$this->addMessage('title_is_empty');
		}

		$description = iaUtil::safeHTML($data['description']);

		if (empty($description))
		{
			$this->addMessage(iaLanguage::getf('field_is_empty', array('field' => iaLanguage::get('description'))), false);
		}

		if (!in_array($data['repeat'], $this->_repeatOptions))
		{
			$messages[] = iaLanguage::get('incorrect_repeat_value');
		}

		$entry['title'] = $title;
		$entry['category_id'] = (int)$data['category_id'];
		$entry['date'] = $data['date'];
		$entry['date_end'] = $data['date_end'];
		$entry['description'] = $description;
		$entry['venue'] = iaSanitize::tags($data['venue']);
		$entry['repeat'] = $data['repeat'];
		$entry['status'] = $data['status'];
		$entry['sponsored'] = (int)$data['sponsored'];
		$entry['sponsored_plan_id'] = (int)$data['sponsored_plan_id'];
		$entry['latitude'] = $data['latitude'] ? $data['latitude'] : $entry['latitude'];
		$entry['longitude'] = $data['longitude'] ? $data['longitude'] : $entry['longitude'];
		$entry['member_id'] = $data['member_id'];

		if ($this->getMessages())
		{
			return false;
		}

		unset($entry['owner']);

		if (isset($_FILES['image']['tmp_name']) && $_FILES['image']['tmp_name'])
		{
			$iaPicture = $this->_iaCore->factory('picture');

			$info = array(
				'image_width' => 1000,
				'image_height' => 750,
				'thumb_width' => 250,
				'thumb_height' => 250,
				'resize_mode' => iaPicture::CROP
			);

			if ($image = $iaPicture->processImage($_FILES['image'], iaUtil::getAccountDir(), iaUtil::generateToken(), $info))
			{
				empty($entry['image']) || $iaPicture->delete($entry['image']);
				$entry['image'] = $image;
			}
		}

		return true;
	}
}