<?php
/**
 * @module		com_cooltouraman
 * @author-name Fabio Zito
 * @copyright	Copyright (C) 2016 Fabio Zito
 * @license		GNU/GPL, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
?>
<form action="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=students');?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="userList">
				<thead>
					<tr>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_STUDENT', 'a.lastname', $listDirn, $listOrder); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_STUDENT_FIELD_BIRTHDAY_DATE_LABEL', 'a.birthday_date', $listDirn, $listOrder); ?>
						</th>
						<th width="18%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_PRIMARY_PHONE', 'a.primary_phone', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_PARENT_OR_GUARDIAN_AD_LITEM', 'a.parental_authority', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_ATTENDED_COURSES', 'a.attended_courses_number', $listDirn, $listOrder); ?>
						</th>
						<th width="5%" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_STUDENT_FIELD_SITE_ACCESS_LABEL', 'a.site_access', $listDirn, $listOrder); ?>
						</th>
						<th width="15%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_COOLTOURAMAN_STUDENT_FIELD_REGISTRATION_DATE_LABEL', 'a.register_date', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
				<?php foreach ($this->items as $i => $item) :
					$canEdit   = $this->canDo->get('core.edit');
					$canChange = $loggeduser->authorise('core.edit.state',	'com_cooltouraman');

					// If this group is super admin and this user is not super admin, $canEdit is false
					if ((!$loggeduser->authorise('core.admin')) && JAccess::check($item->id, 'core.admin'))
					{
						$canEdit   = false;
						$canChange = false;
					}
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php if ($canEdit) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td>
							<div class="student">
							<?php if ($canEdit) : 
								$student = $this->escape($item->firstname . ' ' .$item->lastname);
							?>
								<a href="<?php echo JRoute::_('index.php?option=com_cooltouraman&view=student&layout=edit&id=' . (int) $item->id); ?>" title="<?php echo JText::sprintf('COM_COOLTOURAMAN_EDIT_STUDENT', $student); ?>">
									<?php echo $student; ?></a>
							<?php else : ?>
								<?php echo $student; ?>
							<?php endif; ?>
							</div>
						</td>
						<td class="hidden-phone">
							<?php echo JHtml::_('date', $item->birthday_date, 'd-m-Y'); ?>
						</td>
						<td>
							<?php echo JStringPunycode::emailToUTF8($this->escape($item->email)); ?>
						</td>
						<td>
							<?php echo $this->escape($item->primary_phone); ?>
						</td>
						<td>
							<?php if (intval($item->parental_authority) > 0) :?>
								<?php echo $this->escape($item->parental_firstname) .' '. $this->escape($item->parental_lastname); ?>
							<?php else : ?>
								<?php echo JText::_('N/A'); ?>
							<?php endif; ?>
						</td>
						<td class="center hidden-phone">
							<?php echo $this->escape($item->attended_courses_number); ?>
						</td>
						<td class="center">
							<?php if ($canChange) : ?>
								<?php
								$self = $loggeduser->id == $item->id;
								echo JHtml::_('jgrid.state', CooltouramanHelper::blockStates($self), $item->site_access, $i, 'students.', !$self);
								?>
							<?php else : ?>
								<?php echo JText::_($item->site_access ? 'JNO' : 'JYES'); ?>
							<?php endif; ?>
						</td>
						<td class="hidden-phone">
							<?php echo JHtml::_('date', $item->register_date, 'd-m-Y H:i:s'); ?>
						</td>
						<td class="hidden-phone">
							<?php echo (int) $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
