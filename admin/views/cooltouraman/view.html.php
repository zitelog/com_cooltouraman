<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
class CooltouramanViewCooltouraman extends JViewLegacy
{	
	public function display($tpl = null) 	
	{		
		require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_cooltouraman' . DS .'helpers' . DS . 'cooltouraman.php');		CooltouramanHelper::addSubmenu('cooltouraman');
		$this->sidebar = JHtmlSidebar::render();			
		JToolBarHelper::title(JText::_('COM_COOLTOURAMAN'), 'cooltouraman');	
		parent::display($tpl);
	}
}