<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * View class for a list of students.
 *
 */
class CooltouramanViewStudents extends JViewLegacy
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

		CooltouramanHelper::addSubmenu('students');

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

		JToolbarHelper::title(JText::_('COM_COOLTOURAMAN_VIEW_STUDENTS_TITLE'), 'students student');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('student.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('student.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::divider();
			JToolbarHelper::unpublish('students.block', 'COM_COOLTOURAMAN_TOOLBAR_BLOCK', true);
			JToolbarHelper::custom('students.unblock', 'unblock.png', 'unblock_f2.png', 'COM_COOLTOURAMAN_TOOLBAR_UNBLOCK', true);
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'students.delete');
			JToolbarHelper::divider();
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			JToolbarHelper::preferences('com_cooltouraman');
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
				'a.lastname' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_LASTNAME_LABEL'),
				'a.firstname' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_FIRSTNAME_LABEL'),
				'a.parental_lastname' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_PARENT_LASTNAME_LABEL'),
				'a.parental_firstname' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_PARENT_FIRSTNAME_LABEL'),
				'a.email' => JText::_('JGLOBAL_EMAIL'),
				'a.birthday_date' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_BIRTHDAY_DATE_LABEL'),
				'a.register_date' => JText::_('COM_COOLTOURAMAN_STUDENT_FIELD_REGISTRATION_DATE_LABEL'),
				'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
