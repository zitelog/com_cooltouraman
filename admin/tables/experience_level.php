<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

/**
 * Experience_level Table class.
 *
 */
class CooltouramanTableExperience_level extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__cooltouraman_experience_level', 'id', $db);
	}
}
