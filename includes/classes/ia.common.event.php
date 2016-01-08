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

	protected function _get($conditions = array(), $additional = false, $start, $limit, $defaultSorting = true)
	{
		$where = (string)'';
		if ($conditions)
		{
			$where = 'WHERE 1';
			foreach ($conditions as $column => $value)
			{
				$where .= " AND t1.`$column` = '$value'";
			}
		}

		if ($additional)
		{
			$where .= ' AND ' . $additional;
		}

		$sql = "
			SELECT SQL_CALC_FOUND_ROWS
				t1.*,
				DATE_FORMAT(t1.`date`, ':format') `date`,
				DATE_FORMAT(t1.`date_end`, ':format') `date_end`,
				t2.`username` `owner`,
				t2.`fullname` `owner_fullname`
			FROM `:table` t1
			LEFT JOIN `:memberstable` t2 ON (t2.`id` = t1.`member_id`)
			{$where}
			ORDER BY t1.`date` :direction
			LIMIT :start, :limit";

		$dtFormat = $this->iaCore->get('date_format') . ' %H:%i';
		$sql = iaDb::printf($sql, array(
			'format' => $dtFormat,
			'table' => self::getTable(true),
			'memberstable' => iaUsers::getTable(true),
			'direction' => $defaultSorting ? iaDb::ORDER_ASC : iaDb::ORDER_DESC,
			'start' => (int)$start,
			'limit' => (int)$limit)
		);

		$result = $this->iaDb->getAll($sql);

		return $result ? $this->_processValues($result) : false;
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

		if (!utf8_is_ascii($alias))
		{
			$alias = utf8_to_ascii($alias);
		}

		$alias = iaSanitize::alias($alias);

		return iaDb::printf(':urlevent/:title-:id.html', array('url' => IA_URL, 'title' => $alias, 'id' => $eventData['id']));
	}


	protected function _processValues($entries)
	{
		$result = $entries;
		if (is_array($result) && $result)
		{
			foreach ($result as $key => $event)
			{
				$result[$key]['url'] = $this->url($event);
			}
		}

		return $result;
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
		$sql = 'SELECT c.*, COUNT(e.`id`) `num` '
			. 'FROM `:prefix:table_categories` c '
			. "LEFT JOIN `:prefix:table_events` e ON (e.`category_id` = c.`id` AND e.`status` = ':status') "
			. "WHERE c.`status` = ':status' "
			. 'GROUP BY c.`id` '
			. 'ORDER BY c.`title`';

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