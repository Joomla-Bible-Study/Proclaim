<?php
/**
 * Default for sermons
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
if (BIBLESTUDY_CHECKREL)
{
    JHtml::_('bootstrap.framework');
}
$JViewLegacy = new JViewLegacy;
$JViewLegacy->loadHelper('teacher');
$JBSMTeacher = new JBSMTeacher;
$teachers = $JBSMTeacher->getTeachersFluid($this->params);
?>

<div class="container-fluid">
<?php if ($this->params->get('intro_show') > 0)
{ ?>
<div class="hero-unit" style="padding: 30px;">
    <?php
    if ($this->params->get('listteachers') && $this->params->get('list_teacher_show') > 0)
    {
        ?>
    <div class="row-fluid" >
            <ul class="thumbnails">
                <?php $spans = 12 / count($teachers);
                foreach ($teachers as $teacher)
                {
                        echo '<li class="span'.$spans.'">';
                        if ($this->params->get('teacherlink')> 0)
                        {echo '<a href="index.php?option=com_biblestudy&view=teacher&id='.$teacher['id'].'&t='.$teacher['t'].'" ><img class="img-polaroid" src="'.JURI::base().$teacher['image'].'"></a>';}
                        else {echo '<img class="img-polaroid" src="'.JURI::base().$teacher['image'].'">';}
                        if ($this->params->get('teacherlink')> 0)
                        {echo '<div class="caption"><p><a href="index.php?option=com_biblestudy&view=teacher&id='.$teacher['id'].'&t='.$teacher['t'].'">'.$teacher['name'].'</a></p>';}
                        else {echo '<div class="caption"><p>'.$teacher['name'].'</p></div>';}
                        echo '</li>';
                }
                ?>
            </ul>
    </div>
    <?php } ?>
    <div class="row-fluid">
        <div class="span12">
            <?php if ($this->params->get('show_page_image') > 0){?> <img class="imgcenter" src="<?php echo JURI::base() . $this->main->path; ?>"><?php }?>
            <?php if ($this->params->get('show_page_title') == 1){?><h2><?php echo $this->params->get('page_title');?></h2><?php }?>
            <?php if ($this->params->get('list_intro')){?><p><?php echo $this->params->get('list_intro');?></p><?php }?>
        </div>
    </div>
</div>

</div><!-- .hero-unit -->
<?php }?>
<div class="container-fluid">
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            Books
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a tabindex="-1" href="#">Action</a></li>
            <li><a tabindex="-1" href="#">Another action</a></li>
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#">More options</a>
                <ul class="dropdown-menu">
                    <li><a tabindex="-1" href="#">Something else here</a></li>
                    <li><a tabindex="-1" href="#">Something else here</a></li>
                </ul>
            <li><a tabindex="-1" href="#">Something else here</a></li>
        </ul>
    </div>
    <form class="btn-group">
    <div class="btn-group">
        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
            Teachers
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <li><a tabindex="-1" href="#">Action</a></li>
            <li><a tabindex="-1" href="#">Another action</a></li>
            <li class="dropdown-submenu">
                <a tabindex="-1" href="#">More options</a>
                <ul class="dropdown-menu">
                    <li><a tabindex="-1" href="#">Something else here</a></li>
                    <li><a tabindex="-1" href="#">Something else here</a></li>
                </ul>
            <li><a tabindex="-1" href="#">Something else here</a></li>
        </ul>
    </div>


    </form>
</div>
<div class="container-fluid">

    <div class="row-fluid">
        <div class="span4">
            <h2>Box Number 1</h2>
            <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
            <p><a class="btn" href="#">Click meeee &raquo;</a></p>
        </div><!-- .span4 -->

        <div class="span4">
            <h2>Box Number 2</h2>
            <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
            <p><a class="btn" href="#">Click meeee &raquo;</a></p>
        </div><!-- .span4 -->

        <div class="span4">
            <h2>Box Number 3</h2>
            <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
            <p><a class="btn" href="#">Click meeee &raquo;</a></p>
        </div><!-- .span4 -->

    </div><!-- .row -->

    <div class="row-fluid ">
        <div class="span4"><div class=""><img src="<?php echo JURI::base();?>/tom.jpg"></div></div>
        <div class="span8">
            <div class="row-fluid ">
                <div class="span6"><div class="">2</div></div>
                <div class="span6"><div class="">3</div></div>
            </div>
            <div class="row-fluid ">
                <div class="span6"><div class="">4</div></div>
                <div class="span6"><div class="">5</div></div>
            </div>
        </div>
    </div>
    <div class="row-fluid ">
        <div class="span4">
            <div class="">6</div>
        </div>
        <div class="span4">
            <div class="">6</div>
        </div>
        <div class="span4">
            <div class="">6</div>
        </div>
    </div>
</div><!-- .container -->
</div>