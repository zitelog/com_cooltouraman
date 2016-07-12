<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
 
// import the list field type
jimport('joomla.form.helper');

 
/**
 * Course Form Field class for the Cooltoura Manager component
 */
class JFormFieldWeeklyfrequency extends JFormField 
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Weeklyfrequency';
 
	public function getInput() 
	{
		//Days Version Numeric representation of the day of the week 0 (for Sunday) through 6 (for Saturday)
		$days = array(JText::_('SUNDAY'), JText::_('MONDAY'), JText::_('TUESDAY'), JText::_('WEDNESDAY'), JText::_('THURSDAY'), JText::_('FRIDAY'), JText::_('SATURDAY'));
		$html = '<table class="table" id="weeklyfrequency-table">'."\n\r";
		$html .= '<thead>'."\n\r";
		$html .= '<tr>'."\n\r";
		$html .= '<th>#</th>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_DAY', true).'</th>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_START_TIME', true).'</th>'."\n\r";
		$html .= '<th>'.JText::_('COM_COOLTOURAMAN_END_TIME', true).'</th>'."\n\r";
	    $html .= '</tr>'."\n\r";
		$html .= '</thead>'."\n\r";
		$html .= '<tbody>'."\n\r";
		foreach($days as $i => $oneDay)
		{
			$checked = '';
			$start_time = '';
			$end_time = '';
			if (!empty($this->value))
			{
				if (!empty($this->value['day_of_week_'.$i]["start_time"]))
				{
					$checked = ' checked';
					$start_time = ' value="'.$this->value['day_of_week_'.$i]["start_time"].'"';
					$end_time = ' value="'.$this->value['day_of_week_'.$i]["end_time"].'"';
				}
			}
			
			$html .= '<tr id="weeklyfrequency-row-'.$i.'">'."\n\r";
			$html .= '<td><input class="validate-day" type="checkbox" id="day_of_week_'.$i.'" name="jform[weeklyfrequency][day_of_week_'.$i.']" value="'.$oneDay.'"'.$checked.'></td>'."\n\r";
			$html .= '<td class="day-label">'.$oneDay.'</td>'."\n\r";
			$html .= '<td><input aria-invalid="false" class="input-small validate-time" type="text" name="jform[weeklyfrequency][day_of_week_'.$i.'][start_time]" id="start_time_'.$i.'"'.$start_time.' placeholder="HH:MM"></td>'."\n\r";
			$html .= '<td><input class="input-small validate-time" type="text" name="jform[weeklyfrequency][day_of_week_'.$i.'][end_time]" id="end_time_'.$i.'"'.$end_time.' placeholder="HH:MM"></td>'."\n\r";
			$html .= '</tr>'."\n\r";
		}
		$html .= '</tbody>'."\n\r";
		$html .= '</table>'."\n\r";
		
		return $html;
	}
}
