<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * Certificate view class.
 */
class CooltouramanViewCertificate extends JViewLegacy
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
				$isNew ? 'COM_COOLTOURAMAN_VIEW_NEW_CERTIFICATE_TITLE' : 'COM_COOLTOURAMAN_VIEW_EDIT_CERTIFICATE_TITLE'
			),
			'certificate ' . ($isNew ? 'certificate-add' : 'certificate-edit')
		);

		if ($canDo->get('core.edit') || $canDo->get('core.create'))
		{
			JToolbarHelper::apply('certificate.apply');
			JToolbarHelper::save('certificate.save');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('certificate.cancel');
		}
		else
		{
			JToolbarHelper::cancel('certificate.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
