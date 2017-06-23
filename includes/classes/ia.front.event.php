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
class iaEvent extends abstractModuleFront
{
    protected static $_table = 'events';
    protected $_categoriesTable = 'events_categories';

    protected $_itemName = 'events';

//    protected $_repeatOptions = ['none', 'monthly', 'yearly'];
    protected $_statusOptions = [iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE];

    protected $_dateFormat = 'Y-m-d H:i';

    public $foundRows;


    public function getCategoriesTable()
    {
        return $this->_categoriesTable;
    }

    protected function _get($conditions = [], $additionalConditions = null, $start, $limit, $defaultSorting = true)
    {
        $where = iaDb::EMPTY_CONDITION;
        if ($conditions) {
            foreach ($conditions as $column => $value) {
                $where .= " AND e.`$column` = '" . iaSanitize::sql($value) . "'";
            }
        }

        empty($additionalConditions) || $where .= ' AND ' . $additionalConditions;

        if (!$this->iaCore->get('events_show_past_events')) {
            $where .= " AND e.`date_end` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)";
        }

        $sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS 
e.*,
DATE_FORMAT(e.`date`, ":format") `date`, DATE_FORMAT(e.`date_end`, ":format") `date_end`,
m.`username` `owner`, m.`fullname` `owner_fullname`
FROM `:table_events` e
LEFT JOIN `:table_members` m ON (m.`id` = e.`member_id`)
WHERE :where
GROUP BY e.`id`
ORDER BY e.`date` :direction
LIMIT :start, :limit
SQL;

        $sql = iaDb::printf($sql, [
            'table_events' => self::getTable(true),
            'table_members' => iaUsers::getTable(true),
            'format' => $this->iaCore->get('date_format') . ' %H:%i',
            'where' => $where,
            'direction' => $defaultSorting ? iaDb::ORDER_ASC : iaDb::ORDER_DESC,
            'start' => (int)$start,
            'limit' => (int)$limit,
            'lang' => $this->iaCore->language['iso']
        ]);

        $result = $this->iaDb->getAll($sql);
        $this->foundRows = $this->iaDb->foundRows();

        $this->_processValues($result);

        return $result;
    }

    public function url($action, array $listingData, $relativeToRoot = false)
    {
        $patterns = [
            'default' => ':action/:id/',
            'view' => 'event/:title-:id.html',
            'my' => 'profile/events/'
        ];
//        $baseUrl = ('my' == $action) ? IA_URL : $this->getInfo('url');
        $baseUrl = IA_URL;
//
        if ($action != 'view') {
            $baseUrl = $this->iaCore->factory('page')->getUrlByName('event_' . $action);
        }

        $uri = iaDb::printf(
            isset($patterns[$action]) ? $patterns[$action] : $patterns['default'],
            [
                'action' => $action,
                'title' => isset($listingData['title_' . $this->iaCore->language['iso']]) ? iaSanitize::alias($listingData['title_' . $this->iaCore->language['iso']]) : '',
                'id' => isset($listingData['id']) ? $listingData['id'] : ''
            ]
        );

        return $baseUrl . $uri;
    }

    public function coreSearch($query, $start, $limit, $order)
    {
        $where = '(e.`title_:lang` LIKE :query OR e.`description` LIKE :query OR e.`venue` LIKE :query)';
        $this->iaDb->bind($where,
            ['query' => '%' . iaSanitize::sql($query) . '%', 'lang' => $this->iaCore->language['iso']]);

        $rows = $this->get(null, $start, $limit, $where);

        return [$this->iaDb->foundRows(), $rows];
    }

//    public function getRepeatOptions()
//    {
//        $result = [];
//        foreach ($this->_repeatOptions as $option) {
//            $result[$option] = iaLanguage::get($option == 'none' ? 'once' : $option);
//        }
//
//        return $result;
//    }

    public function getStatusOptions()
    {
        $result = [];
        foreach ($this->_statusOptions as $option) {
            $result[$option] = iaLanguage::get($option);
        }

        return $result;
    }

    public function getCategoryOptions()
    {
        return $this->iaDb->keyvalue(['id', 'title_' . $this->iaCore->language['iso']], null,
            $this->getCategoriesTable());
    }

