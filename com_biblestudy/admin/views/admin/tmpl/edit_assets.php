<?php
/**
 * Form sub assets
 * @package BibleStudy.Admin
 * @author Joomla Bible Study
 * @copyright 2012
 * @since 7.0.2
 * @desc Form to perform check and fix to the assets
 */
//No Direct Access
defined('_JEXEC') or die;
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
    <div class="row-fluid">
        <div class="span6 form-horizontal">
            <h4><?php echo JText::_('JBS_ADM_ASSET_CHECK'); ?></h4>

            <div class="span2">
                <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&amp;view=admin&layout=edit&id=1&task=admin.checkassets') ?>"><img
                        src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/import.png'; ?>"
                        alt="Check Assets" height="48" width="48" style="clear: right"/>

                    <div><?php echo JText::_('JBS_ADM_CHECK_ASSETS'); ?></div>
                </a>
            </div>
            <div class="span2">
                <a href="<?php echo JRoute::_('index.php?option=com_biblestudy&amp;view=admin&layout=edit&id=1&task=admin.fixAssets') ?>"><img
                        src="<?php echo JURI::base() . '../media/com_biblestudy/images/icons/export.png'; ?>"
                        alt="Fix Assets" height="48" width="48"/>

                    <div><?php echo JText::_('JBS_ADM_FIX'); ?></div>
                </a>
            </div>
            <div class="clearfix"></div>
            <div class="table">
                <?php
                $checkassets2 = JRequest::getVar('checkassets', null, 'get', 'array');

                if ($checkassets2) {
                    echo '<table>';
                    echo '<caption><h2>' . JText::_('JBS_ADM_ASSET_TABLE_NAME') . '</h2></caption>';
                    echo '<thead>';
                    echo '<tr>';

                    echo '<th>' . JText::_('JBS_ADM_TABLENAMES') . '</th>';
                    echo '<th>' . JText::_('JBS_ADM_ROWCOUNT') . '</th>';
                    echo '<th>' . JText::_('JBS_ADM_NULLROWS') . '</th>';
                    echo '<th>' . JText::_('JBS_ADM_MATCHROWS') . '</th>';
                    echo '<th>' . JText::_('JBS_ADM_NOMATCHROWS') . '</th>';
                    echo '</tr>';
                    echo '</thead>';
                    foreach ($checkassets2 as $asset) {
                        echo '<tr>';
                        echo '<td><p>' . JText::_($asset['realname']) . '</p></td>';
                        echo '<td><p>' . JText::_($asset['numrows']) . '</p></td>';
                        echo '<td>';
                        if ($asset['nullrows'] > 0) {
                            echo '<p style="color: red;">';
                        } else {
                            echo '<p>';
                        }
                        echo JText::_($asset['nullrows']) . '</p></td>';
                        echo '<td>';
                        if ($asset['matchrows'] > 0) {
                            echo '<p style="color: green">';
                        } else {
                            echo '<p>';
                        }
                        echo JText::_($asset['matchrows']) . '</p></td>';
                        echo '<td>';
                        if ($asset['nomatchrows'] > 0) {
                            echo '<p style="color: red">';
                        } else {
                            echo '<p>';
                        }
                        echo JText::_($asset['nomatchrows']) . '</p></td>';
                        echo '</tr>';
                    }
                    echo '<tr><td colspan="5">';
                    echo '<p>' . JText::_('JBS_ADM_ASSET_EXPLANATION') . '</p>';
                    echo '</td></tr>';
                    echo '</table>';
                }
                ?>
            </div>
            <input type="hidden" name="option" value="com_biblestudy"/>
            <input type="hidden" name="task" value="admin.checkassets"/>
            <input type="hidden" name="controller" value="admin"/>
            <input type="hidden" name="tooltype" value=""/>
	        <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
</form>
