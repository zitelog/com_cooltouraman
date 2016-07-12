<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * Holiday view class.
 */
class CooltouramanViewHoliday extends JViewLegacy
{
	protected $form;
	protected $item;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 */
	public function display($tpl = null)
	{
		$this->form      = $this->get('Form');
		$this->item      = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		parent::display($tpl);
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$canDo     = JHelperContent::getActions('com_cooltouraman');
		$isNew     = ($this->item->id == 0);

		JToolbarHelper::title(
			JText::_(
				$isNew ? 'COM_COOLTOURAMAN_VIEW_NEW_HOLIDAY_TITLE' : 'COM_COOLTOURAMAN_VIEW_EDIT_HOLIDAY_TITLE'
			),
			'holiday ' . ($isNew ? 'holiday-add' : 'holiday-edit')
		);

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolbarHelper::apply('holiday.apply');
			JToolbarHelper::save('holiday.save');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('holiday.cancel');
		}
		else
		{
			JToolbarHelper::cancel('holiday.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
