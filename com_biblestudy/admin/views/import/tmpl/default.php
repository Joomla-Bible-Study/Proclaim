<?php
/**
 * Default
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */

// Protect from unauthorized access
defined('_JEXEC') or die();

JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
?>
<div id="j-main-container" class="span10">

    <div id="cpanel" style="padding-left: 20px">
    <div class="pull-left">
        <div class="icon">
            <a href="index.php?option=com_biblestudy&view=migration&tmpl=component&jbsimport=<?php echo $this->jbsimport ?>"
               class="modal" style="text-decoration:none;" rel="{handler: 'iframe', size: {x: 600, y: 250}}">
                <img src="../media/com_biblestudy/images/icons/icon-48-administration.png"
                     border="0" alt="<?php echo JText::_('test') ?>" width="32" height="32" align='middle'
                     style="float: none; clear: both;"/>
					<span>
						<?php echo JText::_('test') ?>
					</span>
            </a>
        </div>
    </div>
	    </div>
</div>
