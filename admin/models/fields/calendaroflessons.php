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
class JFormFieldCalendaroflessons extends JFormField 
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Calendaroflessons';
 
	public function getInput() 
	{
		$items = $this->value;
		
		if (isset($this->value) && !is_array($this->value))
		{
			$registry = new Registry;
			$registry->loadString($this->value);
			$items = $registry->toArray();
		}
		
		$daysForWeek = 7;
		
		$html = '';		
		$html .= '<table class="table">'."\n\r";
		$html .= '<tbody>'."\n\r";
		foreach($items as $key => $item)
		{
			$html .= '<tr><th>'.JText::_($key). ' '.$item["year"].'</th></tr>'."\n\r";
			$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][year]" value="'.$item['year'].'">'."\n\r";
			$daysForMonth = count($item["days"]);
			$days = 0;
			
			foreach ($item["days"] as $i => $day)
			{
				if ($days === 0)
					$html .= '<tr>'."\n\r";
				$style = '';
				$checked = '';
				$tooltip = '';
				
				if((int)$day["holiday"])
				{
					$style = ' style="color: red;"';
					$tooltip = ' class="hasTooltip" data-original-title="'.$day['holidaytitle'].'"';
				}
				
				if(isset($day["checked"]))
					$checked = ' checked';
				
				$html .= '<td width="10%">'."\n\r";
				$html .= '<span'.$style.$tooltip.' id="jformcalendaroflessonsspanday'.$i.'">'.JText::_($day['name']).' ' .$day['number'].'</span>'."\n\r";
				$html .= '<span'.$style.' id="jformcalendaroflessonsspanduration'.$i.'">('.gmdate("H:i", $day['duration']).')</span>'."\n\r";
				$html .= '<input type="checkbox" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][checked]"'.$checked.'>'."\n\r";
				$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][name]" value="'.$day['name'].'">'."\n\r";
				$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][number]" value="'.$day['number'].'">'."\n\r";
				$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][holiday]" value="'.$day['holiday'].'">'."\n\r";
				$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][holidaytitle]" value="'.$day['holidaytitle'].'">'."\n\r";
				$html .= '<input type="hidden" name="jform[calendaroflessons]['.$key.'][days]['.$i.'][duration]" value="'.$day['duration'].'">'."\n\r";
				$html .= '</td>'."\n\r";
				
				$days++;
				
				if (($i+1) === $daysForMonth || $days === $daysForWeek)
				{
					$html .= '</tr>'."\n\r";
					$days = 0;
				}
			}
			$html .= '<tr class="lastrow"><td width="10%"></td><td width="10%"></td><td width="10%"></td><td width="10%"></td><td width="10%"></td><td width="10%"></td><td width="10%"></td></tr>'."\n\r";
		}
		
		$html .= '</tbody>'."\n\r";
		$html .= '</table>'."\n\r";
		return $html;
	}
}
