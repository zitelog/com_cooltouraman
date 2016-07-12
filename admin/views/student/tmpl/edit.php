<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addStyleDeclaration("
	.hidden_field_parental{display:none;}
");
JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'student.cancel' || document.formvalidator.isValid(document.getElementById('student-form')))
		{
			Joomla.submitform(task, document.getElementById('student-form'));
		}
	};
	
	jQuery(document).ready(function() 
	{
		var oldMail = jQuery('input[name=\'jform[email]\']').val();
		jQuery('input[name=\'jform[email]\']').change(function() 
		{
			if (oldMail != '')
				window.alert('ATTENZIONE: Ricordati che la modifica della mail comporta la modifica anche della login di accesso!');
		});
		jQuery('input[name=\'jform[underage]\']').change(function() 
		{
			if(jQuery(this).val() == 1)
			{
				jQuery('.hidden_field_parental').each(function()
				{
					var label = jQuery(this).find('.control-label>label');
					
					if(label.length > 0)
					{
						label.addClass('required');
						label.append('<span class=\"star\">&nbsp;*</span>');
					}
					
					var elem = jQuery(this).find('div.controls').children().first();
					if(label.length > 0)
						elem.addClass('required');
					jQuery(this).show();
				});
			}
			else
			{
				jQuery('.hidden_field_parental').each(function()
				{
					jQuery(this).find('.required').each(function(){jQuery(this).removeClass('required')});
					jQuery(this).find('.star').remove();
					jQuery(this).hide();
				});
			}
		});
	});
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="student-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>
	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_COOLTOURAMAN_STUDENT_INFORMATION', true)); ?>
				<?php foreach ($this->form->getFieldset('student_information') as $field) : ?>
					<?php 
						$class='';
						if ($field->fieldname == 'parental_authority' || 
							  $field->fieldname == 'parental_firstname' ||
							  $field->fieldname == 'parental_lastname')
							  {
								  $class=' hidden_field_parental';
							  }  
					?>
					<div class="control-group <?php echo $class; ?>">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attended_courses', JText::_('COM_COOLTOURAMAN_ATTENDED_COURSES', true)); ?>
			<div class="row-fluid">
				<div class="span12">
					<fieldset class="adminform">
					<div class="container">
						<table class="table table-striped" id="courseList">
							<thead>
								<tr>
									<th><?php echo JText::_('JGLOBAL_TITLE', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_ATTENDANCES', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_FIELD_DURATION_IN_HOURS_LABEL', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_TEACHER', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_STUDENTS', true); ?></th>
									<th><?php echo JText::_('COM_COOLTOURAMAN_SELECT_START_DATE', true); ?></th>
									<th><?php echo JText::_('COM_COOLTOURAMAN_SELECT_END_DATE', true); ?></th>
									<th><?php echo JText::_('JGRID_HEADING_ID', true); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach($this->item->courses as $course): ?>
							<tr>
								<td>
								<a href="<?php echo JRoute::_('index.php?option=com_cooltouraman&task=course.edit&id=' . $course["course_id"]); ?>">
								<?php echo $course["title"]; ?>
								</a>
								</td>
								<td class="center">
								<?php echo $course["real_attendances"].'/'.$course["request_attendances"]; ?>
								</td>
								<td class="center">
								<?php echo $course["duration_in_hours"]; ?>
								</td>
								<td class="center">
								<?php echo $course["teacher"]; ?>
								</td>
								<td class="center">
								<?php echo $course["students"]; ?>
								</td>
								<td>
								<?php echo $course["start_date"]; ?>
								</td>
								<td>
								<?php echo $course["start_date"]; ?>
								</td>
								<td>
								<?php echo $course["course_id"]?>
								</td>
							</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					</fieldset>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</fieldset>

	<input type="hidden" name="user_id" value="<?php echo $this->item->user_id; ?>" />
	<input type="hidden" name="current_mail" value="<?php echo $this->item->email; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>