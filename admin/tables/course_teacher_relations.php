<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die('Restricted access');

class CooltouramanTableCourse_teacher_relations extends JTable
{

    public function __construct(&$db)
	{
		parent::__construct('#__cooltouraman_course_teacher_relations', 'teacherid', $db);
	}
}

?>