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
 * Student model.
 *
 */
class CooltouramanModelStudent extends JModelAdmin
{
	
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 */
	public function getTable($type = 'Student', $prefix = 'CooltouramanTable', $config = array())
	{
		$table = JTable::getInstance($type, $prefix, $config);

		return $table;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{  
			$item->courses = array();
			
			//Get the assign courses
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select(array('a.*', 'b.title, b.duration_in_hours, b.start_date, b.attendanceregister'))
				->from($db->quoteName('#__cooltouraman_course_student_relations', 'a'))
				->join('LEFT', $db->quoteName('#__cooltouraman_course', 'b') . ' ON (' . $db->quoteName('b.id') . ' = ' . $db->quoteName('a.course_id') . ')')
				->where('a.student_id='.$item->id);
				
			$db->setQuery($query);
			
			$courses = $db->loadObjectlist();
			
			
			foreach($courses as $index => $course)
			{
				if (!empty($course->attendanceregister))
				{
					// Convert the attendanceregister field to an array.
					$registry = new Registry;
					$registry->loadString($course->attendanceregister);
					$attendanceregister = $registry->toArray();
					$item->courses[$index]["course_id"] = $course->course_id;
					$item->courses[$index]["title"] = $course->title;
					$item->courses[$index]["duration_in_hours"] = $course->duration_in_hours;
					$item->courses[$index]["start_date"] = $course->start_date;
					$item->courses[$index]["students"] = count($attendanceregister["students"])-1;
					$item->courses[$index]["teacher"] = $attendanceregister["teacher"]["name"];
					foreach($attendanceregister["students"] as $student)
					{
						if((int)$student["id"] === (int)$item->id)
						{
							$item->courses[$index]["request_attendances"] = count($student["attendances"]);
							$item->courses[$index]["absences"] = 0;
							foreach($student["attendances"] as $value)
								if((int)$value === 0)
									$item->courses[$index]["absences"]++;
								
							$item->courses[$index]["real_attendances"] = $item->courses[$index]["request_attendances"] - $item->courses[$index]["absences"];
							break;
						}
					}
				}
				
			}
		}
		
		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cooltouraman.student', 'student', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_cooltouraman.edit.student.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Override JModelAdmin::preprocessForm to ensure the correct plugin group is loaded.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function save($data)
	{
		$date = JFactory::getDate();
		jimport('joomla.user.helper');
		
		if (empty($data["register_date"]))
		{
			$data["register_date"] = $date->toSql();
		}
		
		$input = JFactory::getApplication()->input;
		$data['user_id'] = $input->getInt('user_id');
		$currentMail = $input->getInt('current_mail');
		
		// Make a new joomla account (only for J30)
		if ($data['user_id'] <= 0)
		{
			$user = clone(JFactory::getUser());
			$authorize = JFactory::getACL();
			

			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams( 'com_users' );
			$jconfig = JFactory::getConfig();
			
			$newUsertype = 2;
			
			$password = JUserHelper::genRandomPassword();
			$password2 = $password;
			
			$dataUser = array(
			 'name' => $data["firstname"],
			 'username' => $data["email"],
			 'email' => $data["email"],
			 'password' => $password,
			 'password2' => $password2,
			);
			
			$locale = $jconfig->get('language');
			$dataUser['params']=array('site_language'=>$locale,'language'=>$locale);

			if (!$user->bind( $dataUser, 'usertype' )) {
				JError::raiseError( '', $user->getError());
				return false;
			}

			$user->set('id', 0);
			$user->set('usertype', $newUsertype);
			$user->groups = array($newUsertype=>$newUsertype);
		    $user->set('registerDate', $date->toSql());
			$user->set('activation', '0');
			$user->set('sendEmail', '0');
			$user->set('lastvisitDate', '0000-00-00 00:00:00');
			$user->set('lastResetTime', '0000-00-00 00:00:00');
			$user->set('resetCount', '0');
			$user->set('requireReset', '0');
		

			if (intval($data['site_access']) === 0)
				$user->set('block', '1');
			else
				$user->set('block', '0');

			
			if ( !$user->save() ){
				JError::raiseWarning('', JText::_( $user->getError()));
				return false;
			}

			$data['user_id']=$user->id;
			//Devo aggiungere attivazione newsletter
		}
		else
		{
			//Devo aggiungere gestione newsletter
			$user = JFactory::getUser($data['user_id']);
			
			if (intval($data['site_access']) === 0)
				$user->set('block', '1');
			else
				$user->set('block', '0');
			
			if ($currentMail !== $data['email'])
			{
				$user->set('username', $data['email']);
				$user->set('email', $data['email']);
			}
			
			
			if ( !$user->save() )
			{
				JError::raiseWarning('', JText::_( $user->getError()));
				return false;
			}
		}
		
		if (parent::save($data))
			return true;

		return false;
	}
	
	/**
	 * Method to block the site access for the student records.
	 *
	 * @param   array    &$pks   The ids of the items to publish.
	 * @param   integer  $value  The value of the published state
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function block(&$pks, $value = 1)
	{
		$user = JFactory::getUser();

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');
		$table         = $this->getTable();
		$pks           = (array) $pks;
		
		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($value == 1 && $pk == $user->get('id'))
			{
				// Cannot block yourself.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('COM_COOLTOURAMAN_ERROR_CANNOT_BLOCK_SELF'));
			}
			elseif ($table->load($pk))
			{
				$old   = $table->getProperties();
				$allow = $user->authorise('core.edit.state', 'com_cooltouraman');
				
				// Don't allow non-super-admin to block a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if ($allow)
				{
					// Skip changing of same state
					if ($table->site_access == $value)
					{
						unset($pks[$i]);
						continue;
					}

					$table->site_access = (int) $value;

					// Allow an exception to be thrown.
					try
					{
						if (!$table->check())
						{
							$this->setError($table->getError());

							return false;
						}
						

						// Store the table.
						if (!$table->store())
						{
							$this->setError($table->getError());

							return false;
						}
						
						if ($table->user_id > 0 )
						{
							$user = JFactory::getUser($table->user_id);
							
							if (intval($table->site_access) === 0)
								$user->set('block', '1');
							else
								$user->set('block', '0');
							
							if ( !$user->save() )
							{
								JError::raiseWarning('', JText::_( $user->getError()));
								return false;
							}
							
						}
					}
					catch (Exception $e)
					{
						$this->setError($e->getMessage());
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		return true;
	}

	/**
	 * Method to delete rows.
	 *
	 * @param   array  &$pks  An array of item ids.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 */
	public function delete(&$pks)
	{
		$user  = JFactory::getUser();
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Check if I am a Super Admin
		$iAmSuperAdmin = $user->authorise('core.admin');

		if (in_array($user->id, $pks))
		{
			$this->setError(JText::_('COM_COOLTOURAMAN_ERROR_CANNOT_DELETE_SELF'));

			return false;
		}

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.delete', 'com_cooltouraman');

				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if ($allow)
				{

					if (!$table->delete($pk))
					{
						$this->setError($table->getError());

						return false;
					}
					else
					{
						//Devo cancellare i corsi
						if ($table->user_id > 0 )
						{
							$user = JFactory::getUser($table->user_id);
							if ( !$user->delete() )
							{
								JError::raiseWarning('', JText::_( $user->getError()));
								return false;
							}
						}
						
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}
}
