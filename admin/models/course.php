<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die('Restricted access');


use Joomla\Registry\Registry;

/**
 * Item Model for a Course.
 *
 */
class CooltouramanModelCourse extends JModelAdmin
{
	/**
	 * @var string  The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_COOLTOURAMAN';

	/**
	 * The type alias for this content type (for example, 'com_cooltouraman.course').
	 *
	 * @var      string
	 */
	public $typeAlias = 'com_cooltouraman.course';

	/**
	 * The context used for the associations table
	 *
	 * @var      string
	 */
	protected $associationsContext = 'com_cooltouraman.item';

	/**
	 * Batch copy items to a new category or current.
	 *
	 * @param   integer  $value     The new category.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 */
	protected function batchCopy($value, $pks, $contexts)
	{
		$categoryId = (int) $value;

		$newIds = array();

		if (!parent::checkCategoryId($categoryId))
		{
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$this->table->reset();

			// Check that the row actually exists
			if (!$this->table->load($pk))
			{
				if ($error = $this->table->getError())
				{
					// Fatal error
					$this->setError($error);

					return false;
				}
				else
				{
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$data = $this->generateNewTitle($categoryId, $this->table->alias, $this->table->title);
			$this->table->title = $data['0'];
			$this->table->alias = $data['1'];

			// Reset the ID because we are making a copy
			$this->table->id = 0;

			// Reset hits because we are making a copy
			$this->table->hits = 0;

			// Unpublish because we are making a copy
			$this->table->state = 0;

			// New category ID
			$this->table->catid = $categoryId;

			// TODO: Deal with ordering?
			// $table->ordering = 1;

			// Get the featured state
			$featured = $this->table->featured;

			// Check the row.
			if (!$this->table->check())
			{
				$this->setError($this->table->getError());
				return false;
			}

			parent::createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);

			// Store the row.
			if (!$this->table->store())
			{
				$this->setError($this->table->getError());
				return false;
			}

			// Get the new item ID
			$newId = $this->table->get('id');

			// Add the new ID to the array
			$newIds[$pk] = $newId;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return false;
			}
			
			$user = JFactory::getUser();
			
			return $user->authorise('core.delete', 'com_cooltouraman.course.' . (int) $record->id);
		}

		return false;
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
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		//delete all relations student;
		$conditions = array(
			$db->quoteName('course_id') . ' IN ('.implode(',',$pks).')'
		);
		$query->delete($db->quoteName('#__cooltouraman_course_student_relations'));
		$query->where($conditions);
		$db->setQuery($query);
		
		if(!$db->execute())
		{
			$this->setError($db->getError());
			return false;
		}
		
		$query->clear();
		//delete all relations teacher;
		$query->delete($db->quoteName('#__cooltouraman_course_teacher_relations'));
		$query->where($conditions);
		$db->setQuery($query);
		
		if(!$db->execute())
		{
			$this->setError($db->getError());
			return false;
		}
		
		parent::delete($pks);
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing article.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', 'com_cooltouraman.course.' . (int) $record->id);
		}
		// New article, so check against the category.
		elseif (!empty($record->catid))
		{
			return $user->authorise('core.edit.state', 'com_cooltouraman.category.' . (int) $record->catid);
		}
		// Default to component settings if neither article nor category known.
		else
		{
			return parent::canEditState('com_cooltouraman');
		}
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		// Set the publish date to now
		$db = $this->getDbo();

		if ($table->state == 1 && (int) $table->publish_up == 0)
		{
			$table->publish_up = JFactory::getDate()->toSql();
		}

		if ($table->state == 1 && intval($table->publish_down) == 0)
		{
			$table->publish_down = $db->getNullDate();
		}

		// Increment the content version number.
		$table->version++;

