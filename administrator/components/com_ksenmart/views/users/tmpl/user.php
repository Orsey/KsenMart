<?php
defined( '_JEXEC' ) or die;
JHtml::_('behavior.tooltip');
?>
<form class="form" method="post">
	<div class="heading">
		<h3><?php echo $this->title;?></h3>
		<div class="save-close">
			<input type="button" value="<?php echo JText::_('ksm_save')?>" class="save">
			<input type="button" class="close" onclick="parent.closePopupWindow();">
		</div>
	</div>
	<div class="edit">
		<table width="100%">
			<tr>
				<td class="leftcol">
					<div class="row">
						<?php echo $this->form->getLabel('name'); ?>
						<?php echo $this->form->getInput('name'); ?>
					</div>					
					<div class="row">
						<?php echo $this->form->getLabel('username'); ?>
						<?php echo $this->form->getInput('username'); ?>
					</div>	
					<?php if (!isset($this->user->social)):?>
					<div class="row">
						<?php echo $this->form->getLabel('password'); ?>
						<?php echo $this->form->getInput('password'); ?>
					</div>	
					<div class="row">
						<?php echo $this->form->getLabel('password2'); ?>
						<?php echo $this->form->getInput('password2'); ?>
					</div>						
					<?php endif;?>
					<div class="row">
						<?php echo $this->form->getLabel('email'); ?>
						<?php echo $this->form->getInput('email'); ?>
					</div>						
					<div class="row">
						<?php echo $this->form->getLabel('phone'); ?>
						<?php echo $this->form->getInput('phone'); ?>
					</div>
					<div class="row">
						<?php echo $this->form->getLabel('region_id'); ?>
						<?php echo $this->form->getInput('region_id'); ?>
					</div>	
					<div class="row">
						<?php echo $this->form->getInput('fields'); ?>
					</div>	
					<div class="row">
						<?php echo $this->form->getInput('addresses'); ?>
					</div>					
				</td>
				<td class="rightcol">	
					<div class="row">
						<?php echo $this->form->getInput('images'); ?>
					</div>				
					<div class="row">
						<?php echo $this->form->getInput('groups'); ?>
					</div>					
				</td>
			</tr>	
		</table>
	</div>	
	<input type="hidden" name="task" value="save_form_item">
	<?php echo $this->form->getInput('social'); ?>
	<?php echo $this->form->getInput('id'); ?>
</form>	