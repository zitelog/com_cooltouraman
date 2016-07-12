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
 * Methods supporting a list of teacher records.
 *
 */
class CooltouramanModelTeachers extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'firstname', 'a.firstname',
				'lastname', 'a.lastname',
				'email', 'a.email',
				'birthday_date', 'a.birthday_date',
				'street', 'a.street',
				'site_access', 'a.site_access',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout', 'default', 'cmd'))
		{
			$this->context .= '.' . $layout;
		}

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$site_access = $this->getUserStateFromRequest($this->context . '.filter.site_access', 'filter_site_access');
		$this->setState('filter.site_access', $site_access);

		$range = $this->getUserStateFromRequest($this->context . '.filter.range', 'filter_range');
		$this->setState('filter.range', $range);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_cooltouraman');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.lastname', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.range');

		return parent::getStoreId($id);
	}

	/**
	 * Gets the list of users and adds expensive joins to the result set.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		foreach ($items as $item)
		{
			// Convert the skills field to an array.
			$registry = new Registry;
			$registry->loadString($item->skills);
			$item->skills = $registry->toArray();
		}
		
		
		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*, COUNT(b.course_id) AS assignment_courses_number'
			)
		);

		$query->from($db->quoteName('#__cooltouraman_teacher') . ' AS a');
		
		// Join over the assign courses
		$query->join('LEFT', $db->quoteName('#__cooltouraman_course_teacher_relations') . ' AS b ON b.teacher_id = a.id');
			
		$query->group($db->quoteName('a.id'));

		// If the model is set to check item site_access, add to the query.
		$site_access = $this->getState('filter.site_access');

		if (is_numeric($site_access))
		{
			$query->where('a.site_access = ' . (int) $site_access);
		}

		// Filter the items over the search string if set.
		if ($this->getState('filter.search') !== '' && $this->getState('filter.search') !== null)
		{
			// Escape the search token.
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($this->getState('filter.search')), true) . '%'));

			// Compile the different search clauses.
			$searches   = array();
			$searches[] = 'a.firstname LIKE ' . $search;
			$searches[] = 'a.lastname LIKE ' . $search;
			$searches[] = 'a.email LIKE ' . $search;

			// Add the clauses to the query.
			$query->where('(' . implode(' OR ', $searches) . ')');
		}

		// Add filter for registration ranges select list
		$range = $this->getState('filter.range');

		// Apply the range filter.
		if ($range)
		{
			// Get UTC for now.
			$dNow   = new JDate;
			$dStart = clone $dNow;

			switch ($range)
			{
				case 'past_week':
					$dStart->modify('-7 day');
					break;

				case 'past_1month':
					$dStart->modify('-1 month');
					break;

				case 'past_3month':
					$dStart->modify('-3 month');
					break;

				case 'past_6month':
					$dStart->modify('-6 month');
					break;

				case 'post_year':
				case 'past_year':
					$dStart->modify('-1 year');
					break;

				case 'today':
					// Ranges that need to align with local 'days' need special treatment.
					$app    = JFactory::getApplication();
					$offset = $app->get('offset');

					// Reset the start time to be the beginning of today, local time.
					$dStart = new JDate('now', $offset);
					$dStart->setTime(0, 0, 0);

					// Now change the timezone back to UTC.
					$tz = new DateTimeZone('GMT');
					$dStart->setTimezone($tz);
					break;
			}

			if ($range == 'post_year')
			{
				$query->where(
					$db->qn('a.register_date') . ' < ' . $db->quote($dStart->format('Y-m-d H:i:s'))
				);
			}
			else
			{
				$query->where(
					$db->qn('a.register_date') . ' >= ' . $db->quote($dStart->format('Y-m-d H:i:s')) .
					' AND ' . $db->qn('a.register_date') . ' <= ' . $db->quote($dNow->format('Y-m-d H:i:s'))
				);
			}
		}

		// Add the list ordering clause.
		$query->order($db->qn($db->escape($this->getState('list.ordering', 'a.lastname'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
		return $query;
	}
}
