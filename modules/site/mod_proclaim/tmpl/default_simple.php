<?php
/**
 * Mod_Proclaim core file
 *
 * @package     Proclaim
 * @subpackage  Module.Proclaim
 * @copyright   2007 - 2019 (C) CWM Team All rights reserved
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link        https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Site\Helper\CWMListing;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/** @var Registry $params */
$show_link = $params->get('show_link', 1);

$Listing = new CWMListing; ?>

  <div class="row-fluid span12">
    <h5>
      <?php echo Text::_('JBS_CMN_TEACHINGS'); ?>
    </h5>
  </div>

<?php foreach ($list as $study)
{

	?>
    <div class="page-header">
        <p itemprop="headline">
            <?php echo $study->studytitle; ?>		</p>
    </div>
    <dl class="article-info text-muted">

        <dd class="createdby" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
            <span class="icon-user icon-fw" aria-hidden="true"></span>
           <span itemprop="name"><?php echo $study->teachername;?></span>	</dd>

        <dd class="category-name">
            <span class="fas fa-bible" aria-hidden="true"></span>
             <?php echo $study->scripture1;?>	</dd>

        <dd class="published">
            <span class="icon-calendar icon-fw" aria-hidden="true"></span>
            <time datetime="2022-07-14T12:19:37-07:00" itemprop="datePublished">
                 <?php echo $study->studydate;?>	</time>
        </dd>
    </dl>
    <div itemprop="articleBody" class="com-content-article__body">
        <?php echo $study->media; ?> 	</div>
    <div><hr/></div>
<?php }?>

<div class="row-fluid">
	<div class="span12">
		<?php
		if ($params->get('show_link') > 0)
		{
			echo '<span class="fas fa-bible" aria-hidden="true"></span>'.$link;
		}
		?>
	</div>
</div>
<!--end of footer div-->
<!--end container -->
<div style="clear: both;"></div>

