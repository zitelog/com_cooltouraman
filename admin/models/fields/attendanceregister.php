<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
 
// import the list field type
jimport('joomla.form.helper');

 
/**
 * Course Form Field class for the Cooltoura Manager component
 */
class JFormFieldAttendanceregister extends JFormField 
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Attendanceregister';
 
	public function getInput() 
	{	
		$maxColumnsEachPage = 14;
		$count = 0;
		$index = 1;
		$switch = 0;
		$theads =  array();
		$tbodys = array();
		$teacherRows = array();
		$daysRow = '';
		$monthRow = '';
		$tmpMonth = '';
		$tmpMonthDays = 0;
		
		//This is the hidden row that has cloned for each new student added to a course
		$templatePage = '';
		
		
		$students = count($this->value['students']); 
		$pages = ceil(count($this->value['students'][0]['attendances'])/$maxColumnsEachPage);
		
		//Prepare a thead for each "page"
		foreach($this->value['months_and_days'] as $month => $days)
		{
			$daysForMonth = count($days);
			
			if ($daysForMonth > 0)
			{
				if ($switch === 0)
				{
					$tmpDaysRow = $daysRow;
					$monthRow = '<tr class="month-row-'.$index.'">'."\n\r";
					$monthRow .= '<th colspan="2"></th>'."\n\r";
					$daysRow  = '<tr class="days-row-'.$index.'">'."\n\r";
					$daysRow .= '<th width="15%">'.JText::_('COM_COOLTOURAMAN_STUDENT', true).'/'.JText::_('COM_COOLTOURAMAN_TEACHER', true).'</th>'."\n\r";
					$daysRow .= '<th></th>'."\n\r";	
					$switch = 1;
				}
		
				if (!empty($tmpMonth) && $tmpMonthDays > 0)
				{
					$monthRow .= '<th colspan="'.$tmpMonthDays.'">'.$tmpMonth.'</th>'."\n\r";
					$daysRow .= $tmpDaysRow;
					$tmpMonth = '';
					$tmpMonthDays = 0;
				}
			
				if (($count + $daysForMonth) > $maxColumnsEachPage)
				{
					$tmpMonthDays = ($count + $daysForMonth) - $maxColumnsEachPage;
					$daysForMonth = $maxColumnsEachPage - $count;
				}
				if ($daysForMonth > 0)
					$monthRow .= '<th colspan="'.$daysForMonth.'">'.JText::_($month, true).'</th>'."\n\r";
			
				foreach ($days as $i => $day)
				{
					$date = new DateTime($day);
					$htmlDate = JText::_($date->format("D")).' '.$date->format("d");
					
					if ($count >= $maxColumnsEachPage)
					{
						$monthRow .= '</tr>'."\n\r";
						$daysRow  .= '</tr>'."\n\r";
						$theads[$index] = new stdClass();
						$theads[$index]->html = '<thead>'.$monthRow.$daysRow.'</thead>'."\n\r";
						$theads[$index]->columns = $count;
						$daysRow = '<th>'.$htmlDate.'</th>'."\n\r";
						$daysRow .= '<input type="hidden" name="jform[attendanceregister][months_and_days]['.$month.']['.$i.']" value="'.$day.'">'."\n\r";
						$tmpMonth = JText::_($month, true);	
						$count = 1;
						$switch = 0;
						$index++;
					}
					else
					{
						$daysRow .= '<th>'.$htmlDate.'</th>'."\n\r";
						$daysRow .= '<input type="hidden" name="jform[attendanceregister][months_and_days]['.$month.']['.$i.']" value="'.$day.'">'."\n\r";
						
						$count++;
						$daysForMonth--;
					}
					
				}
			}
		}
		
		if ($count > 1 && !isset($theads[$index]))
		{
			if ($switch === 0 && $tmpMonth !== '' && $tmpMonthDays > 0)
			{
				$tmpDaysRow = $daysRow;
				$monthRow = '<tr class="month-row-'.$index.'">'."\n\r";
				$monthRow .= '<th colspan="2"></th>'."\n\r";
				$monthRow .= '<th colspan="'.$tmpMonthDays.'">'.$tmpMonth.'</th>'."\n\r";
				$daysRow  = '<tr class="days-row-'.$index.'">'."\n\r";
				$daysRow .= '<th width="15%">'.JText::_('COM_COOLTOURAMAN_STUDENT', true).'/'.JText::_('COM_COOLTOURAMAN_TEACHER', true).'</th>'."\n\r";
				$daysRow .= '<th></th>'."\n\r";	
				$daysRow .= $tmpDaysRow;
			}
			
			$monthRow .= '</tr>'."\n\r";
			$daysRow  .= '</tr>'."\n\r";
			$theads[$index] = new stdClass();
			$theads[$index]->html = '<thead>'.$monthRow.$daysRow.'</thead>'."\n\r";
			$theads[$index]->columns = $count;
		}
		
		//Prepare a attendance for each "page" and for a teacher 
		if (isset($this->value['teacher']))
		{			
			$index = 1;
			$count = 1;
			$item = 0;
			$oldTeacherRow = '';
			
			$teacherRow = $this->_teacherFirstPartRow($this->value['teacher']['id'], $this->value['teacher']['name']);
			
			$items = count($this->value['teacher']['attendances']);
			foreach($this->value['teacher']['attendances'] as $i => $attendance)
			{
				if ($count > $maxColumnsEachPage || ($item + 1) === $items)
				{
					if(($item + 1) === $items)
					{
						$teacherRow .= $this->_teacherSecondPartRow($i, $attendance['attendance'], 
								$this->value['teacher']['attendances'][$i]['substitute'], 
								$this->value['teacher']['attendances'][$i]['lessonid']);
					}
					
					$teacherRow .= '</tr>'."\n\r";
					if (isset($teacherRows[$index]) && !empty($teacherRows[$index]))
						$oldTeacherRow = $teacherRows[$index]; 
					$teacherRows[$index] = $oldTeacherRow.$teacherRow;
					
					if(($item + 1) !== $items)
					{
						$teacherRow = $this->_teacherFirstPartRow($this->value['teacher']['id'], $this->value['teacher']['name']);
						$teacherRow .= $this->_teacherSecondPartRow($i, $attendance['attendance'], 
								$this->value['teacher']['attendances'][$i]['substitute'], 
								$this->value['teacher']['attendances'][$i]['lessonid']);
					}
					
					$count = 1;
					$index++;
				}
				else
				{
					$teacherRow .= $this->_teacherSecondPartRow($i, $attendance['attendance'], 
								$this->value['teacher']['attendances'][$i]['substitute'], 
								$this->value['teacher']['attendances'][$i]['lessonid']);
				}
				
				$count++;
				$item++;
			}
		}
		
		JFormHelper::loadFieldClass('radio');
		$field = new JFormFieldRadio();
		//Prepare a attendance for each "page" and for each student 
		foreach($this->value['students'] as $k => $student)
		{
			$index = 1;
			$count = 1;
			$switch = 0;
			$studentRow = '';
			$oldStudentRow = '';
			$item = 0;
			$style = '';
			
			if ($k === 0)
				$style = 'style="display:none"'; 
			
			if (isset($student['attendances']))
			{
				$items = count($student['attendances']);
				
				foreach ($student['attendances'] as $i => $attendance)
				{	
					$field->setup(new SimpleXMLElement('<field name="jform[attendanceregister][students]['.$k.'][attendances]['.$i.']" type="radio" size="1" default="0" class="btn-group btn-group-yesno"><option class="btn-small" value="0">A</option><option class="btn-small" value="1">P</option></field>'), $attendance);
					
					if ($switch === 0)
					{		
						$studentRow = '<tr id="studentId_'.$student['id'].'" class="row'.$k.'" '.$style.'>'."\n\r";
					    $studentRow .= '<td><a target="_blank" href="index.php?option=com_cooltouraman&view=student&layout=edit&id='.$student['id'].'">'.$student['name'].'</a></td>'."\n\r";
						$studentRow .= '<input type="hidden" name="jform[attendanceregister][students]['.$k.'][name]" value="'.$student['name'].'">'."\n\r";
						$studentRow .= '<input type="hidden" name="jform[attendanceregister][students]['.$k.'][id]" value="'.$student['id'].'">'."\n\r";
						$studentRow .= '<td id="trashBnt"><a class="btn btn-small" href="javascript:void(0);" onclick="return deleteRow(this, '.$student['id'].');"><span class="icon-trash"></span></a></td>'."\n\r";
						$switch = 1;
					}

					if ($count > $maxColumnsEachPage || ($item + 1) === $items)
					{
						
						if(($item + 1) === $items)
						{
							$studentRow .= '<td>'."\n\r";
							$studentRow .= $field->renderField(array('hiddenLabel'=>true));
							$studentRow .= '</td>'."\n\r";
						}
						
						$studentRow .= '</tr>'."\n\r";
						if (isset($tbodys[$index]) && !empty($tbodys[$index]))
							$oldStudentRow = $tbodys[$index]; 
						$tbodys[$index] = $oldStudentRow.$studentRow;
						
						if(($item + 1) !== $items)
						{
							$studentRow = '<tr id="studentId_'.$student['id'].'" class="row'.$k.'" '.$style.'>'."\n\r";
							$studentRow .= '<td><a target="_blank" href="index.php?option=com_cooltouraman&view=student&layout=edit&id='.$student['id'].'">'.$student['name'].'</a></td>'."\n\r";
							$studentRow .= '<td id="trashBnt"><a class="btn btn-small" href="javascript:void(0);" onclick="return deleteRow(this, '.$student['id'].');"><span class="icon-trash"></span></a></td>'."\n\r";
							$studentRow .= '<td>'."\n\r";
							$studentRow .= $field->renderField(array('hiddenLabel'=>true));
							$studentRow .= '</td>'."\n\r";
						}
						
						$count = 1;
						$index++;
					}
					else
					{
						$studentRow .= '<td>'."\n\r";
						$studentRow .= $field->renderField(array('hiddenLabel'=>true));
						$studentRow .= '</td>'."\n\r";
					}
					
					$count++;
					$item++;
					
				}
			}
		}
	
	
		$html = '';
		for ($i = 1; $i <= $pages; $i++)
		{
			$style = '';
			$current = '';
			$tableWidth = '';
			if ($i === 1)
			{
				$style = 'style="margin-top: 10px;"';
				$current = 'current';
			}
			if($theads[$i]->columns === 2)
				$tableWidth = 'style="width: 30%; ';
			
			$html .= '<div id="page-'.$i.'" class="row-fluid page '.$current.'" style="margin-top: 10px;">'."\n\r";
			$html .= '<div class="span12">'."\n\r";
			$html .= '<table '.$tableWidth.'id="attendance_register_table_'.$i.'" class="table table-bordered table-hover table-condensed">'."\n\r";
			$html .= $theads[$i]->html;
			$html .= '<tbody>'.$teacherRows[$i].$tbodys[$i].'</tbody>';
			$html .= '</table>'."\n\r";
			$html .= '</div>'."\n\r";
			$html .= '</div>'."\n\r";
		}
		
		return $html;
	}
	
	private function _teacherFirstPartRow($id, $name) 
	{
		$html = '';
		
		$html = '<tr id="teacherId_'.$id.'">'."\n\r";
		$html .= '<td><a target="_blank" href="index.php?option=com_cooltouraman&view=teacher&layout=edit&id='.$id.'">'.$this->value['teacher']['name'].'</a></td>'."\n\r";
		$html .= '<input type="hidden" name="jform[attendanceregister][teacher][name]" value="'.$name.'">'."\n\r";
		$html .= '<input type="hidden" name="jform[attendanceregister][teacher][id]" value="'.$id.'">'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<table>'."\n\r";
		$html .= '<tbody>'."\n\r";
		$html .= '<tr>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_ATTENDANCE', true).'</th>';
		$html .= '</tr>'."\n\r";
		$html .= '<tr>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_SUBSTITUTE_TEACHING', true).'</th>';
		$html .= '</tr>'."\n\r";
		$html .= '<tr>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_LESSON', true).'</th>';
		$html .= '</tr>'."\n\r";
		$html .= '</tbody>'."\n\r";
		$html .= '</table>'."\n\r";
		$html .= '</td>'."\n\r";
		
		return $html;
	}
	
	private function _teacherSecondPartRow($i, $attendance, $substitute, $lessonid) 
	{
		$options[] = JHTML::_('select.option',0, 'A');
		$options[] = JHTML::_('select.option', 1, 'P');
		$options[] = JHTML::_('select.option', 2, 'S');
		
		$html = '';
		
		$html .= '<td id="jformattendanceregisterteacherattendances'.$i.'-td" '.(!$attendance ? 'style="background-color: red";' : '').'>'."\n\r";
		$html .= '<table>'."\n\r";
		$html .= '<tbody>'."\n\r";
		//presence row
		$html .= '<tr>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= JHTML::_('select.genericlist', $options, 'jform[attendanceregister][teacher][attendances]['.$i.'][attendance] ', 'style="width:100%;" label', 'value', 'text', $attendance);
		$html .= '</td>'."\n\r";
		$html .= '</tr>'."\n\r";
		//Substitute row
		$html .= '<tr>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<span id="jformattendanceregisterteacherattendances'.$i.'-lbl" class="label label-info">'.$substitute.'</span>';
		$html .= '<input type="hidden" id="jformattendanceregisterteacherattendances'.$i.'-substitute" name="jform[attendanceregister][teacher][attendances]['.$i.'][substitute]" value="'.$substitute.'">'."\n\r";
		$html .= '</td>'."\n\r";
		$html .= '</tr>'."\n\r";
		//Program row
		$html .= '<tr>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<a href="#">'.JText::_('COM_COOLTOURAMAN_LESSON', true).'</a>';
		$html .= '<input type="hidden" name="jform[attendanceregister][teacher][attendances]['.$i.'][lessonid]" value="'.$lessonid.'">'."\n\r";
		$html .= '</td>'."\n\r";
		$html .= '</tr>'."\n\r";
		$html .= '</tbody>'."\n\r";
		$html .= '</table>'."\n\r";
		$html .= '</td>'."\n\r";
		
		return $html;
	}
}