		// Reorder the articles within the category so the new article is first
		if (empty($table->id))
		{
			$table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
		}
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable    A database object
	 */
	public function getTable($type = 'Course', $prefix = 'CooltouramanTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
		
			// Merge global params with item params
		   $params = clone $this->getState('params');
		   $params->merge($item->params);
		   $item->params = $params;
			
			// Convert the info field to an array.
			$registry = new Registry;
			$registry->loadString($item->info);
			$item->info = $registry->toArray();
			
			// Convert the prices field to an array.
			$registry = new Registry;
			$registry->loadString($item->prices);
			$item->prices = $registry->toArray();
			
			// Convert the weeklyfrequency field to an array.
			$registry = new Registry;
			$registry->loadString($item->weeklyfrequency);
			$item->weeklyfrequency = $registry->toArray();
			
			// Convert the calendaroflessons field to an array.
			$registry = new Registry;
			$registry->loadString($item->calendaroflessons);
			$item->calendaroflessons = $registry->toArray();
			
			// Convert the attendanceregister field to an array.
			$registry = new Registry;
			$registry->loadString($item->attendanceregister);
			$item->attendanceregister = $registry->toArray();
			
			// Convert the accounting field to an array.
			$registry = new Registry;
			$registry->loadString($item->accounting);
			$item->accounting = $registry->toArray();
			
			//Compare and align calendaroflessons and attendanceregister
			foreach($item->calendaroflessons as $month => $daysForMonth)
			{
				$attendanceRegisterDays = null;
				
				if (isset($item->attendanceregister['months_and_days'][$month]))
					$attendanceRegisterDays = $item->attendanceregister['months_and_days'][$month];
					
				foreach($daysForMonth["days"] as $key => $day)
				{
					if (!empty($attendanceRegisterDays))
					{
						
						if(isset($day["checked"]) && !in_array($key, $attendanceRegisterDays))
						{
			
							//Add days to attendance register
							$item->attendanceregister['months_and_days'][$month][] = $key;
							$item->attendanceregister['teacher']['attendances'][$key]['attendance'] = 1;
							$item->attendanceregister['teacher']['attendances'][$key]['substitute'] = 'N/A';
							$item->attendanceregister['teacher']['attendances'][$key]['lessonid'] = 1;
							foreach($item->attendanceregister['students'] as $i => $student)
							{								
								if (!isset($student['attendances'][$key]))
								{
									$item->attendanceregister['students'][$i]['attendances'][$key] = 1;
									uksort($item->attendanceregister['students'][$i]['attendances'], array($this, "date_compare"));
								}
							}
							
							uksort($item->attendanceregister['teacher']['attendances'], array($this, "date_compare"));
						}
						else if(!isset($day["checked"]) && in_array($key, $attendanceRegisterDays))
						{
							$index = array_search($key, $attendanceRegisterDays);
							
							//Remove the day from attendance register only if the teacher is present or substitute
							if ((int)$item->attendanceregister['teacher']['attendances'][$key]['attendance'] > 0)
							{
								unset($item->attendanceregister['months_and_days'][$month][$index]);
								unset($item->attendanceregister['teacher']['attendances'][$key]);
								foreach ($item->attendanceregister['students'] as $i => $student)
								{
									if (isset($student['attendances'][$key]))
									{
										unset($item->attendanceregister['students'][$i]['attendances'][$key]);
										uksort($item->attendanceregister['students'][$i]['attendances'], array($this, "date_compare"));
									}
								}
								uksort($item->attendanceregister['teacher']['attendances'], array($this, "date_compare"));
							}

						}
						
						
						sort($item->attendanceregister['months_and_days'][$month]);
					}
					else
					{
						if (isset($day["checked"]))
						{
							$item->attendanceregister['months_and_days'][$month] = array();
							$item->attendanceregister['months_and_days'][$month][] = $key;
							$item->attendanceregister['teacher']['attendances'][$key]['attendance'] = 1;
							$item->attendanceregister['teacher']['attendances'][$key]['substitute'] = 'N/A';
							$item->attendanceregister['teacher']['attendances'][$key]['lessonid'] = 1;
							foreach($item->attendanceregister['students'] as $i => $student)
							{
								if (!isset($student['attendances'][$key]))
								{
									$item->attendanceregister['students'][$i]['attendances'][$key] = 1;
									uksort($item->attendanceregister['students'][$i]['attendances'], array($this, "date_compare"));
								}
							}
							
							uksort($item->attendanceregister['teacher']['attendances'], array($this, "date_compare"));
						}	
						if (isset($item->attendanceregister['months_and_days'][$month]))
							sort($item->attendanceregister['months_and_days'][$month]);
					}
				}
			}
			
			if (isset($item->attendanceregister['teacher']))
			{
				//Compare and align global teacher_id whit attendance register teacher_id and teacher name
				if ((int)$item->attendanceregister['teacher']['id'] !== (int)$item->teacher_id)
				{
					$item->attendanceregister['teacher']['id']  = $item->teacher_id;
					$item->attendanceregister['teacher']['name']  = $this->getTeacherName($item->teacher_id);
				}
			}
			
			$item->course_description_text = trim($item->course_description_text);
			$item->course_teaching_program_text = trim($item->course_teaching_program_text);
			$item->course_certificate_text = trim($item->course_certificate_text);
		}

