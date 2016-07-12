<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die('Restricted access');


/**
 * View to edit a course.
 *
 */
class CooltouramanViewCourse extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;

	/**
	 * Execute and display a course script.
	 *
	 * @param   string  $tpl  The name of the course file to parse; automatically searches through the course paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 */
	public function display($tpl = null)
	{
		
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_cooltouraman', 'course', $this->item->id);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		if ($this->item->id == 0)
			$this->setLayout('new');
		
		$this->addToolbar();
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
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user       = JFactory::getUser();
		$userId     = $user->get('id');
		$isNew      = ($this->item->id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;

		JToolbarHelper::title(
			JText::_('COM_COOLTOURAMAN_PAGE_' . ($checkedOut ? 'VIEW_COURSE' : ($isNew ? 'ADD_COURSE' : 'EDIT_COURSE'))),
			'pencil-2 article-add'
		);
		
		if ($this->getLayout() === 'new' || $isNew)
		{
			JToolbarHelper::cancel('course.cancel');
			
			return true;
		}
			
		
		// For new records, check the create permission.
		if ($isNew && (count($user->getAuthorisedCategories('com_cooltouraman', 'core.create')) > 0))
		{
			JToolbarHelper::apply('course.apply');
			JToolbarHelper::save('course.save');
			JToolbarHelper::save2new('course.save2new');
			JToolbarHelper::cancel('course.cancel');
		}
		else
		{
			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
				if ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId))
				{
					JToolbarHelper::apply('course.apply');
					JToolbarHelper::save('course.save');

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						JToolbarHelper::save2new('course.save2new');
					}
				}
			}

			// If checked out, we can still save
			if ($canDo->get('core.create'))
			{
				JToolbarHelper::save2copy('course.save2copy');
			}

			if ($this->state->params->get('save_history', 0) && $canDo->get('core.edit'))
			{
				JToolbarHelper::versions('com_cooltouraman.course', $this->item->id);
			}

			JToolbarHelper::cancel('course.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
