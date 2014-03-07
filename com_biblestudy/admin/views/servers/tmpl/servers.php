<?php
//@todo Add proper header

defined('_JEXEC') or die;
$input = new JInput;

$function   = $input->get('function', 'jSelectServer', 'cmd');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDir    = $this->escape($this->state->get('list.direction'));

$input = JFactory::getApplication()->input;
?>
<script type="text/javascript">
    setServer = function(server) {
        window.parent.Joomla.submitbutton('mediafile.setServer', server);
        window.parent.SqueezeBox.close();
    }
</script>
<form
    action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=servers&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1'); ?>"
    method="post"
    name="adminForm"
    id="adminForm">
    <fieldset id="filter clearfix">
        <div class="filter-search btn-group pull-left">
            <label for="filter_search" class="element-invisible">
                <?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>
            </label>
            <input type="text" name="filter_search" placeholder="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"
                   id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   title="<?php echo JText::_('JBS_CMN_FILTER_SEARCH_DESC'); ?>"/>
        </div>
    </fieldset>
    <table class="adminlist">
        <thead>
        <tr>
            <th>
                <?php echo JHtml::_('grid.sort', '*SERVER NAME*', 'mediafile.name', $listDir, $listOrder); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="1">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach($this->items as $i => $item): ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center">
                    <a href="#" onclick="javascript: setServer('<?php echo base64_encode(json_encode(array('media_id' => $input->get('media_id', 0, 'get'), 'server_id' => $item->id))); ?>')">
                        <?php echo $this->escape($item->server_name); ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</form>