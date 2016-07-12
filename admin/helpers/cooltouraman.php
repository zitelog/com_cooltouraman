<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die;
 
/**
 * Ola component helper.
 */
abstract class CooltouramanHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($submenu)
	{
		$menus = array('cooltouraman' => false, 'courses' => false,
					   'templates' => false, 'categories' => false,
					   'students' => false, 'teachers' => false, 
					   'settings' => false
					   );
					   
		if (array_key_exists($submenu, $menus))
			$menus[$submenu] = true;
		
		
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_HOME'), 'index.php?option=com_cooltouraman', $menus['cooltouraman']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_COURSES'),'index.php?option=com_cooltouraman&view=courses', $menus['courses']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_COURSE_TEMPLATES'),'index.php?option=com_cooltouraman&view=templates', $menus['templates']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_CATEGORIES'),'index.php?option=com_categories&view=categories&extension=com_cooltouraman', $menus['categories']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_STUDENTS'),'index.php?option=com_cooltouraman&view=students', $menus['students']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_TEACHERS'),'index.php?option=com_cooltouraman&view=teachers', $menus['teachers']);
		JHtmlSidebar::addEntry(JText::_('COM_COOLTOURAMAN_SETTINGS'),'index.php?option=com_cooltouraman&view=settings', $menus['settings']);
	}
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
 
		if (empty($messageId)) {
			$assetName = 'com_cooltouraman';
		}
		else {
			$assetName = 'com_cooltouraman.message.'.(int) $messageId;
		}
 
		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);
 
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
 
		return $result;
	}
	
	/**
	 * Build an array of block/unblock student states to be used by jgrid.state,
	 * State options will be different for any student
	 * and for currently logged in student
	 *
	 * @param   boolean  $self  True if state array is for currently logged in student
	 *
	 * @return  array  a list of possible states to display
	 *
	 */
	public static function blockStates( $self = false)
	{
		if ($self)
		{
			$states = array(
				0 => array(
					'task'           => 'unblock',
					'text'           => '',
					'active_title'   => 'COM_COOLTOURAMAN_STUDENT_FIELD_BLOCK_DESC',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'unpublish',
					'inactive_class' => 'unpublish',
				),
				1 => array(
					'task'           => 'block',
					'text'           => '',
					'active_title'   => '',
					'inactive_title' => 'COM_COOLTOURAMAN_ERROR_CANNOT_BLOCK_SELF',
					'tip'            => true,
					'active_class'   => 'publish',
					'inactive_class' => 'publish',
				)
			);
		}
		else
		{
			$states = array(
				0 => array(
					'task'           => 'unblock',
					'text'           => '',
					'active_title'   => 'COM_COOLTOURAMAN_TOOLBAR_UNBLOCK',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'unpublish',
					'inactive_class' => 'unpublish',
				),
				1 => array(
					'task'           => 'block',
					'text'           => '',
					'active_title'   => 'COM_COOLTOURAMAN_STUDENT_FIELD_BLOCK_DESC',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'publish',
					'inactive_class' => 'publish',
				)
			);
		}

		return $states;
	}
}