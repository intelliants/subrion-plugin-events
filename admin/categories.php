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
	protected $_name = 'categories';

	protected $_table = 'events_categories';

	protected $_gridColumns = array('title', 'slug', 'status');
	protected $_gridFilters = array('status' => self::EQUAL, 'title' => self::LIKE);


	public function init()
	{
		$this->_template = 'form-categories';
		$this->_path = IA_ADMIN_URL . 'events' . IA_URL_DELIMITER . $this->getName() . IA_URL_DELIMITER;

		if (iaView::REQUEST_HTML == $this->_iaCore->iaView->getRequestType())
		{
			iaBreadcrumb::insert(iaLanguage::get('events'), IA_ADMIN_URL . 'events/', iaBreadcrumb::POSITION_FIRST + 1);
		}
	}

	protected function _setDefaultValues(array &$entry)
	{
		$entry = array(
			'title' => '',
			'slug' => '',
			'status' => iaCore::STATUS_ACTIVE
		);
	}

	protected function _preSaveEntry(array &$entry, array $data, $action)
	{
		$entry['title'] = $data['title'];
		$entry['slug'] = strtolower(iaSanitize::alias($entry['title'] ? $entry['title'] : $entry['slug']));
		$entry['status'] = $data['status'];

		$requiredFields = array('title', 'slug');

		foreach ($requiredFields as $fieldName)
		{
			if (empty($entry[$fieldName]))
			{
				$this->addMessage(iaLanguage::getf('field_is_empty', array('field' => iaLanguage::get($fieldName))), false);
			}
		}

		return !$this->getMessages();
	}

	protected function _setPageTitle(&$iaView, array $entryData, $action)
	{
		$iaView->title(iaLanguage::get($action . '_category', $iaView->title()));
	}
}