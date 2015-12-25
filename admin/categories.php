<?php
//##copyright##

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