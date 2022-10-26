<?php

/**
 * Helper for Template Code
 *
 * @package    BibleStudy.Admin
 * $author		Tom Fuller www.ChristianWebMinistries.org
 * @copyright  (C) 2007 - 2017 Christian Web Ministries Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.ChristianWebMinistries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

// Do not remove

?>
<div class="container-fluid">
  

<div class="row-fluid">
<div class="span12">
   <h3 style="text-align:right;">
  Bible Study from Calvary Chapel Newberg
</h3>
</div> 
<div class="span12"> 
  <h4 style="text-align:right;">
  with <?php echo $this->item->teachername;?>
</h4>
  </div>
</div>
<?php
echo $this->print;
echo $this->page->social;
//echo $this->related;
?>
<br />
<h2 style="text-align:center;">
  <?php echo $this->item->studytitle;?>
</h2>
<h4 style="text-align:center;">
<strong><?php echo $this->item->scripture1;?></strong></h4>
<?php echo $this->item->media;?>
<p>
  <?php echo $this->item->studytext;?>
</p>
</div>