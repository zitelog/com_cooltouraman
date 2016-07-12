<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * Student view class.
 * 
 */
class CooltouramanViewStudent extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $grouplist;
	protected $groups;
	protected $state;

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
		$this->grouplist = $this->get('Groups');
		$this->groups    = $this->get('AssignedGroups');
		$this->state     = $this->get('State');
		$this->tfaform   = $this->get('Twofactorform');
		$this->otpConfig = $this->get('otpConfig');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->form->setValue('password', null);
		$this->form->setValue('password2', null);

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
				$isNew ? 'COM_COOLTOURAMAN_VIEW_NEW_STUDENT_TITLE' : ($isProfile ? 'COM_COOLTOURAMAN_VIEW_EDIT_STUDENT_PROFILE_TITLE' : 'COM_COOLTOURAMAN_VIEW_EDIT_STUDENT_TITLE')
			),
			'student ' . ($isNew ? 'student-add' : ($isProfile ? 'student-profile' : 'student-edit'))
		);

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolbarHelper::apply('student.apply');
			JToolbarHelper::save('student.save');
		}

		if ($canDo->get('core.create') && $canDo->get('core.manage'))
		{
			JToolbarHelper::save2new('student.save2new');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('student.cancel');
		}
		else
		{
			JToolbarHelper::cancel('student.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
