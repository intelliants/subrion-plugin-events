<?php
//##copyright##

class iaEvent extends abstractCore
{

	protected static $_table = 'events';
	protected static $_item = 'events';

	protected $_statuses = array(iaCore::STATUS_ACTIVE, iaCore::STATUS_INACTIVE);

	protected $_repeatOptions = array('none', 'monthly', 'yearly');
	protected $_statusOptions = array('active', 'inactive');
	protected $_dateFormat = 'Y-m-d H:i';

	protected function _get ($conditions = array(), $additional = false, $start, $limit, $defaultSorting = true)
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
		$sql = $this->printf($sql, array('format' => $dtFormat, 'table' => self::getTable(true), 'memberstable' => iaUsers::getTable(true), 'direction' => $defaultSorting ? 'ASC' : 'DESC', 'start' => $start, 'limit' => $limit));

		$result = $this->iaDb->getAll($sql);

		return $result ? $this->_processValues($result) : false;
	}

	public function getItemName()
	{
		$_item = 'none';
		if (version_compare('5.3.0', PHP_VERSION, '<='))
		{
			eval('$_item = static::$_item;');
		}
		else
		{
			$class = get_called_class();
			eval('$_item = ' . $class . '::$_item;');
		}

		return $_item;
	}

	public function url ($eventData)
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

		return $this->printf(':urlevent/:title-:id.html', array('url' => IA_URL, 'title' => $alias, 'id' => $eventData['id']));
	}


	public function _processValues ($entries)
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

	public function printf ($format, $args = array())
	{
		$result = (string)$format;
		if ($args)
		{
			$search = array_keys($args);
			foreach ($search as $key => $item)
			{
				$search[$key] = ':' . $item;
			}
			$result = str_replace($search, array_values($args), $result);
		}
		return $result;
	}

	public function getRepeatOptions ()
	{
		$result = array();
		foreach ($this->_repeatOptions as $option)
		{
			$result[$option] = $option == 'none' ? iaLanguage::get('once') : iaLanguage::get($option);
		}
		return $result;
	}

	public function getStatusOptions ()
	{
		$result = array();
		foreach ($this->_statusOptions as $option)
		{
			$result[$option] = iaLanguage::get($option);
		}
		return $result;
	}

	public function getDateFormat ()
	{
		return $this->_dateFormat;
	}

	public function get ($conditions, $start, $limit, $additionalStatement = false, $direction = true)
	{
		return $this->_get(array_merge($conditions, array('status' => 'active')), $additionalStatement ? $additionalStatement : null, $start, $limit, $direction);
	}

	public function getForMonth ($month, $year)
	{
		$sql = "SELECT `title`, DAYOFMONTH(`date`) `day_start`, DAYOFMONTH(`date_end`) `day_end` FROM `:table` WHERE `status` = 'active' AND (MONTH(`date`) = ':month' AND YEAR(`date`) = ':year' AND `repeat` = 'none') OR (`repeat` = 'yearly' AND MONTH(`date`) = ':month') OR (`repeat` = 'monthly')";
		$sql = $this->printf($sql, array('table' => self::getTable(true), 'month' => $month, 'year' => $year));

		return $this->iaCore->iaDb->getAll($sql);
	}

	public function getFuture ($limit)
	{
		return $this->_get(array('status' => 'active'), "`date` > DATE_ADD(NOW(), INTERVAL 1 MINUTE)", 0, $limit);
	}

	public function getPast ($limit)
	{
		return $this->_get(array('status' => 'active'), "`date_end` < DATE_SUB(NOW(), INTERVAL 1 MINUTE)", 0, $limit, false);
	}

	public function getByDate ($date, $limit)
	{
		$conditions = "DATE_FORMAT(t1.`date`, '%Y-%m-%d') <= '" . $date . "' AND "
						. "DATE_FORMAT(t1.`date_end`, '%Y-%m-%d') >= '" . $date . "'";
		$this->_get(array('status' => 'active'), $conditions, 0, $limit);
		return $this->_get(array('status' => 'active'), $conditions, 0, $limit);
	}
}