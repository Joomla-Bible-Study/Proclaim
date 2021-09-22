<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
namespace CWM\Component\Proclaim\Site\Helper;
// No Direct Access
//namespace CWM\Compenent\BibleStudy\Site\CWMPagebuilder;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
require_once(BIBLESTUDY_PATH_LIB.'/CWMListing.php');
defined('_JEXEC') or die;

/**
 * Class to build page elements in use by custom template files
 *
 * @since  7.0.1
 */
class CWMPagebuilder
{
	/** @var string Extension Name
	 * @since 7.0
	 */
	public $extension = 'com_proclaim';

	/** @var  string Event
	 * @since 7.0
	 */
	public $event;

	/**
	 * Build Page
	 *
	 * @param   object                    $item      Item info
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   TableTemplate             $template  Template data
	 *
	 * @return object
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	public function buildPage($item, $params, $template)
	{
		$item->tp_id = '1';

		// Media files image, links, download
		$mids         = $item->mids;
		$page         = new stdClass;
		$JBSMElements = new CWMListing;

		if ($mids)
		{
			$page->media = $this->mediaBuilder($mids, $params, $template, $item);
		}
		else
		{
			$page->media = '';
		}

		// Scripture1
		$esv          = 0;
		$scripturerow = 1;

		if ($item->chapter_begin)
		{
			$page->scripture1 = $JBSMElements->getScripture($params, $item, $esv, $scripturerow);
		}
		else
		{
			$page->scripture1 = '';
		}

		if (!$item->secondary_reference)
		{
			$item->secondary_reference = '';
		}
		// Scripture 2
		$esv          = 0;
		$scripturerow = 2;

		if ($item->booknumber2 >= 1)
		{
			$page->scripture2 = $JBSMElements->getScripture($params, $item, $esv, $scripturerow);
		}
		else
		{
			$page->scripture2 = '';
		}

		// Study Date
		$page->studydate = $JBSMElements->getStudyDate($params, $item->studydate);

		// Translate Topics.
		$item->topics_text = JBSMTranslated::getConcatTopicItemTranslated($item);

		if (isset($item->topics_text) && (substr_count($item->topics_text, ',') > 0))
		{
			$topics = explode(',', $item->topics_text);

			foreach ($topics as $key => $value)
			{
				$topics[$key] = Text::_($value);
			}

			$page->topics = implode(', ', $topics);
		}
		else
		{
			$page->topics = Text::_($item->topics_text);
		}

		if ($item->thumbnailm)
		{
			$image                 = JBSMImages::getStudyThumbnail($item->thumbnailm);
			$page->study_thumbnail = '<img src="' . JUri::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height
				. '" alt="' . $item->studytitle . '" />';
		}
		else
		{
			$page->study_thumbnail = '';
		}

		if ($item->series_thumbnail)
		{
			$image                  = JBSMImages::getSeriesThumbnail($item->series_thumbnail);
			$page->series_thumbnail = '<img src="' . JUri::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height
				. '" alt="' . $item->series_text . '" />';
		}
		else
		{
			$page->series_thumnail = '';
		}

		$page->detailslink = JRoute::_('index.php?option=com_proclaim&view=sermon&id=' . $item->slug . '&t=' . $params->get('detailstemplateid'));

		if (!isset($item->image))
		{
			$item->image = '';
		}

		if (!isset($item->thumb))
		{
			$item->thumb = '';
		}

		if ($item->image || $item->thumb)
		{
			$image              = JBSMImages::getTeacherImage($item->image, $item->thumb);
			$page->teacherimage = '<img src="' . JUri::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="'
				. $item->teachername . '" />';
		}
		else
		{
			$page->teacherimage = '';
		}

		// Study Text
		if (!isset($item->studytext))
		{
			$item->studytext = '';
		}

		if (!isset($item->secondary_reference))
		{
			$item->secondary_reference = '';
		}

		if (!isset($item->sdescription))
		{
			$item->sdescription = '';
		}

		if ($params->get('show_scripture_link') === 0)
		{
			return $page;
		}
		// Set the item for the plugin to $item->text //run content plugins
		if ($page->scripture1)
		{
			$item->text       = $page->scripture1;
			$item             = $this->runContentPlugins($item, $params);
			$page->scripture1 = $item->text;
		}

		if ($page->scripture2)
		{
			$item->text       = $page->scripture2;
			$item             = $this->runContentPlugins($item, $params);
			$page->scripture2 = $item->text;
		}

		if ($item->studyintro)
		{
			$item->text       = $item->studyintro;
			$item             = $this->runContentPlugins($item, $params);
			$page->studyintro = $item->text;
		}

		if ($item->studytext)
		{
			$item->text      = $item->studytext;
			$item            = $this->runContentPlugins($item, $params);
			$page->studytext = $item->text;
		}

		if ($item->secondary_reference)
		{
			$item->text                = $item->secondary_reference;
			$item                      = $this->runContentPlugins($item, $params);
			$page->secondary_reference = $item->text;
		}

		if ($item->sdescription)
		{
			$item->text         = $item->sdescription;
			$item               = $this->runContentPlugins($item, $params);
			$page->sdescription = $item->text;
		}

		return $page;
	}

	/**
	 * Media Builder
	 *
	 * @param   array                     $mediaids  ID of Media
	 * @param   Joomla\Registry\Registry  $params    Item Params
	 * @param   TableTemplate             $template  template date
	 * @param   object                    $item      Item Params
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	private function mediaBuilder($mediaids, $params, $template, $item)
	{
		$listing          = new CWMListing;
		$mediaIDs         = $listing->getFluidMediaids($item);
		$media            = $listing->getMediaFiles($mediaIDs);
		$item->mediafiles = $media;

		return $listing->getFluidMediaFiles($item, $params, $template);
	}

	/**
	 * Run Content Plugins
	 *
	 * @param   object  $item    Item info
	 * @param   object  $params  Item params
	 *
	 * @return object
	 *
	 * @since 7.0
	 */
	public function runContentPlugins($item, $params)
	{
		// We don't need offset but it is a required argument for the plugin dispatcher
		$offset = '';
		JPluginHelper::importPlugin('content');

		// Run content plugins
        $dispatcher = Factory::getApplication();

		$dispatcher->triggerEvent('onContentPrepare', array(
				'com_proclaim.sermon',
				& $item,
				& $params,
				$offset
			)
		);

		$item->event = new stdClass;

		$results                        = $dispatcher->triggerEvent('onContentAfterTitle', array(
				'com_proclaim.sermon',
				&$item,
				&$params,
				$offset)
		);
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results                           = $dispatcher->triggerEvent('onContentBeforeDisplay', array(
				'com_proclaim.sermon',
				&$item,
				&$params,
				$offset)
		);
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results                          = $dispatcher->triggerEvent('onContentAfterDisplay', array(
				'com_proclaim.sermon',
				&$item,
				&$params,
				$offset)
		);
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		return $item;
	}

