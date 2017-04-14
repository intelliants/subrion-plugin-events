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
class iaBackendController extends iaAbstractControllerModuleBackend
{
    protected $_name = 'events';

    protected $_itemName = 'events';

    protected $_gridColumns = ['title', 'member_id', 'date', 'date_end', 'status'];
    protected $_gridFilters = ['status' => self::EQUAL];
    protected $_gridQueryMainTableAlias = 'e';

    protected $_phraseAddSuccess = 'event_added';
    protected $_phraseEditSuccess = 'event_updated';
    protected $_phraseGridEntriesDeleted = 'events_deleted';

    protected $_repeatOptions = ['none', 'monthly', 'yearly'];
    protected $_statuses = [iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE];


    public function init()
    {
        $this->_template = 'form-' . $this->getName();
        $this->_path = IA_ADMIN_URL . $this->getName() . IA_URL_DELIMITER;
    }

    protected function _modifyGridParams(&$conditions, &$values, array $params)
    {
        if (!empty($params['text'])) {
            $conditions[] = '(e.`title` LIKE :text OR e.`description` LIKE :text)';
            $values['text'] = '%' . iaSanitize::sql($params['text']) . '%';
        }
    }

    protected function _gridQuery($columns, $where, $order, $start, $limit)
    {
        $sql = <<<SQL
SELECT :columns,
IF(m.`fullname` != "", m.`fullname`, m.`username`) `owner`,
1 `update`, 1 `delete`
FROM `:prefix:table_events` e
LEFT JOIN `:prefix:table_members` m ON (m.`id` = e.`member_id`)
:where 
:order
LIMIT :start, :limit
SQL;



        $sql = iaDb::printf($sql, [
            'columns' => $columns,
            'prefix' => $this->_iaDb->prefix,
            'table_events' => self::getTable(),
            'table_members' => iaUsers::getTable(),
            'start' => $start,
            'limit' => $limit,
            'where' => $where ? 'WHERE ' . $where . ' ' : '',
            'order' => $order
        ]);

        return $this->_iaDb->getAll($sql);
    }

    protected function _entryDelete($entryId)
    {
        $row = $this->getById($entryId);

        $result = parent::_entryDelete($entryId);

        if ($result && !empty($row['image'])) {
            $iaPicture = $this->_iaCore->factory('picture');
            $iaPicture->delete($row['image']);
        }

        return $result;
    }

    protected function _setDefaultValues(array &$entry)
    {
        $entry = [
            'category_id' => 0,
            'venue' => '',
            'latitude' => '',
            'longitude' => '',
            'status' => iaCore::STATUS_ACTIVE,
            'member_id' => iaUsers::getIdentity()->id,
            'sponsored' => 0,
        ];
    }

    protected function _assignValues(&$iaView, array &$entryData)
    {
        parent::_assignValues($iaView, $entryData);
        $iaPlan = $this->_iaCore->factory('plan');
        $iaUsers = $this->_iaCore->factory('users');

        $owner = empty($entryData['member_id']) ? iaUsers::getIdentity(true) : $iaUsers->getInfo($entryData['member_id']);
        $entryData['owner'] = $owner['fullname'] . " ({$owner['email']})";

        $plans = $iaPlan->getPlans($this->_itemName);

        $categories = $this->_iaDb->keyvalue(['id', 'title_' . $this->_iaCore->language['iso']], null, 'events_categories');



        $repeatOptions = [];
        foreach ($this->_repeatOptions as $option) {
            $repeatOptions[$option] = iaLanguage::get($option);
        }

        $iaView->assign('categories', $categories);
        $iaView->assign('plans', $plans);
        $iaView->assign('repeat', $repeatOptions);
        $iaView->assign('status', $this->_statuses);
    }

    protected function _preSaveEntry(array &$entry, array $data, $action)
    {
        parent::_preSaveEntry($entry, $data, $action);
        $entry['category_id'] = $data['category_id'];

        return true;
    }
}