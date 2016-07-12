<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of Certificates.
 *
 */
class CooltouramanViewCertificates extends JViewLegacy
{

	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		
		$this->canDo         = JHelperContent::getActions('com_cooltouraman');

		CooltouramanHelper::addSubmenu('settings');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Include the component HTML helpers.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 */
	protected function addToolbar()
	{
		$canDo = $this->canDo;
		$user  = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_COOLTOURAMAN_CERTIFICATES'), 'certificates certificate');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('certificate.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('certificate.edit');
		}
		
		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'certificates.delete');
			JToolbarHelper::divider();
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
				'a.title' => JText::_('COM_COOLTOURAMAN_CERTIFICATES_TITLE')
		);
	}
}
