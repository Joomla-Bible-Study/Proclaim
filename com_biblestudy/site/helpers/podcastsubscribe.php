<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * A helper to return buttons for podcast subscriptions
 *
 * @package  BibleStudy.Site
 * @since    7.1.0
 *
 */
class JBSMPodcastSubscribe
{
	/**
	 * Build Subscribe Table
	 *
	 * @param   string  $introtext  Intro Text
	 *
	 * @return string
	 *
	 * @since    7.1
	 */
	public function buildSubscribeTable($introtext = 'Our Podcasts')
	{
		$podcasts = $this->getPodcasts();

		$subscribe = '';

		if ($podcasts)
		{
			$subscribe .= '<div class="podcastheader" ><h3>' . $introtext . '</h3></div>';
			$subscribe .= '<div class="prow row-fluid">';

			foreach ($podcasts AS $podcast)
			{
				$podcastshow = $podcast->podcast_subscribe_show;

				if (!$podcastshow)
				{
					$podcastshow = 2;
				}

				switch ($podcastshow)
				{
					case 1:
						break;

					case 2:
						$subscribe .= '<div class="pcell span6"><h4>' . $podcast->title . '</h4><hr />';
						$subscribe .= $this->buildStandardPodcast($podcast);
						$subscribe .= '</div>';
						break;

					case 3:
						$subscribe .= '<div class="pcell span6"><h4>' . $podcast->title . '</h4><hr />';
						$subscribe .= $this->buildAlternatePodcast($podcast);
						$subscribe .= '</div>';
						break;

					case 4:
						$subscribe .= '<div class="pcell span6"><h4>' . $podcast->title . '</h4><hr /><div class="span2">';
						$subscribe .= $this->buildStandardPodcast($podcast);
						$subscribe .= '</div><div class="span2">';
						$subscribe .= $this->buildAlternatePodcast($podcast);
						$subscribe .= '</div></div>';
						break;
				}
			}
			// End of row
			$subscribe .= '</div>';

			// Add a div around it all
			$subscribe = '<div class="podcastsubscribe">' . $subscribe . '</div>';
		}

		return $subscribe;
	}

	/**
	 * Get Podcasts
	 *
	 * @return object Object List of Podcasts
	 *
	 * @since    7.1
	 */
	public function getPodcasts()
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$db    = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('*')
			->from('#__bsms_podcast as p')
			->where('p.published = 1')
			->where('p.access IN (' . $groups . ')');
		$db->setQuery($query);
		$podcasts = $db->loadObjectList();

		return $podcasts;
	}

	/**
	 * Build Standard Podcast
	 *
	 * @param   object  $podcast  Podcast Info
	 *
	 * @return string
	 *
	 * @since    7.1
	 */
	public function buildStandardPodcast($podcast)
	{
		$subscribe = '';

		if (!empty($podcast->podcast_image_subscribe))
		{
			$image = $this->buildPodcastImage($podcast->podcast_image_subscribe, $podcast->podcast_subscribe_desc);
			$link  = '<div class="image"><a href="' . JUri::base() . $podcast->filename . '">' . $image . '</a></div><div class="clr"></div>';
			$subscribe .= $link;
		}

		if (empty($podcast->podcast_subscribe_desc))
		{
			$name = $podcast->title;
		}
		else
		{
			$name = $podcast->podcast_subscribe_desc;
		}

		$subscribe .= '<div class="text"><a href="' . JUri::base() . $podcast->filename . '">' . $name . '</a></div>';

		return $subscribe;
	}

	/**
	 * Build Podcast Image
	 *
	 * @param   array  $podcastimagefromdb  Podcast image
	 * @param   array  $words               Alt podcast image text
	 *
	 * @return string
	 *
	 * @since    7.1
	 */
	public function buildPodcastImage($podcastimagefromdb = null, $words = null)
	{
		$images       = new JBSMImages;
		$image        = $images->getMediaImage($podcastimagefromdb);
		$podcastimage = null;

		if ($image->path)
		{
			$podcastimage = JHtml::image(
				JUri::base() . $image->path, $words, 'width = "' . $image->width
				. '" height = "' . $image->height . '" title = "' . $words . '"'
			);
		}

		return $podcastimage;
	}

	/**
	 * Build Alternate Podcast
	 *
	 * @param   object  $podcast  Podcast info
	 *
	 * @return string
	 *
	 * @since    7.1
	 */
	public function buildAlternatePodcast($podcast)
	{
		$subscribe = '';

		if (!empty($podcast->alternateimage))
		{
			$image = $this->buildPodcastImage($podcast->alternateimage, $podcast->alternatewords);
			$link  = '<div class="image"><a href="' . $podcast->alternatelink . '">' . $image . '</a></div><div class="clearfix"></div>';
			$subscribe .= $link;
		}

		$subscribe .= '<div class="text"><a href="' . $podcast->alternatelink . '">' . $podcast->alternatewords . '</a></div>';

		return $subscribe;
	}
}
