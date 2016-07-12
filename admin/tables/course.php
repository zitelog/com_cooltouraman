<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Course Table class.
 *
 */
class CooltouramanTableCourse extends JTable
{
	/**
	 * Ensure the params and metadata in json encoded in the bind method
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $_jsonEncode = array('params', 'prices', 'info', 'weeklyfrequency', 'calendaroflessons', 'attendanceregister', 'accounting');
	
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__cooltouraman_course', 'id', $db);
		
		JTableObserverTags::createObserver($this, array('typeAlias' => 'com_cooltouraman.course'));
		JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_cooltouraman.course'));
		
		
		$this->setColumnAlias('published', 'state');

	}
	
	/**
	 * Overload the store method for the Course table.
	 *
	 * @param   boolean	Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$this->modified = $date->toSql();

		if ($this->id)
		{
			// Existing item
			$this->modified_by = $user->id;
		}
		else
		{
			// New course. A course created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->id;
			}
		}

		// Set publish_up to null date if not set
		if (!$this->publish_up)
		{
			$this->publish_up = $this->getDbo()->getNullDate();
		}

		// Set publish_down to null date if not set
		if (!$this->publish_down)
		{
			$this->publish_down = $this->getDbo()->getNullDate();
		}

		return parent::store($updateNulls);
	}
}
