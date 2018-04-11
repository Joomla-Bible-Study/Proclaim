<?php
/**
 * Default
 *
 * @package    BibleStudy.Site
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;
?>
<div class="container-fluid"> <!-- This div is the container for the whole page --><?php

	if ($this->item->params->get('sermontemplate') && !$this->simple_mode)
	{
		echo $this->loadTemplate($this->item->params->get('sermontemplate'));
	}
    elseif ($this->simple_mode == 1)
	{
		echo $this->loadTemplate('simple');
	}
	else
	{
		echo $this->loadTemplate('main');
	}
	$show_comments = $this->item->params->get('show_comments');

	if ($show_comments >= 1)
	{
		$user           = JFactory::getUser();
		$groups         = $user->getAuthorisedViewLevels();
		$comment_access = $this->item->params->get('comment_access');

		if (in_array($show_comments, $groups))
		{
			// Determine what kind of comments component to use
			switch ($this->item->params->get('comments_type', 0))
			{
				case 0:
					// This should be using JBS comments only
					echo $this->loadTemplate('commentsform');
					break;

				case 1:
					// This is a just JComments
					$comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
					if (file_exists($comments))
					{
						require_once($comments);
						echo JComments::showComments($this->item->id, 'com_biblestudy', $this->item->studytitle);
					}
					break;

				case 2:
					// This is a combination of JBS and JComments
					$comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
					if (file_exists($comments))
					{
						require_once($comments);
						echo JComments::show($this->item->id, 'com_biblestudy', $this->item->studytitle);
					}
					echo $this->loadTemplate('commentsform');
					break;
			}

		}
	}
	echo $this->loadTemplate('footerlink');

	?>
</div><!--end of container fluid-->