    public function getCategories()
    {

        $sql = <<<SQL
SELECT c.*, COUNT(e.`id`) `num`, c.`title_:lang` `title`
FROM `:prefix:table_categories` c
LEFT JOIN `:prefix:table_events` e ON :condition
WHERE c.`status` = ':status'
GROUP BY c.`id`
ORDER BY c.`title_:lang`
SQL;

        $sql = iaDb::printf($sql, [
            'prefix' => $this->iaDb->prefix,
            'table_categories' => self::getCategoriesTable(),
            'table_events' => self::getTable(),
            'status' => iaCore::STATUS_ACTIVE,
            'condition' => $this->iaCore->get('events_show_past_events')
                ? "(e.`category_id` = c.`id` AND e.`status` = '" . iaCore::STATUS_ACTIVE . "') "
                : "(e.`category_id` = c.`id` AND e.`status` = '" . iaCore::STATUS_ACTIVE . "' AND e.`date_end` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)) ",
            'lang' => $this->iaCore->language['iso']
        ]);
        return $this->iaDb->getAll($sql);
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    public function get(
        $conditions,
        $start,
        $limit,
        $additionalStatement = false,
        $direction = true,
        $ignoreStatus = false
    ) {
        $ignoreStatus || $conditions['status'] = iaCore::STATUS_ACTIVE;

        return $this->_get(array_merge($conditions), $additionalStatement ? $additionalStatement : null, $start, $limit,
            $direction);
    }

//    public function getForMonth($month, $year)
//    {
//        $sql = <<<SQL
//SELECT `title`, DAYOFMONTH(`date`) `day_start`, DAYOFMONTH(`date_end`) `day_end`
//FROM `:table`
//WHERE `status` = 'active'
//AND (MONTH(`date`) = ':month'
//AND YEAR(`date`) = ':year'
//AND `repeat` = 'none')
//OR (`repeat` = 'yearly'
//AND MONTH(`date`) = ':month')
//OR (`repeat` = 'monthly')";
//SQL;
//
//
//
//        $sql = iaDb::printf($sql, [
//            'table' => self::getTable(true),
//            'month' => $month,
//            'year' => $year
//        ]);
//
//        return $this->iaCore->iaDb->getAll($sql);
//    }

    public function getFuture($limit)
    {
        return $this->_get(['status' => iaCore::STATUS_ACTIVE], "`date` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)", 0,
            $limit);
    }

    public function getPast($limit)
    {
        return $this->_get(['status' => iaCore::STATUS_ACTIVE], "`date_end` < DATE_SUB(NOW(), INTERVAL 1 MINUTE)",
            0, $limit, false);
    }

    public function getByDate($date, $limit)
    {
        $conditions = "DATE_FORMAT(e.`date`, '%Y-%m-%d') <= '" . $date . "' AND "
            . "DATE_FORMAT(e.`date_end`, '%Y-%m-%d') >= '" . $date . "'";
        return $this->_get(['status' => iaCore::STATUS_ACTIVE], $conditions, 0, $limit);
    }

    public function getById($id, $decorate = true)
    {
        $sql = <<<SQL
SELECT * FROM :table_events
WHERE `id` = :id
SQL;
        $sql = iaDb::printf($sql, [
            'table_events' => self::getTable(true),
            'id' => $id
        ]);

        $result = $this->iaDb->getRow($sql);

        $decorate && $this->_processValues($result, true);

        return $result;
    }

    public function insert(array $listingData)
    {
        $listingData['member_id'] = iaUsers::hasIdentity() ? iaUsers::getIdentity()->id : 0;

        $result = parent::insert($listingData);

        if (!iaUsers::hasIdentity() && $result) {
            $this->_rememberUsersListing($listingData, $result);
        }

        return $result;
    }

    public function update(array $itemData, $id)
    {
        if (empty($id)) {
            return false;
        }

        $currentData = $this->iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($id), self::getTable());
        $result = (bool)$this->iaDb->update($itemData, iaDb::convertIds($id), null, self::getTable());

        if ($result) {

            $this->iaCore->startHook('phpListingUpdated', [
                'itemId' => $id,
                'itemName' => $this->getItemName(),
                'itemData' => $itemData,
                'previousData' => $currentData
            ]);
        }

        return $result;
    }

    public function getListingCountCurrentMember()
    {
        return $this->iaDb->one('COUNT(*)', "`member_id` = '" . iaUsers::getIdentity()->id . "' && `status` = '" . iaCore::STATUS_ACTIVE . "'", self::getTable());
    }
}