		return $item;
	}
							

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cooltouraman.course', 'course', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$jinput = JFactory::getApplication()->input;

		// The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
		if ($jinput->get('a_id'))
		{
			$id = $jinput->get('a_id', 0);
		}
		// The back end uses id so we use that the rest of the time and set it to 0 by default.
		else
		{
			$id = $jinput->get('id', 0);
		}
		// Determine correct permissions to check.
		if ($this->getState('course.id'))
		{
			$id = $this->getState('course.id');

			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');

			// Existing record. Can only edit own articles in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		$user = JFactory::getUser();

		// Check for existing article.
		// Modify the form based on Edit State access controls.
		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_cooltouraman.course.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_cooltouraman')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
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
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_cooltouraman.edit.course.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
			if ($this->getState('course.id') == 0)
			{
				$filters = (array) $app->getUserState('com_cooltouraman.course.filter');
				$data->set(
					'state',
					$app->input->getInt(
						'state',
						((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
					)
				);
				$data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
				$data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
				$data->set('access', $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access'))));
			}
		}

		// If there are params fieldsets in the form it will fail with a registry object
		if (isset($data->params) && $data->params instanceof Registry)
		{
			$data->params = $data->params->toArray();
		}

		$this->preprocessData('com_cooltouraman.course', $data);

		return $data;
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
		$input = JFactory::getApplication()->input;
		$filter  = JFilterInput::getInstance();
		
		$db = JFactory::getDbo();
	    $query = $db->getQuery(true);
		
		$calendarDays = $this->getCalendarDays($data['calendaroflessons']);
		$checkedDays = $calendarDays['checkedays'];
		$unCheckedDays = $calendarDays['uncheckedays'];
		
		//Save the end date in sql format
		uksort($checkedDays, array($this, "date_compare"));
		$data['end_date'] = date("Y-m-d",strtotime(key( array_slice( $checkedDays, -1, 1, TRUE ))));
		
		//Actual hours for week Convert in seconds
		$hoursForWeek = strtotime('1970-01-01 '.$data['hours_for_week'].' UTC');
		//Actual days for week
		$daysForWeek = $data['days_for_week'];
		
		//Total duration we can't change during the course 
		$duration = $data["duration_in_hours"] * 60 * 60;//Convert duration from hours to seconds

		//Re-Calculate hours for week and days for week
		$totalDaysAndHoursForWeek = $this->getTotalDaysAndHoursForWeek($data["weeklyfrequency"]);
		//We check this for brief course like one or two days
		if ($totalDaysAndHoursForWeek['totalHoursForWeek'] > $duration)
		{
			$this->setError('COM_COOLTOURAMAN_HOURS_GT_TO_DURATION_ERROR');
			return false;
		}
		
		//Save the calculate hours for week
		$data['hours_for_week'] = $totalDaysAndHoursForWeek['totalHoursForWeek'];
		//Save the calculate days for week
		$data['days_for_week'] = implode(',', array_keys($totalDaysAndHoursForWeek['totalDaysForWeek']));
	
		//Rebuild the new calendar array; WARNING: (Itsn't possible when the course started) 		
		if (($data["start_date"] !== $input->get('start_date_beforetosave')) ||
			((int)$data["duration_in_hours"] !== (int)$input->get('duration_in_hours_beforetosave'))
		   )
		{
			$data['calendaroflessons'] = $this->createCalendarOfLessons($data["weeklyfrequency"], $data["duration_in_hours"], $data["start_date"]);
		}
		else if ($hoursForWeek !== $data['hours_for_week'] || $daysForWeek !== $data['days_for_week'])
		{
			
			$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
			$now = JFactory::getDate('now', $timeZone);
			$currentTime = $now->toUnix();
			$timesStamp = $this->stringToDate(array_keys($checkedDays));
			$firstCourseDay = min($timesStamp);
			$lastCourseDay = max($timesStamp);
			$closestTimestamp = $this->closestNumber($timesStamp, $currentTime); //find closest date
			$closestDate = array_search($closestTimestamp, $timesStamp);
			$spentDuration = 0;
			$calendarToSave = $this->getCalendarPart($data['calendaroflessons'], $closestDate, $spentDuration);
			$remainingTime = 0;
			if ($duration > $spentDuration)
				$remainingTime = ($duration - $spentDuration)/60/60;
			
			if ($remainingTime > 0)
			{	
				//Course has yet to start  make a new calendar
				if ($firstCourseDay > $currentTime)
					$data['calendaroflessons'] = $this->createCalendarOfLessons($data["weeklyfrequency"], $data["duration_in_hours"], $data["start_date"]);
				else if ($currentTime >= $firstCourseDay && $currentTime <= $lastCourseDay) //merge old calendar part whit new one 
					$data['calendaroflessons'] = $this->createCalendarOfLessons($data["weeklyfrequency"], $remainingTime, $now->format('d-m-Y', true), $calendarToSave);
			}
			else
			{
				if ($firstCourseDay > $currentTime)
					$data['calendaroflessons'] = $this->createCalendarOfLessons($data["weeklyfrequency"], $data["duration_in_hours"], $data["start_date"]);
				else
					$data['calendaroflessons'] = $calendarToSave;
			}
		}
		else
		{
			if ($calendarDays['total_checked_duration'] != $duration)
			{
				$msg = '';
				if ($calendarDays['total_checked_duration'] > $duration)
					$msg = JText::_('COM_COOLTOURAMAN_DAYS_GT_TO_DURATION_ERROR', true);
				elseif ($calendarDays['total_checked_duration'] < $duration)
					$msg = JText::_('COM_COOLTOURAMAN_DAYS_LT_TO_DURATION_ERROR', true);
					
				
				$this->setError($msg);
				
				return false;
			}
		}
		
		//Convert hours for week in human readble
		$data['hours_for_week'] = gmdate("H:i", $data['hours_for_week']); 

		if (isset($data['created_by_alias']))
		{
			$data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
		}
		
		if ($data["teacher_id"] !== $input->get('teacher_id_beforetosave'))
		{	
			//Delete actual relate from teacher to the course
			$conditions = array(
				$db->quoteName('course_id') . ' = '.$input->getInt('id'), 
				$db->quoteName('teacher_id') . ' = ' . $input->getInt('teacher_id_beforetosave')
			);
			$query->delete($db->quoteName('#__cooltouraman_course_teacher_relations'));
			$query->where($conditions);
			 
			echo $query->__toString();
			$db->setQuery($query);
			 
			if(!$db->execute())
			{
				$this->setError($db->getError());
				return false;
			}
			
			//Add new relate from teacher to the course
			$columns = array('course_id', 'teacher_id');
			$values = array($input->getInt('id'), (int)$data["teacher_id"]);
			$query
				->insert($db->quoteName('#__cooltouraman_course_teacher_relations'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
				
			$db->setQuery($query);
			if(!$db->execute())
			{
				$this->setError($db->getError());
				return false;
			}
		}
		
		$query->clear();
		//Join course to the students; First delete all relations;
		$conditions = array(
			$db->quoteName('course_id') . ' = '.$input->getInt('id')
		);
		$query->delete($db->quoteName('#__cooltouraman_course_student_relations'));
		$query->where($conditions);
		$db->setQuery($query);
		
		if(!$db->execute())
		{
			$this->setError($db->getError());
			return false;
		}

		$query->clear();
		// Second relate each student to the course
		$values = array();
		foreach($data['attendanceregister']['students'] as $i => $student)
			if (is_numeric($student['id']))
				$values[] = $input->getInt('id').','.(int)$student['id'];
		if (!empty($values))
		{
			$columns = array('course_id', 'student_id');
			$query
				->insert($db->quoteName('#__cooltouraman_course_student_relations'))
				->columns($db->quoteName($columns))
				->values($values);
				
			$db->setQuery($query);
			if(!$db->execute())
			{
				$this->setError($db->getError());
				return false;
			}
		}

		// Alter the title for save as copy
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['catid'], 'copy', $data['title']);
				$data['title'] = $title;
			}
			
			$data['state'] = 0;

		}
		
		if (parent::save($data))
			return true;

		return false;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 *
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   JForm   $form   The form object
	 * @param   array   $data   The data to be merged into the form object
	 * @param   string  $group  The plugin group to be executed
	 *
	 * @return  void
	 *
	 * @since    3.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Association content items
		$app = JFactory::getApplication();
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$languages = JLanguageHelper::getLanguages('lang_code');
			$addform = new SimpleXMLElement('<form />');
			$fields = $addform->addChild('fields');
			$fields->addAttribute('name', 'associations');
			$fieldset = $fields->addChild('fieldset');
			$fieldset->addAttribute('name', 'item_associations');
			$fieldset->addAttribute('description', 'COM_CONTENT_ITEM_ASSOCIATIONS_FIELDSET_DESC');
			$add = false;

			foreach ($languages as $tag => $language)
			{
				if (empty($data->language) || $tag != $data->language)
				{
					$add = true;
					$field = $fieldset->addChild('field');
					$field->addAttribute('name', $tag);
					$field->addAttribute('type', 'modal_article');
					$field->addAttribute('language', $tag);
					$field->addAttribute('label', $language->title);
					$field->addAttribute('translate_label', 'false');
					$field->addAttribute('edit', 'true');
					$field->addAttribute('clear', 'true');
				}
			}
			if ($add)
			{
				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}
	
	/**
	 * Method to create new course from template copy.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  new course  id on success.
	 *
	 */
	public function createFromTmpl($data)
	{
		$templateId = $data->getInt('template_id', 0);
		$teacherId = $data->getInt('teacher_id', 0);
		$startDate = $data->get('start_date');
		$weeklyFrequency = $data->get('weeklyfrequency', '', 'array');
		
		if (isset($startDate))
		{
			$date = JFactory::getDate($startDate);
			$startDate = $date->toSql();
		}
		
		if (isset($weeklyFrequency) && is_array($weeklyFrequency))
		{
			$registry = new Registry;
			$registry->loadArray($weeklyFrequency);
			$weeklyFrequencyStr = (string) $registry;
		}
		
		$courseTable = $this->getTable();
		$templateTable = JTable::getInstance('Template', 'CooltouramanTable');
		
		if ($templateTable->load($templateId))
		{
			$courseTable->template_id = $templateId;
			$courseTable->teacher_id = $teacherId;
			$courseTable->start_date = $startDate;
			$courseTable->weeklyfrequency = $weeklyFrequencyStr;
			$courseTable->title = JText::_('COM_COOLTOURAMAN_COPY_TEMPLATE_TITLE').$templateTable->title;
			$courseTable->code = $templateTable->code;
			$courseTable->catid = $templateTable->catid;
			$courseTable->language = $templateTable->language;
			$courseTable->course_description_text = $templateTable->course_description_text;
			$courseTable->course_certificate_text = $templateTable->course_certificate_text;
			$courseTable->course_teaching_program_text = $templateTable->course_teaching_program_text;
			$courseTable->prices = $templateTable->prices;
			$courseTable->vat = $templateTable->vat;
			$courseTable->info = $templateTable->info;
			$courseTable->duration_in_hours = $templateTable->duration_in_hours;
			$totalDaysAndHoursForWeek = array();
			$calendaroflessons = $this->createCalendarOfLessons($weeklyFrequency, $courseTable->duration_in_hours, $startDate, null,$totalDaysAndHoursForWeek);
			if (isset($totalDaysAndHoursForWeek['hours_for_week']))
				$courseTable->hours_for_week = gmdate("H:i", $totalDaysAndHoursForWeek['hours_for_week']); 	//Convert hours for week in human readble
			
			if (isset($totalDaysAndHoursForWeek['days_for_week']))
				$courseTable->days_for_week = $totalDaysAndHoursForWeek['days_for_week'];
		
			
			if (isset($calendaroflessons) && is_array($calendaroflessons))
			{
				$attendanceRegister = $this->createAttendanceRegister($calendaroflessons, $teacherId);
				
				if (!empty($attendanceRegister['students'][0]['attendances']))
				{
					//Save the end date in sql format
					$tmp = $attendanceRegister['teacher']["attendances"];
					uksort($tmp, array($this, "date_compare"));
					$courseTable->end_date = date("Y-m-d",strtotime(key( array_slice( $tmp, -1, 1, TRUE ))));
					
					$registry = new Registry;
					$registry->loadArray($attendanceRegister);
					$courseTable->attendanceregister = (string) $registry;
				}
				
				$registry = new Registry;
				$registry->loadArray($calendaroflessons);
				$courseTable->calendaroflessons = (string) $registry;
			}		
			
			if (!$courseTable->store())
			{
				$this->setError($courseTable->getError());
				return false;
			}
			
			//Join Teacher to the course
			$courseTeacherRelationsTable = JTable::getInstance('Course_teacher_relations', 'CooltouramanTable');
			$courseTeacherRelationsTable->teacher_id = $courseTable->teacher_id;
			$courseTeacherRelationsTable->course_id = $courseTable->id;
			if (!$courseTeacherRelationsTable->store())
			{
				$this->setError($courseTeacherRelationsTable->getError());
				return false;
			}
			
			return $courseTable->id;
		}
		else
		{
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
			return false;
		}
	}
	
	/**
	 * Method to create calendar lesson from the weekly frequency, course duration and start_date.
	 *
	 * @param   array  $weeklyFrequency day and start and finish time of lesson.
	 * @param   array  $duration total course duration in hours.
	 * @param   array  $startDate when the course start.
	 * 
	 * @return new calendarOfLessons array.
	 *
	 */
	public function createCalendarOfLessons($weeklyFrequency, $duration, $startDate, $calendarToSave = array(), &$data=null)
	{
		$duration = (int)$duration * 60 * 60;//Convert duration from hours to seconds

		$totalDaysForWeek = array();
		$calendarOfLessons = array();
		$holidays = $this->getHolidays();
		$lessons = 0;
		
		$totalDaysAndHoursForWeek = $this->getTotalDaysAndHoursForWeek($weeklyFrequency);
		
		
		if (!empty($calendarToSave))
			$calendarOfLessons = $calendarToSave;
			
		$data['hours_for_week'] = $totalDaysAndHoursForWeek['totalHoursForWeek'];
		$totalDaysForWeek = $totalDaysAndHoursForWeek['totalDaysForWeek'];
		$data['days_for_week'] = implode(',', array_keys($totalDaysAndHoursForWeek['totalDaysForWeek']));
		
		//Calculate start date and end date 
		$begin = new DateTime($startDate);
		$end = new DateTime($startDate);
		
		$end->modify('+52 weeks');
		$interval = new DateInterval('P1D');
		$daterange = new DatePeriod($begin, $interval ,$end);
		
		//Add at the end 10 hours to have some days "free"
		$extendDuration = (10 * 60 * 60) + $duration;//Convert duration from hours to seconds
		
		//Make calendar
		foreach($daterange as $date)
		{	
			$key = strtoupper($date->format("F"));
			
			if ($extendDuration > 0)
			{
				if (!isset($calendarOfLessons[$key]))
				{
					$calendarOfLessons[$key]['year'] = $date->format("Y");
					$calendarOfLessons[$key]['days'] = array();
				}

				if (isset($totalDaysForWeek['day_of_week_'.$date->format("w")])) 
				{
					$day = $date->format("d-m-Y");
					$calendarOfLessons[$key]['days'][$day] = new stdClass();
					$calendarOfLessons[$key]['days'][$day]->name = $date->format("D");
					$calendarOfLessons[$key]['days'][$day]->number = $date->format("d");
					$calendarOfLessons[$key]['days'][$day]->holiday = 0;
					$calendarOfLessons[$key]['days'][$day]->holidaytitle = '';
					$calendarOfLessons[$key]['days'][$day]->duration = $totalDaysForWeek['day_of_week_'.$date->format("w")];
					
					
					//Check holidays
					if (isset($holidays[$key][$day]))
					{
						$calendarOfLessons[$key]['days'][$day]->holiday = 1;
						$calendarOfLessons[$key]['days'][$day]->holidaytitle = $holidays[$key][$day]["title"];
					}
						
					
					if ($calendarOfLessons[$key]['days'][$day]->holiday === 0 && $duration > 0)
					{
						$calendarOfLessons[$key]['days'][$day]->checked = 1;
						
						$duration -= $totalDaysForWeek['day_of_week_'.$date->format("w")];
					}
					
					$extendDuration -= $totalDaysForWeek['day_of_week_'.$date->format("w")];
				}
			}
		}

		return $calendarOfLessons;
		
							
		
	}
	
	/**
	 * Method to create Attendance Register from $calendarOfLessons.
	 *
	 * @param   array  $calendarOfLessons day of lesson.
	 * 
	 * @return new attendanceRegister array.
	 *
	 */
	public function createAttendanceRegister($calendarOfLessons, $teacherId)
	{
		$attendanceRegister = array();
		$attendanceRegister['months_and_days'] = array();
		$attendanceRegister['teacher']['name'] = $this->getTeacherName($teacherId);
		$attendanceRegister['teacher']['id'] = $teacherId;
		$attendanceRegister['teacher']['attendances'] = array();
		$attendanceRegister['students'][0]['attendances'] = array();
		$attendanceRegister['students'][0]['name'] = '##';
		$attendanceRegister['students'][0]['id'] = '##';
		$attendanceRegister['students'][0]['attendances'] = array();
		foreach ($calendarOfLessons as $month => $daysForMonth)
		{
			if (!isset($attendanceRegister['months_and_days'][$month]))
				$attendanceRegister['months_and_days'][$month] = array();
			
			if (isset($daysForMonth["days"] ))
			{
				foreach ($daysForMonth["days"] as $i => $day)
				{
					if(isset($day->checked))
					{
						$attendanceRegister['months_and_days'][$month][] = $i;
						$attendanceRegister['students'][0]['attendances'][$i] = 1;
						$attendanceRegister['teacher']['attendances'][$i] = array();
						$attendanceRegister['teacher']['attendances'][$i]['attendance'] = 1;
						$attendanceRegister['teacher']['attendances'][$i]['substitute'] = 'N/A';
						$attendanceRegister['teacher']['attendances'][$i]['lessonid'] = 1;
					}	
				}	
			}
		}
		
		return $attendanceRegister;
	}
	
	/**
	 * Method to get getDaysAndHoursForWeek.
	 *
	 * @param  $weeklyFrequency array
	 *
	 * @return  getDaysAndHoursForWeek array.
	 *
	 */
	public function getTotalDaysAndHoursForWeek($weeklyFrequency)
    {
		$totalDaysAndHoursForWeek = array('totalHoursForWeek' => 0, 'totalDaysForWeek' => array());
		
		foreach($weeklyFrequency as $i => $day)
		{
			if(!empty($day['start_time']) && !empty($day['end_time']))
			{
				$totalDaysAndHoursForWeek['totalHoursForWeek'] += strtotime($day['end_time']) - strtotime($day['start_time']);
				$totalDaysAndHoursForWeek['totalDaysForWeek'][$i] = strtotime($day['end_time']) - strtotime($day['start_time']);
			}	
		}
		
		return $totalDaysAndHoursForWeek;
	}
	
	/**
	 * Method to get calendar checked days from user.
	 *
	 * @param $calendarOfLessons array.
	 *
	 * @return calendarDays array.
	 *
	 */
	public function getCalendarDays($calendarOfLessons)
    {
		$calendarDays = array('checkedays' =>array(), 'uncheckedays' =>array(), 'total_checked_duration' => 0);

		foreach ($calendarOfLessons as $daysForMonth)
		{
			if (isset($daysForMonth["days"] ))
			{
				foreach ($daysForMonth["days"] as $i => $day)
				{
					if(isset($day["checked"]))
					{
						$calendarDays['checkedays'][$i] = $day;
						$calendarDays['total_checked_duration'] += (int)$day['duration'];
					}
					else
					{
						$calendarDays['uncheckedays'][$i] = $day;
					}
				}
			}
		}		
					
		return $calendarDays;
	}
	
	/**
	 * Method to convert date string in timestamp.
	 *
	 * @param array date string.
	 *
	 * @return array timestamp.
	 *
	 */
	public function stringToDate($stringDates) 
	{
		$timesStamp = array();
		
		if (!is_array($stringDates))
			$stringDates = array($stringDates);
		
		$timeZone = new DateTimeZone(JFactory::getConfig()->get('offset'));

		foreach($stringDates as $stringDate)
			$timesStamp [$stringDate] = JDate::getInstance($stringDate, $timeZone)->toUnix();
		
		return $timesStamp;
	}
	
	
	
		/**
	 * Method to get calendar part.
	 *
	 * @param $calendarOfLessons array.
	 * @param $offset string to make a cut.
	 *
	 * @return $calendarPart array.
	 *
	 */
	public function getCalendarPart($calendarOfLessons, $offset, &$spentDuration = 0)
    {
		$resultcalendarPart = array();
		$findIt = false;
		
		foreach ($calendarOfLessons as $k => $daysForMonth)
		{
			if (isset($daysForMonth["days"] ))
			{
				$newDays = array();
				foreach ($daysForMonth["days"] as $i => $day)
				{
					$spentDuration += (int)$day['duration'];
					
					$newDays[$i] = $day;
					if($i === $offset)
					{
						$findIt = true;
						break;
					}
				}
			}
			
			if (!$findIt)
			{
				$calendarPart[$k] = $daysForMonth;
			}
			else
			{
				$calendarPart[$k] = $daysForMonth;
				$calendarPart[$k]["days"] = $newDays;
				break;
			}
		}
		
		return $calendarPart;
	}
	
		
	/**
	 * Method to get closest number from another.
	 *
	 * @param numbers array.
	 * @param number to match 
	 *
	 * @return closest match.
	 *
	 */
	public function closestNumber($numbers, $number) 
	{
		$closest = 0;
		
		if ($closest=array_search($number, $numbers)) return $closest;

		//find closest
		foreach ($numbers as $match) 
		{
			if ($number > $match)
			{
				$diff = abs($number - $match);
				if (!isset($closeness) || (isset($closeness) && $closeness > $diff))
				{
					$closeness = $diff;
					$closest = $match;
				}
			}
		}
		
		return $closest;
	}
	
	
	/**
	 * Method to get Holidays.
	 *
	 *
	 * @return  holidays object.
	 *
	 */
	public function getHolidays()
    {
		$db = $this->getDbo();
		$rHolidays = array();
		
		$query = $db->getQuery(true)
			->select('a.*')
			->from('#__cooltouraman_holiday AS a')
			->where('1');
			
		$db->setQuery($query);
		
		$holidays = $db->loadObjectlist();
		
		foreach($holidays as $holiday)
		{ 
			$thisYear = new DateTime();
			$nextYear = new DateTime();
			$nextYear->modify('+ 1 year');
			
			if ($holiday->year === '*')
					$holiday->year = $thisYear->format("Y");
			
			if ($holiday->year === $thisYear->format("Y") || $holiday->year === $nextYear->format("Y"))
			{
				
				$holidayDate = new DateTime($holiday->year.'-'.$holiday->month.'-'.$holiday->day);
				$monthName = strtoupper($holidayDate->format("F"));

				if(!isset($rHolidays[$monthName]))
					$rHolidays[$monthName] = array();
				
				$rHolidays[$monthName][$holidayDate->format("d-m-Y")]['day'] = $holidayDate->format("d");
				$rHolidays[$monthName][$holidayDate->format("d-m-Y")]['title'] = $holiday->title;
			}
		}
		
		return $rHolidays;
    }
	
	
	/**
	 * Method to get teacher name from id.
	 *
	 *
	 * @return  name string.
	 *
	 */
	public function getTeacherName($id)
    {
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('CONCAT(a.firstname, \' \', a.lastname) AS teachername')
			->from('#__cooltouraman_teacher AS a')
			->where('id ='. $id);
			
		$db->setQuery($query);
		
		return  $db->loadResult();
    }
	
	/**
	 * Method to compare date.
	 *
	 * @param   date format.
	 * @param   date format.
	 *
	 * @return  the difference form $t1 - $t2
	 *
	 */
	public function date_compare($a, $b)
	{
		$t1 = strtotime($a);
		$t2 = strtotime($b);
		return $t1 - $t2;
	}
}
