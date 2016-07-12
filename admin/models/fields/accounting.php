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
class JFormFieldAccounting extends JFormField 
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Accounting';
 
	public function getInput() 
	{
		$count = 0;
		$row = 0;
		$paymentsRows = 0;
		
		$options[] = JHTML::_('select.option',0, JText::_('COM_COOLTOURAMAN_SUBSCRIPTION_TYPE_ANNUAL'));
		$options[] = JHTML::_('select.option', 1, JText::_('COM_COOLTOURAMAN_SUBSCRIPTION_TYPE_MONTHLY'));

		$html = '';		
		$html .= '<table id="accounting_table" class="table table-bordered table-hover table-condensed">'."\n\r";
		$html .= '<thead>'."\n\r";
		$html .= '<tr>'."\n\r";
		$html .= '<th width="15%">'.JText::_('COM_COOLTOURAMAN_STUDENT').'</th>'."\n\r";
		$html .= '<th width="5%">'.JText::_('COM_COOLTOURAMAN_REGISTRATION').'</th>'."\n\r";
		$html .= '<th width="5%">'.JText::_('COM_COOLTOURAMAN_EARLY_END').'</th>'."\n\r";
		$html .= '<th width="5%">'.JText::_('COM_COOLTOURAMAN_SUBSCRIPTION').'</th>'."\n\r";
		$html .= '<th width="5%">'.JText::_('COM_COOLTOURAMAN_PRICE').'</th>'."\n\r";
		$html .= '<th width="5%">'.JText::_('COM_COOLTOURAMAN_PRICE_DISCOUNT').'</th>'."\n\r";
		$html .= '<th width="15%">'.JText::_('COM_COOLTOURAMAN_NOTE').'</th>'."\n\r";
		$html .= '<th width="43%">'.JText::_('COM_COOLTOURAMAN_PAYMENTS').'</th>'."\n\r";
		$html .= '<th width="1%"></th>'."\n\r";
		$html .= '<th width="1%"></th>'."\n\r";
		$html .= '</tr>'."\n\r";
		$html .= '</thead>'."\n\r";
		$html .= '<tbody>'."\n\r";
		
		if (!empty($this->value))
		{
			foreach($this->value['students'] as $key => $item)
			{
				$hidden = '';
				if ($key === 'student29061793')
					$hidden = ' style="display:none"';
				$style = ' style="color: green;"';
				if ((int)$item['balance'] > 0)
					$style = ' style="color: red;"';
				
				$html .= '<tr id="accounting_studentId_'.$item['id'].'"'.$hidden.' class="row'.$count.'">'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<span class="student_name">'.$item['name'].'</span>'."\n\r";
				$html .= '<input type="hidden" name="jform[accounting][students][student'.$item['id'].'][id]" value="'.$item['id'].'">'."\n\r";
				$html .= '<input type="hidden" name="jform[accounting][students][student'.$item['id'].'][name]" value="'.$item['name'].'">'."\n\r";
				$html .= '</td>'."\n\r";
				$html .= '<td>';
				$html .= JHTML::calendar($item['registration'],'jform[accounting][students][student'.$item['id'].'][registration]', 'jform_accounting_students_student'.$item['id'].'_registration', '%d-%m-%Y',array('size'=>'8','maxlength'=>'10','class'=>' input-small'));
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= JHTML::calendar($item['earlyend'],'jform[accounting][students][student'.$item['id'].'][earlyend]', 'jform_accounting_students_student'.$item['id'].'_earlyend', '%d-%m-%Y',array('size'=>'8','maxlength'=>'10','class'=>' input-small'));
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= JHTML::_('select.genericlist', $options, 'jform[accounting][students][student'.$item['id'].'][subscription]', 'style="width:100%;" label', 'value', 'text', $item['subscription']);
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<input type="text" name="jform[accounting][students][student'.$item['id'].'][price]" id="jform_accounting_students_student'.$item['id'].'_price" value="'.$item['price'].'" class="input-micro">';
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<input type="text" name="jform[accounting][students][student'.$item['id'].'][discount]" id="jform_accounting_students_student'.$item['id'].'_discount" onchange="calcBalanceValue('.$item['id'].')" value="'.$item['discount'].'" class="input-micro">';
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<textarea name="jform[accounting][students][student'.$item['id'].'][note]" id="jform_accounting_students_student'.$item['id'].'_note" cols="30" rows="1">'.$item['note'].'</textarea>';
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<table style="margin-bottom: 0;" id="accounting_payment_table_student_id_'.$item['id'].'" class="table table-striped table-hover">';
				$html .= '<tbody id="accounting_payment_tbody_student_id_'.$item['id'].'">';
				if (isset($item['payments']) && !empty($item['payments']))
				{
					foreach($item['payments'] as $k => $payment)
					{
						$html .= '<tr class="row'.$row.'" id="payment_'.$item['id'].'_'.$paymentsRows.'">';
						$html .= '<td>'."\n\r";
						$html .= JHTML::calendar($payment['date'],'jform[accounting][students][student'.$item['id'].'][payments][payment'.$paymentsRows.'][date]', 'jform_accounting_students_student'.$item['id'].'_payments_payment'.$paymentsRows.'_date', '%d-%m-%Y',array('size'=>'8','maxlength'=>'10','class'=>' input-small'));
						$html .= '</td>'."\n\r";
						$html .= '<td>'."\n\r";
						$html .= '<input type="text" value="'.$payment['amount'].'" name="jform[accounting][students][student'.$item['id'].'][payments][payment'.$paymentsRows.'][amount]" id="jform_accounting_students_student'.$item['id'].'_payments_payment'.$paymentsRows.'_amount" onchange="calcBalanceValue('.$item['id'].')" class="input-micro payment_amount'.$item['id'].'">'."\n\r";
						$html .= '</td>'."\n\r";
						$html .= '<td>'."\n\r";
						$html .= '<a class="btn btn-micro" href="javascript:void(0);" onclick="return deletePaymentRow(this, '.$item['id'].');"><span class="icon-trash"></span></a>'."\n\r";
						$html .= '</td>'."\n\r";
						$html .= '</tr>';
						
						$paymentsRows++;
						
						if ($paymentsRows%2 !== 0) 
							$row = 1;
						else
							$row = 0;
					}
				}
				$html .= '</tbody>';
				$html .= '</table>'."\n\r";
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<a class="btn btn-micro" href="javascript:void(0);" onclick="return addPaymentRow('.$item['id'].');"><span class="icon-plus"></span>';
				$html .= '</td>'."\n\r";
				$html .= '<td>'."\n\r";
				$html .= '<span '.$style.'class="student_balance'.$item['id'].'">'.$item['balance'].'</span>'."\n\r";
				$html .= '<input type="hidden" name="jform[accounting][students][student'.$item['id'].'][balance]" value="'.$item['balance'].'">'."\n\r";
				$html .= '</td>'."\n\r";
				$html .= '</tr>'."\n\r";
				
				$count++;
				
			}
		}
		else
		{
			$html .= $this->_firstRow();
		}
		
		$html .= '</tbody>'."\n\r";
		$html .= '</table>'."\n\r";
		return $html;
	}
	
	private function _firstRow() 
	{
		$options[] = JHTML::_('select.option',0, JText::_('COM_COOLTOURAMAN_SUBSCRIPTION_TYPE_ANNUAL'));
		$options[] = JHTML::_('select.option', 1, JText::_('COM_COOLTOURAMAN_SUBSCRIPTION_TYPE_MONTHLY'));
		
		$html = '';
		$html .= '<tr id="accounting_studentId_29061793" class="row29061793" style="display:none">'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<span class="student_name">%name%</span>'."\n\r";
		$html .= '<input type="hidden" name="jform[accounting][students][student29061793][id]" value="29061793">'."\n\r";
		$html .= '<input type="hidden" name="jform[accounting][students][student29061793][name]" value="%name%">'."\n\r";
		$html .= '</td>'."\n\r";
		$html .= '<td>';
		$html .= JHTML::calendar(date("d-m-Y"),'jform[accounting][students][student29061793][registration]', 'jform_accounting_students_student29061793_registration', '%d-%m-%Y',array('size'=>'8','maxlength'=>'10','class'=>' input-small', 'required'));
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= JHTML::calendar('','jform[accounting][students][student29061793][earlyend]', 'jform_accounting_students_student29061793_earlyend', '%d-%m-%Y',array('size'=>'8','maxlength'=>'10','class'=>' input-small'));
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= JHTML::_('select.genericlist', $options, 'jform[accounting][students][student29061793][subscription]', 'style="width:100%;" label', 'value', 'text', 0);
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<input type="text" name="jform[accounting][students][student29061793][price]" id="jform_accounting_students_student29061793_price" value="%price%" class="input-micro">';
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<input type="text" name="jform[accounting][students][student29061793][discount]" id="jform_accounting_students_student29061793_discount" onchange="calcBalanceValue(29061793)" value="0" class="input-micro">';
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<textarea name="jform[accounting][students][student29061793][note]" id="jform_accounting_students_student29061793_note" cols="30" rows="1"></textarea>';
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<table style="margin-bottom: 0;" id="accounting_payment_table_student_id_29061793" class="table table-striped table-hover">';
		$html .= '<tbody id="accounting_payment_tbody_student_id_29061793">';
		$html .= '</tbody>';
		$html .= '</table>'."\n\r";
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<a class="btn btn-micro" href="javascript:void(0);" onclick="return addPaymentRow(29061793);"><span class="icon-plus"></span>';
		$html .= '</td>'."\n\r";
		$html .= '<td>'."\n\r";
		$html .= '<span class="student_balance29061793">%balance%</span>'."\n\r";
		$html .= '<input type="hidden" name="jform[accounting][students][student29061793][balance]" value="%balance%">'."\n\r";
		$html .= '</td>'."\n\r";
		$html .= '</tr>'."\n\r";
		
		return $html;
	}
}
