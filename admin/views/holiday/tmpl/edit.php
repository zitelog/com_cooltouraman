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
		if (task == 'holiday.cancel' || document.formvalidator.isValid(document.getElementById('holiday-form')))
		{
			Joomla.submitform(task, document.getElementById('holiday-form'));
		}
	};
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="holiday-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'holiday')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'holiday', JText::_('COM_COOLTOURAMAN_TEACHER_INFORMATION', true)); ?>
			<div class="span12">
				<?php foreach ($this->form->getFieldset('holiday') as $field) : ?>
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
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>