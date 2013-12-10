<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die();

?>
<form action="<?php
$input = new JInput;
if ($input->get('layout', '', 'string') == 'modal')
{
    $url = 'index.php?option=com_biblestudy&view=upload&tmpl=component&layout=modal';
}
else
{
    $url = 'index.php?option=com_biblestudy&view=upload&layout=edit&id=';
}
echo JRoute::_($url);
?>" method="post" name="adminForm" id="item-form" class=" form-horizontal">
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('server'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('server'); ?>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
            <?php echo $this->form->getLabel('path'); ?>
        </div>
        <div class="controls">
            <?php echo $this->form->getInput('path'); ?>
        </div>
    </div>
