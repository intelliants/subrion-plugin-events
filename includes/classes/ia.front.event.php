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

class iaEvent extends abstractCore
{
	protected static $_table = 'events';
	protected $_categoriesTable = 'events_categories';

	protected static $_itemName = 'events';

	protected $_repeatOptions = array('none', 'monthly', 'yearly');
	protected $_statusOptions = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE);

	protected $_dateFormat = 'Y-m-d H:i';


	public function getCategoriesTable()
	{
		return $this->_categoriesTable;
	}

	protected function _get($conditions = array(), $additionalConditions = null, $start, $limit, $defaultSorting = true)
	{
		$where = iaDb::EMPTY_CONDITION;
		if ($conditions)
		{
			foreach ($conditions as $column => $value)
				$where.= " AND e.`$column` = '" . iaSanitize::sql($value) . "'";
		}

		empty($additionalConditions) || $where.= ' AND ' . $additionalConditions;

		if (!$this->iaCore->get('events_show_past_events'))
		{
			$where.= " AND e.`date_end` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)";
		}

		$sql =
			'SELECT SQL_CALC_FOUND_ROWS '
				. 'e.*, '
				. 'DATE_FORMAT(e.`date`, ":format") `date`, DATE_FORMAT(e.`date_end`, ":format") `date_end`, '
				. 'm.`username` `owner`, m.`fullname` `owner_fullname` '
			. 'FROM `:table_events` e '
			. 'LEFT JOIN `:table_members` m ON (m.`id` = e.`member_id`) '
			. 'WHERE :where '
			. 'GROUP BY e.`id` '
			. 'ORDER BY e.`date` :direction '
			. 'LIMIT :start, :limit';

		$sql = iaDb::printf($sql, array(
			'table_events' => self::getTable(true),
			'table_members' => iaUsers::getTable(true),
			'format' => $this->iaCore->get('date_format') . ' %H:%i',
			'where' => $where,
			'direction' => $defaultSorting ? iaDb::ORDER_ASC : iaDb::ORDER_DESC,
			'start' => (int)$start,
			'limit' => (int)$limit
		));

		return $this->_processValues($this->iaDb->getAll($sql));
	}

	public function getItemName()
	{
		return self::$_itemName;
	}

	public function url($eventData)
	{
		$alias = $eventData['title'];

		if (empty($alias))
		{
			return false;
		}

		iaCore::util();
		iaUtil::loadUTF8Functions('ascii', 'validation', 'bad', 'utf8_to_ascii');

		utf8_is_ascii($alias) || $alias = utf8_to_ascii($alias);

		$alias = iaSanitize::alias($alias);

		return iaDb::printf(':urlevent/:title-:id.html', array('url' => IA_URL, 'title' => $alias, 'id' => $eventData['id']));
	}

	public function coreSearch($query, $start, $limit)
	{
		$where = '(e.`title` LIKE :query OR e.`description` LIKE :query OR e.`venue` LIKE :query)';
		$this->iaDb->bind($where, array('query' => '%' . iaSanitize::sql($query) . '%'));

		$rows = $this->get(null, $start, $limit, $where);

		return array($this->iaDb->foundRows(), $rows);
	}

	protected function _processValues($entries)
	{
		if (is_array($entries))
		{
			foreach ($entries as &$entry)
			{
				$entry['url'] = $this->url($entry);
			}
		}

		return $entries;
	}

	public function getRepeatOptions()
	{
		$result = array();
		foreach ($this->_repeatOptions as $option)
		{
			$result[$option] = iaLanguage::get($option == 'none' ? 'once' : $option);
		}

		return $result;
	}

	public function getStatusOptions()
	{
		$result = array();
		foreach ($this->_statusOptions as $option)
		{
			$result[$option] = iaLanguage::get($option);
		}

		return $result;
	}

	public function getCategoryOptions()
	{
		return $this->iaDb->keyvalue(array('id', 'title'), null, $this->getCategoriesTable());
	}

	public function getCategories()
	{
		$sql = 'SELECT c.*, COUNT(e.`id`) `num` ';
		$sql .= 'FROM `:prefix:table_categories` c ';
		$sql .= "LEFT JOIN `:prefix:table_events` e ON ";
		$sql .= $this->iaCore->get('events_show_past_events')
			? "(e.`category_id` = c.`id` AND e.`status` = ':status') "
			: "(e.`category_id` = c.`id` AND e.`status` = ':status' AND e.`date_end` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)) ";
		$sql .= "WHERE c.`status` = ':status' ";
		$sql .= 'GROUP BY c.`id` ';
		$sql .= 'ORDER BY c.`title`';

		$sql = iaDb::printf($sql, array(
			'prefix' => $this->iaDb->prefix,
			'table_categories' => self::getCategoriesTable(),
			'table_events' => self::getTable(),
			'status' => iaCore::STATUS_ACTIVE
		));

		return $this->iaDb->getAll($sql);
	}

	public function getDateFormat()
	{
		return $this->_dateFormat;
	}

	public function get($conditions, $start, $limit, $additionalStatement = false, $direction = true, $ignoreStatus = false)
	{
		$ignoreStatus || $conditions['status'] = iaCore::STATUS_ACTIVE;

		return $this->_get(array_merge($conditions), $additionalStatement ? $additionalStatement : null, $start, $limit, $direction);
	}

	public function getForMonth($month, $year)
	{
		$sql = "SELECT `title`, DAYOFMONTH(`date`) `day_start`, DAYOFMONTH(`date_end`) `day_end` FROM `:table` WHERE `status` = 'active' AND (MONTH(`date`) = ':month' AND YEAR(`date`) = ':year' AND `repeat` = 'none') OR (`repeat` = 'yearly' AND MONTH(`date`) = ':month') OR (`repeat` = 'monthly')";
		$sql = iaDb::printf($sql, array('table' => self::getTable(true), 'month' => $month, 'year' => $year));

		return $this->iaCore->iaDb->getAll($sql);
	}

	public function getFuture($limit)
	{
		return $this->_get(array('status' => iaCore::STATUS_ACTIVE), "`date` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)", 0, $limit);
	}

	public function getPast($limit)
	{
		return $this->_get(array('status' => iaCore::STATUS_ACTIVE), "`date_end` < DATE_SUB(NOW(), INTERVAL 1 MINUTE)", 0, $limit, false);
	}

	public function getByDate($date, $limit)
	{
		$conditions = "DATE_FORMAT(t1.`date`, '%Y-%m-%d') <= '" . $date . "' AND "
						. "DATE_FORMAT(t1.`date_end`, '%Y-%m-%d') >= '" . $date . "'";

		return $this->_get(array('status' => iaCore::STATUS_ACTIVE), $conditions, 0, $limit);
	}
}