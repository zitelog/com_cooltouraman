<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Courses list controller class.
 */
class CooltouramanControllerCourses extends JControllerAdmin
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
		parent::__construct($config);

		// Articles default form can come from the articles or featured view.
		// Adjust the redirect view on the value of 'view' in the request.
		if ($this->input->get('view') == 'featured')
		{
			$this->view_list = 'featured';
		}

		$this->registerTask('unfeatured', 'featured');
		$this->text_prefix = 'COM_COOLTOURAMAN_COURSES';
	}

	/**
	 * Method to toggle the featured setting of a list of articles.
	 *
	 * @return  void
	 *
	 */
	public function featured()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user   = JFactory::getUser();
		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('featured' => 1, 'unfeatured' => 0);
		$task   = $this->getTask();
		$value  = JArrayHelper::getValue($values, $task, 0, 'int');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.edit.state', 'com_cooltouraman.course.' . (int) $id))
			{
				// Prune items that you can't change.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
		}

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Publish the items.
			if (!$model->featured($ids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}

			if ($value == 1)
			{
				$message = JText::plural('COM_COOLTOURAMAN_COURSES_N_ITEMS_FEATURED', count($ids));
			}
			else
			{
				$message = JText::plural('COM_COOLTOURAMAN_COURSES_N_ITEMS_UNFEATURED', count($ids));
			}
		}

		$view = $this->input->get('view', '');

		if ($view == 'featured')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_cooltouraman&view=featured', false), $message);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_cooltouraman&view=courses', false), $message);
		}
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 */
	public function getModel($name = 'Course', $prefix = 'CooltouramanModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   JModelLegacy  $model  The data model object.
	 * @param   integer       $ids    The array of ids for items being deleted.
	 *
	 * @return  void
	 *
	 */
	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}
}
