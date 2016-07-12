<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * Teacher view class.
 */
class CooltouramanViewTeacher extends JViewLegacy
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

		$user      = JFactory::getUser();
		$canDo     = JHelperContent::getActions('com_cooltouraman');
		$isNew     = ($this->item->id == 0);
		$isProfile = $this->item->id == $user->id;

		JToolbarHelper::title(
			JText::_(
				$isNew ? 'COM_COOLTOURAMAN_VIEW_NEW_TEACHER_TITLE' : ($isProfile ? 'COM_COOLTOURAMAN_VIEW_EDIT_TEACHER_PROFILE_TITLE' : 'COM_COOLTOURAMAN_VIEW_EDIT_TEACHER_TITLE')
			),
			'teacher ' . ($isNew ? 'teacher-add' : ($isProfile ? 'teacher-profile' : 'teacher-edit'))
		);

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolbarHelper::apply('teacher.apply');
			JToolbarHelper::save('teacher.save');
		}

		if ($canDo->get('core.create') && $canDo->get('core.manage'))
		{
			JToolbarHelper::save2new('teacher.save2new');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('teacher.cancel');
		}
		else
		{
			JToolbarHelper::cancel('teacher.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
