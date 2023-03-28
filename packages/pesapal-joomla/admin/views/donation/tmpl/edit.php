<?php
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_pesapal&layout=edit&id=' . (int)$this->item->id); ?>" method="post"
      name="adminForm" id="adminForm" class="form-validate">
    <div class="row-fluid">
        <div class="span10 form-horizontal">
            <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_PESAPAL_MAIN_FIELDS', true)); ?>

            <?php foreach ($this->form->getFieldset("main") as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label; ?></div>
                    <div class="controls"><?php echo $field->input; ?></div>
                </div>
            <?php endforeach; ?>

            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('COM_PESAPAL_OTHER_FIELDS', true)); ?>

            <?php foreach ($this->form->getFieldset("others") as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label; ?></div>
                    <div class="controls"><?php echo $field->input; ?></div>
                </div>
            <?php endforeach; ?>

            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>
                <input type="hidden" name="task" value=""/>
                <?php echo JHtml::_('form.token'); ?>
                <?php echo JHtml::_('bootstrap.endPane'); ?>
        </div>
    </div>
</form>