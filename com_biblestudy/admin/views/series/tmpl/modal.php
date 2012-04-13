<?php
/**
 * @version $Id: modal.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
$function = JRequest::getVar('function', 'jSelectStudy');
?>
<form action="<?php echo JRoute::_('index.php?option=com_biblestudy&view=series&layout=modal&tmpl=component'); ?>" method="post" name="adminForm">
    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th width="5">
                        <?php echo JText::_('JBS_CMN_ID'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('JBS_CMN_SERIES'); ?>
                    </th>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            for ($i = 0, $n = count($this->items); $i < $n; $i++) {
                $row = &$this->items[$i];
                // workaround for JS: replace quotes and double quotes by another character
                $row->series_text = str_replace("'", '`', $row->series_text);
                $row->series_text = str_replace('"', '`', $row->series_text);
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td width="5">
                        <?php echo $row->id; ?>
                    </td>

                    <td><a class="pointer" onclick="if (window.parent) window.parent.<?php echo $function; ?>('<?php echo $row->id; ?>', '<?php echo $row->series_text; ?>');">
                            <?php echo $row->series_text; ?></a></td>

                </tr>
                <td class="center">
                    <?php if ($item->language == '*'): ?>
                        <?php echo JText::alt('JALL', 'language'); ?>
                    <?php else: ?>
                        <?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                    <?php endif; ?>
                </td>
                <?php
                $k = 1 - $k;
            }
            ?>
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
