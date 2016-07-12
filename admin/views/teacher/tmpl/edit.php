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

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'teacher.cancel' || document.formvalidator.isValid(document.getElementById('teacher-form')))
		{
			Joomla.submitform(task, document.getElementById('teacher-form'));
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
	});
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="teacher-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<?php echo JLayoutHelper::render('joomla.edit.item_title', $this); ?>
	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_COOLTOURAMAN_TEACHER_INFORMATION', true)); ?>
			<div class="span6">
				<?php foreach ($this->form->getFieldset('teacher_information') as $field) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="span6">
			<?php foreach ($this->form->getGroup('skills') as $field) : ?>
				<?php echo $field->getControlGroup(); ?>
			<?php endforeach; ?>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'assignment_courses_number', JText::_('COM_COOLTOURAMAN_ASSIGNMENT_COURSES', true)); ?>
			<div class="row-fluid">
				<div class="span12">
					<div class="container">
						<table class="table table-striped">
							<thead>
								<tr>
									<th><?php echo JText::_('JGLOBAL_TITLE', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_ATTENDANCES', true); ?></th>
									<th class="center"><?php echo JText::_('COM_COOLTOURAMAN_FIELD_DURATION_IN_HOURS_LABEL', true); ?></th>
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
								<?php echo $course["students"]; ?>
								</td>
								<td>
								<?php echo $course["start_date"]; ?>
								</td>
								<td>
								<?php echo $course["end_date"]; ?>
								</td>
								<td>
								<?php echo $course["course_id"]?>
								</td>
							</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>
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