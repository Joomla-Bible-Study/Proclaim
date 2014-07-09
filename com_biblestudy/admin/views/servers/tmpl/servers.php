<?php
//@todo Add proper header

defined('_JEXEC') or die;
$input = new JInput;

$function = $input->get('function', 'jSelectServer', 'cmd');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDir = $this->escape($this->state->get('list.direction'));

$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
    setServerId = function (server_id) {
        window.parent.Joomla.submitbutton('mediafile.setServerId', server_id);
        window.parent.SqueezeBox.close();
    }
</script>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=servers&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1'); ?>"
    method="post"
    name="adminForm"
    id="adminForm">
    <table class="table">
        <thead>
        <tr>
            <th>
                <?php echo JHtml::_('grid.sort', 'JBS_SVR_SERVER_NAME', 'mediafile.name', $listDir, $listOrder); ?>
            </th>
            <th>
                <?php echo JHtml::_('grid.sort', 'JBS_SVR_FILEPATH_TYPE', 'mediafile.type', $listDir, $listOrder); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="2">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <a href="#" onclick="javascript: setServerId('<?php echo $item->id; ?>')">
                        <?php echo $this->escape($item->server_name); ?>
                    </a>
                </td>
                <td>
                    <?php echo $item->type; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</form>