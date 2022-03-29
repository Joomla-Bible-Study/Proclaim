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

  
  <div class="row-fluid span12">
    <h2>
      Bible Studies
    </h2>
  </div>

<div class="row-fluid span12"> <p>
  
  <?php echo $this->params->get('list_intro');?> </p></div>

  <div class="row-fluid span12">
    <?php echo $this->page->gobutton;
    echo $this->limitbox;
	echo $this->page->popular;
    echo $this->page->books;
    echo $this->page->teachers;
    echo $this->page->series;
    echo $this->page->years;
    echo $this->page->order;
    $oddeven = '';
	$class1 = '#d3d3d3';
    $class2 = '';?>
  </div>
  <?php foreach ($this->items as $study)
    {
      $oddeven = ($oddeven == $class1) ? $class2 : $class1;
      ?>
  <div class="row-fluid span12">
    <div class="span3">
    
   <strong> <?php echo $study->scripture1;?></strong>
  </div>
  <div class="span4">
    <a href="<?php echo $study->detailslink;?>"><strong><?php echo $study->studytitle;?></strong></a>
  </div>
  <div class="span4">
    <?php echo $study->media;?>
  </div>
    <div class="span12" style="margin-left: -2px;">
    <p><?php echo $study->studyintro;?></p>
  </div>
    <hr class="span12" style="border: 0; height: 1px; background-image: -webkit-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.25), rgba(0,0,0,0)); background-image: -moz-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.25), rgba(0,0,0,0)); background-image: -ms-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.75), rgba(0,0,0,0)); background-image: -o-linear-gradient(left, rgba(0,0,0,0), rgba(0,0,0,0.25), rgba(0,0,0,0));" />
  </div>
   <?php }?>
  <div class="row-fluid span12 pagination">
    <?php echo $this->pagination->getPageslinks();?>
  </div>