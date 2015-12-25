<?php
//##copyright##

class iaEvent extends abstractPlugin
{
	protected static $_table = 'events';
	protected $_categoriesTable = 'events_categories';


	public function gridRead($params, $columns, array $filterParams = array(), array $persistentConditions = array())
	{
		$params || $params = array();

		$start = isset($params['start']) ? (int)$params['start'] : 0;
		$limit = isset($params['limit']) ? (int)$params['limit'] : 15;

		$sort = $params['sort'];
		$dir = in_array($params['dir'], array(iaDb::ORDER_ASC, iaDb::ORDER_DESC)) ? $params['dir'] : iaDb::ORDER_ASC;
		$order = ($sort && $dir) ? " ORDER BY `{$sort}` {$dir}" : '';

		$where = $values = array();
		foreach ($filterParams as $name => $type)
		{
			if (isset($params[$name]) && $params[$name])
			{
				$value = iaSanitize::sql($params[$name]);

				switch ($type)
				{
					case 'equal':
						$where[] = sprintf('`%s` = :%s', $name, $name);
						$values[$name] = $value;
						break;
					case 'like':
						$where[] = sprintf('`%s` LIKE :%s', $name, $name);
						$values[$name] = '%' . $value . '%';
				}
			}
		}

		$where = array_merge($where, $persistentConditions);
		$where || $where[] = iaDb::EMPTY_CONDITION;
		$where = implode(' AND ', $where);
		$this->iaDb->bind($where, $values);

		if (is_array($columns))
		{
			$columns = array_merge(array('id', 'delete' => 1, 'update' => 1), $columns);
		}

		$stmtFields = $columns;

		if (is_array($columns))
		{
			$stmtFields = '';
			foreach ($columns as $key => $field)
			{
				$stmtFields .= is_int($key)
					? 'e.`' . $field . '`'
					: sprintf('%s `%s`', is_numeric($field) ? $field : '`' . $field . '`', $key);
				$stmtFields .= ', ';
			}
			$stmtFields = substr($stmtFields, 0, -2);
		}

		$sql = 'SELECT ' . $stmtFields . ', IF(m.`fullname` != "", m.`fullname`, m.`username`) `username`'
			. ' FROM `' . self::getTable(true) . '` e'
			. ' LEFT JOIN `' . iaUsers::getTable(true) . '` m ON e.`member_id` = m.`id`'
			. ' WHERE ' . $where
			. $order;

		if ($limit && stripos($where, 'limit') === false)
		{
			$sql .= ' LIMIT ' . $start . ', ' . $limit;
		}

		return array(
			'data' => $this->iaDb->getAll($sql),
			'total' => (int)$this->iaDb->one(iaDb::STMT_COUNT_ROWS, $where)
		);
	}

	public function delete($id)
	{
		$row = $this->iaDb->row('image', iaDb::convertIds($id));
		$result = parent::delete($id);

		if ($result && !empty($row['image']))
		{
			$iaPicture = $this->iaCore->factory('picture');
			$iaPicture->delete($row['image']);
		}

		return $result;
	}
}