	/**
	 * Study Builder
	 *
	 * @param   string                    $whereitem   ?
	 * @param   string                    $wherefield  ?
	 * @param   Joomla\Registry\Registry  $params      Item params
	 * @param   int                       $limit       Limit of Records
	 * @param   string                    $order       DESC or ASC
	 * @param   TableTemplate             $template    Template Data
	 *
	 * @return array
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	public function studyBuilder($whereitem = null, $wherefield = null, $params = null, $limit = 10, $order = 'DESC', $template = null)
	{
		$db = Factory::getDbo();

		$orderparam = $params->get('order', '1');

		if ($orderparam === 2)
		{
			$order = "ASC";
		}

		// Compute view access permissions.
		$user   = Factory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		$query = $db->getQuery(true);
		$query->select('study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		                study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.studyintro,
		                study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, ' .
			// Use created if modified is 0
			'CASE WHEN study.modified = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.modified END as modified, ' .
			'study.modified_by, uam.name as modified_by_name,' .
			// Use created if publish_up is 0
			'CASE WHEN study.publish_up = ' . $db->quote($db->getNullDate()) . ' THEN study.studydate ELSE study.publish_up END as publish_up,' .
			'study.publish_down,
		                study.series_id, study.download_id, study.thumbnailm, study.thumbhm, study.thumbwm,
		                study.access, study.user_name, study.user_id, study.studynumber, study.chapter_begin2, study.chapter_end2,
		                study.verse_end2, study.verse_begin2, ' . ' ' . $query->length('study.studytext') . ' AS readmore' . ','
			. ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');
		$query->from('#__bsms_studies AS study');

		// Join over Message Types
		$query->select('messageType.message_type AS message_type');
		$query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

		// Join over Teachers
		$query->select('teacher.teachername AS teachername, teacher.title as teachertitle, teacher.thumb, teacher.thumbh, teacher.thumbw');
		$query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

		// Join over Series
		$query->select('series.series_text, series.series_thumbnail, series.description as sdescription, series.access');
		$query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

		// Join over Books
		$query->select('book.bookname');
		$query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

		$query->select('book2.bookname as bookname2');
		$query->join('LEFT', '#__bsms_books AS book2 ON book2.booknumber = study.booknumber2');

		// Join over Plays/Downloads
		$query->select('GROUP_CONCAT(DISTINCT mediafile.id) as mids, SUM(mediafile.plays) AS totalplays,
		SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
		$query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');

		// Join over Locations
		$query->select('locations.location_text');
		$query->join('LEFT', '#__bsms_locations AS locations ON study.location_id = locations.id');

		// Join over studytopics
		$query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
		$query->join('LEFT', '#__bsms_studytopics AS st ON study.id = st.study_id');
		$query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topic_text, GROUP_CONCAT(DISTINCT t.params) as topic_params');
		$query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

		// Join over the users for the author and modified_by names.
		$query->select("CASE WHEN study.user_name > ' ' THEN study.user_name ELSE users.name END AS submitted")
			->select("users.email AS author_email")
			->join('LEFT', '#__users AS users ON study.user_id = users.id')
			->join('LEFT', '#__users AS uam ON uam.id = study.modified_by');

		$query->group('study.id');

		// Select only published studies
		$query->where('study.published = 1');
		$query->where('(series.published = 1 OR study.series_id <= 0)');

		if ($wherefield && $whereitem)
		{
			$query->where($wherefield . ' = ' . $whereitem);
		}

		// Define null and now dates
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		// Filter by start and end dates.
		if ((!$user->authorise('core.edit.state', 'com_proclaim')) && (!$user->authorise('core.edit', 'com_proclaim')))
		{
			$query->where('(study.publish_up = ' . $nullDate . ' OR study.publish_up <= ' . $nowDate . ')')
				->where('(study.publish_down = ' . $nullDate . ' OR study.publish_down >= ' . $nowDate . ')');
		}

		// Filter by language
		$language = $params->get('language', '*');

		if ($language === '*')
		{
			$query->where('study.language in (' . $db->quote($language) . ',' . $db->quote('*') . ')');
		}
		elseif ($language !== '*')
		{
			$query->where('study.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		}

		$query->order('studydate ' . $order);

		// Filter only for authorized view
		$query->where('(series.access IN (' . $groups . ') or study.series_id <= 0)');
		$query->where('study.access IN (' . $groups . ')');

		$db->setQuery($query, 0, $limit);

		return $db->loadObjectList();
	}
}
