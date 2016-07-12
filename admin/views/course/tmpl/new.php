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


JFactory::getDocument()->addScriptDeclaration('
	
	var courseState =0;
	
	Joomla.submitbutton = function(task)
	{
		if (task == "course.cancel" || 
		   (document.weeklyfrequency.isValid(document.getElementById("weeklyfrequency-table")) &&
			document.formvalidator.isValid(document.getElementById("new-form")))
			)
		{
			Joomla.submitform(task, document.getElementById("new-form"));
		}
	};
	
	var strings = {
			"COM_COOLTOURAMAN_START_TIME_HOUR_AND_MINUTES_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_START_TIME_HOUR_AND_MINUTES_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_END_TIME_HOUR_AND_MINUTES_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_END_TIME_HOUR_AND_MINUTES_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_START_TIME_GT_END_TIME_FORMAT_ERROR":"'.JText::_('COM_COOLTOURAMAN_START_TIME_GT_END_TIME_FORMAT_ERROR', true).'",
			"COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR":"'.JText::_('COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR', true).'",
			"COM_COOLTOURAMAN_SELECT_WEEKLY_ERROR":"'.JText::_('COM_COOLTOURAMAN_LESSON_DURATION_TO_SHORT_ERROR', true).'"
		};
	 (function() {
      Joomla.JText.load(strings);
    })();
');


JFactory::getDocument()->addScript(JURI::root( true ) .'/media/system/js/mootools-core.js');
//START: jQuery twitter bootstrap wizard plugin http://github.com/VinceG/twitter-bootstrap-wizard
JFactory::getDocument()->addScript(JURI::root( true ).'/media/com_cooltouraman/js/jquery.bootstrap.wizard.min.js');
JFactory::getDocument()->addScript(JURI::root( true ) .'/media/com_cooltouraman/js/prettify.js');
JFactory::getDocument()->addStyleSheet(JURI::root( true ) .'/media/com_cooltouraman/css/prettify.css');
//END: jQuery twitter bootstrap wizard plugin http://github.com/VinceG/twitter-bootstrap-wizard
JFactory::getDocument()->addScript(JURI::root( true ) .'/media/com_cooltouraman/js/course.min.js');
JFactory::getDocument()->addStyleSheet(JURI::root( true ) .'/media/com_cooltouraman/css/course.min.css');

?>
<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="new-form" class="form-validate">
<div class='container'>
	<section id="wizard">
		<div id="rootwizard">
			<ul>
				<li>
					<a href="#tab1" data-toggle="tab">
						<h2><span class="number">1.</span>&nbsp;<?php echo JText::_('COM_COOLTOURAMAN_SELECT_TEMPLATE', true) ?></h2>
					</a>
				</li>
				<li>
					<a href="#tab2" data-toggle="tab">
						<h2><span class="number">2.</span>&nbsp;<?php echo JText::_('COM_COOLTOURAMAN_SELECT_TEACHER', true) ?></h2>
					</a>
				</li>
				<li>
					<a href="#tab3" data-toggle="tab">
						<h2><span class="number">3.</span>&nbsp;<?php echo JText::_('COM_COOLTOURAMAN_SELECT_START_DATE', true) ?></h2>
					</a>
				</li>
				<li>
					<a href="#tab4" data-toggle="tab">
						<h2><span class="number">4.</span>&nbsp;<?php echo JText::_('COM_COOLTOURAMAN_SELECT_DAY_AND_HOUR', true) ?></h2>
					</a>
				</li>
			</ul>
			<div id="bar" class="progress progress-striped active">
				<div class="bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;"></div>
			</div>
			<ul class="pager wizard">
				<li class="previous"><a href="#">Previous</a></li>
				<li class="next"><a href="#">Next</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane row-fluid" id="tab1">
					<div class="span6 course-message">
						<p>
						<?php echo JText::_('COM_COOLTOURAMAN_TEMPLATE_MSG', true) ?>
						</p>
					</div>
					<div class="span6 course-fields">
					<?php echo $this->form->renderField('template_id'); ?>
					</div>
				</div>
				<div class="tab-pane" id="tab2">
					<div class="span6 course-message">
						<p>
						<?php echo JText::_('COM_COOLTOURAMAN_TEACHER_MSG', true) ?>
						</p>
					</div>					
					<div class="span6 course-fields">
					<?php echo $this->form->renderField('teacher_id'); ?>
					</div>
				</div>
				<div class="tab-pane" id="tab3">
					<div class="span6 course-message">
						<p>
						<?php echo JText::_('COM_COOLTOURAMAN_START_DATE_MSG', true) ?>
						</p>
					</div>					
					<div class="span6 course-fields">
					<?php echo $this->form->renderField('start_date'); ?>
					</div>
				</div>
				<div class="tab-pane" id="tab4">
					<div class="span6 course-message">
						<p>
						<?php echo JText::_('COM_COOLTOURAMAN_WEEKLY_FREQUENCY_MSG', true) ?>
						</p>
					</div>					
					<div class="span6 course-fields">
					<?php echo $this->form->renderField('weeklyfrequency'); ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>