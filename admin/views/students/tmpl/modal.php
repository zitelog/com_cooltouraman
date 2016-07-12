<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$input     = JFactory::getApplication()->input;
$field     = $input->getCmd('field');
$function  = 'jSelectStudent_' . $field;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=students&layout=modal&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_USERS_SEARCH_IN_NAME'); ?>" data-placement="bottom"/>
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
				<button type="button" class="btn" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('', '<?php echo JText::_('JLIB_FORM_SELECT_USER'); ?>');"><?php echo JText::_('JOPTION_NO_USER'); ?></button>
			</div>
		</div>
	</fieldset>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th class="left">
						<?php echo JHtml::_('grid.sort', 'COM_COOLTOURAMAN_STUDENT', 'a.lastname', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="25%">
						<?php echo JHtml::_('grid.sort', 'COM_COOLTOURAMAN_STUDENT_FIELD_BIRTHDAY_DATE_LABEL', 'a.birthday_date', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap" width="25%">
						<?php echo JText::_('JGLOBAL_EMAIL'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				$i = 0;

				foreach ($this->items as $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<?php $student = $this->escape($item->firstname . ' ' .$item->lastname); ?>
						<td>
							<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $student; ?>');">
								<?php echo $student;?>
							</a>
						</td>
						<td align="center">
							<?php echo JHtml::_('date', $item->birthday_date, 'd-m-Y'); ?>
						</td>
						<td align="left">
							<?php echo JStringPunycode::emailToUTF8($this->escape($item->email)); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
