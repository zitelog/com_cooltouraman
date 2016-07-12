<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.modal'); 

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "course.cancel" || 
			(document.weeklyfrequency.isValid(document.getElementById("weeklyfrequency-table")) &&
			document.formvalidator.isValid(document.getElementById("course-form")))
			)
		{
			Joomla.submitform(task, document.getElementById("course-form"));
		}
	};
	//If state is equal 1; disabled durantion and start date
	var courseState ='.(int)$this->item->state.'
	
	var strings = {
			"COM_COOLTOURAMAN_START_TIME_HOUR_AND_MINUTES_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_START_TIME_HOUR_AND_MINUTES_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_END_TIME_HOUR_AND_MINUTES_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_END_TIME_HOUR_AND_MINUTES_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_START_TIME_GT_END_TIME_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_START_TIME_GT_END_TIME_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR":"'.JText::_('COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR', true).'",
			"COM_COOLTOURAMAN_TEACHER_ABSENT":"'.JText::_('COM_COOLTOURAMAN_TEACHER_ABSENT', true).'",
			"COM_COOLTOURAMAN_TEACHER_ABSENT_WARNING_MSG":"'.JText::_('COM_COOLTOURAMAN_TEACHER_ABSENT_WARNING_MSG', true).'",
			"COM_COOLTOURAMAN_SELECT_WEEKLY_ERROR":"'.JText::_('COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR', true).'"
		};
	 (function() {
      Joomla.JText.load(strings);
    })();
');

JFactory::getDocument()->addStyleSheet(JURI::root( true ) .'/media/com_cooltouraman/css/course.min.css');
JFactory::getDocument()->addScript(JURI::root( true ) .'/media/com_cooltouraman/js/course.min.js');
?>
<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="course-form" class="form-validate">
	<div class="form-inline form-inline-header">
		<?php
			$title = $this->form->getField('title') ? 'title' : ($this->form->getField('name') ? 'name' : '');
			echo $title ? $this->form->renderField($title) : '';
			echo $this->form->renderField('alias');
			echo $this->form->renderField('code');
		?>
	</div>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'calendar')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'calendar', JText::_('COM_COOLTOURAMAN_COURSE_FIELD_CALENDAR_OF_LESSONS_LABEL', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->renderField('calendaroflessons', null, null, array('class' => 'calendaroflessons')); ?>
				</fieldset>
			</div>
			<div class="span3 weeklyFrequency">
				<?php echo $this->form->renderField('start_date'); ?>
				<?php echo $this->form->renderField('duration_in_hours'); ?>
				<?php echo $this->form->renderField('weeklyfrequency'); ?>
				<?php echo $this->form->renderField('days_for_week'); ?>
				<?php echo $this->form->renderField('hours_for_week'); ?>
				<?php echo $this->form->renderField('end_date'); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attendance_register', JText::_('COM_COOLTOURAMAN_COURSE_FIELD_ATTENDANCE_REGISTER_LABEL', true)); ?>
		<div class="container-fluid wrapper">
			<div class="row-fluid">
				<div class="span12">
				<!-- Toolbar -->
					<div class="row-fluid"> 
						<div class="span4">
							<div class="input-append">
								<a class="btn btn-primary modal_jform_created_by" href="index.php?option=com_cooltouraman&amp;view=students&amp;layout=modal&amp;tmpl=component&amp;field=jform_student_name" rel="{handler: 'iframe', size: {x: 800, y: 500}}">
								<span class="icon-new"></span><?php echo JText::_('COM_COOLTOURAMAN_ADD_STUDENT', true); ?></a>
							</div>
						</div>
						<div class="span4 offset4 pull-right">
							<div class="btn-group pull-right">
							<a class="btn btn-info indicator disabled" href="javascript:void(0);" id="previous"><span class="icon-previous icon-white"></span></a>
							<a class="btn indicator btn-info" href="javascript:void(0);" id="next"><span class="icon-next icon-white"></span></a>
							</div>
						</div>
					</div>
					<!-- ./Toolbar -->
					<?php echo $this->form->renderField('attendanceregister', null, null, array('class' => 'attendanceregister')); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'accounting', JText::_('COM_COOLTOURAMAN_ACCOUNTING', true)); ?>
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span12">
				<?php echo $this->form->renderField('accounting', null, null, array('class' => 'accounting')); ?>
				</div>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_COOLTOURAMAN_COURSE_DESCRIPTION', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('course_description_text'); ?>
				</fieldset>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'program', JText::_('COM_COOLTOURAMAN_COURSE_TEACHING_PROGRAM', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('course_teaching_program_text'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'certificate', JText::_('COM_COOLTOURAMAN_COURSE_CERTIFICATE', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('course_certificate_text'); ?>
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'info', JText::_('COM_COOLTOURAMAN_INFO', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo $this->form->renderField('teacher_id'); ?>
				<?php foreach ($this->form->getGroup('info') as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
				<?php echo $this->form->renderField('template_id', null, null, array('class' => 'hidden')); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'prices', JText::_('COM_COOLTOURAMAN_PRICES', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php foreach ($this->form->getGroup('prices') as $field) : ?>
						<?php echo $field->getControlGroup(); ?>
					<?php endforeach; ?>
				</div>
				<div class="span6">
				<?php echo $this->form->renderField('vat'); ?>
				<?php echo $this->form->renderField('promotion_message'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<?php if ((int)$this->item->state === 1) : ?>
		<input type="hidden" name="jform[duration_in_hours]" value="<?php echo $this->item->duration_in_hours; ?>" />
		<?php endif; ?>
		<input type="hidden" name="start_date_beforetosave" value="<?php echo $this->item->start_date; ?>" />
		<input type="hidden" name="duration_in_hours_beforetosave" value="<?php echo $this->item->duration_in_hours; ?>" />
		<input type="hidden" name="teacher_id_beforetosave" value="<?php echo $this->item->teacher_id; ?>" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		<?php echo JHtml::_('form.token'); ?>
		</div>
</form>