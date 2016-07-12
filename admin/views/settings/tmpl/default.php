<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;
?>
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<fieldset>
<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'main_settings')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'main_settings', JText::_('COM_COOLTOURAMAN_MAIN_SETTINGS', true)); ?>
	<div class="row-fluid">
		<div class="span9">
			<a href="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=holidays');?>" class="list-group-item">
			<span class="icon-list"></span>
			<?php echo JText::_('COM_COOLTOURAMAN_HOLIDAYS'); ?>
			</a>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span9">
			<a href="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=certificates');?>" class="list-group-item">
			<span class="icon-book"></span>
			<?php echo JText::_('COM_COOLTOURAMAN_CERTIFICATES'); ?>
			</a>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span9">
			<a href="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=experience_levels');?>" class="list-group-item">
			<span class="icon-user"></span>
			<?php echo JText::_('COM_COOLTOURAMAN_FIELD_EXPERIENCE_LEVEL_LABEL'); ?>
			</a>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'attended_courses', JText::_('COM_COOLTOURAMAN_PDF_TEMPLATES', true)); ?>
	<div class="row-fluid">
		<div class="span12">
			<fieldset class="adminform">
				<?php echo 'nessun corso associato'; ?>
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</fieldset>
</div>