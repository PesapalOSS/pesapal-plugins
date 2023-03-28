<?php
defined('_JEXEC') or die;
$listOrder     = $this->escape($this->filter_order);
$listDirn      = $this->escape($this->filter_order_Dir);
?>
<form action="<?php echo JRoute::_('index.php?option=com_
pesapal&view=donations'); ?>" method="post" name="adminForm"
      id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar = JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">

        <?php
        echo JLayoutHelper::render(
            'joomla.searchtools.default',
            array('view' => $this)
        );
        ?>

        <div class="clearfix"></div>
        <table class="table table-striped" id="folioList">
            <thead>
            <tr>
                <th width="1%" class="hidden-phone">
                    <input type="checkbox" name="checkall-toggle" value=""
                           title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                           onclick="Joomla.checkAll(this)"/>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_FULLNAME', 'a.firstname', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_AMOUNT', 'a.amount', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_DATE', 'a.date_created', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_METHOD', 'a.method', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_REFERENCE', 'a.reference', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_PESAPAL_STATUS', 'a.status', $listDirn, $listOrder); ?>
                </th>


            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center hidden-phone">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td>
                            <?php echo $this->escape($item->firstname); ?>
                            <?php echo $item->lastname; ?>
                    </td>
                    <td>
                        <?php echo $item->email; ?>
                    </td>
                    <td>
                        <?php echo $item->currency; ?> <?php echo $item->amount; ?>
                    </td>
                    <td>
                        <?php echo $item->date_created; ?>
                    </td>
                    <td>
                        <?php echo $item->method; ?>
                    </td>
                    <td>
                        <?php echo $item->reference; ?>
                    </td>
                    <td>
                        <?php echo $item->status; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo
        $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo
        $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
        <?php echo $this->pagination->getListFooter(); ?>
    </div>
</form>
