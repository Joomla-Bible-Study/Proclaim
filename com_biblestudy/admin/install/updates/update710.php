<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');

/**
 * Update for 7.1.0 class
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class JBS710Update
{

	/**
	 * Method to Update to 7.1.0
	 *
	 * @return boolean
	 */
	public function update710()
	{
		$app     = JFactory::getApplication();
		$db      = JFactory::getDBO();
		$old_css = false;
		$newCSS  = null;
		jimport('joomla.filesystem.file');

		// Check to see if there is an existing css
		$src = JPATH_SITE . '/tmp/biblestudy.css';

		// There is no existing css so let us check for a backup
		$backup  = JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/backup/biblestudy.css';
		$default = JPATH_SITE . DIRECTORY_SEPARATOR . 'media/com_biblestudy/css/biblestudy.css';

		// If there is no new css file in the media folder, check to see if there is one in the old assets or in the backup folder

		if (JFile::exists($src))
		{
			$old_css = file_get_contents($src);
		}
		elseif (JFile::exists($backup))
		{
			$old_css = file_get_contents($backup);
		}
		else
		{
			$newCSS = file_get_contents($default);
		}
		if ($old_css)
		{
			$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result)
			{
				$query = 'UPDATE #__bsms_styles SET `stylecode` = "' . $db->escape($old_css) . '" WHERE `id` = ' .
					$result->id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

					return false;
				}
			}
			else
			{
				$query =
					'INSERT INTO #__bsms_styles (`published`, `filename`, `stylecode`, `asset_id`) VALUES (1,"biblestudy","' .
						$db->escape($old_css) . '",0)';
				$db->setQuery($query);

				if (!$db->execute())
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

					return false;
				}
			}
			if (JFile::exists($src))
			{
				JFile::delete($src);
			}
			// Add CSS to the file
			$new710css = '
/* New Teacher Codes */
#bsm_teachertable_list .bsm_teachername
{
    font-weight: bold;
    font-size: 14px;
    color: #000000;
}
#bsm_teachertable_list
{
    margin: 0;
    border-collapse:separate;
}
#bsm_teachertable_list td {
    text-align:left;
    padding:0 5px 0 5px;
    border:none;
}
#bsm_teachertable_list .titlerow
{
    border-bottom: thick;
}
#bsm_teachertable_list .title
{
    font-size:18px;
    font-weight:bold;
    border-bottom: 3px solid #999999;
    padding: 4px 0px 4px 4px;
}
#bsm_teachertable_list .bsm_separator
{
    border-bottom: 1px solid #999999;
}

.bsm_teacherthumbnail_list
{

}
#bsm_teachertable_list .bsm_teacheremail
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_teacherwebsite
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_teacherphone
{
    font-weight:normal;
    font-size: 11px;
}
#bsm_teachertable_list .bsm_short
{
    padding: 8px 4px 4px;
    font-weight:normal;
}
#bsm_teachertable .bsm_studiestitlerow {
    background-color: #666;
}
#bsm_teachertable_list .bsm_titletitle
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_titlescripture
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_titledate
{
    font-weight:bold;
    color:#FFFFFF;
}
#bsm_teachertable_list .bsm_teacherlong
{
    padding: 8px 4px 4px;
    border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bsodd {
    background-color:#FFFFFF;
    border-bottom: 1px solid #999999;
}
#bsm_teachertable_list tr.bseven {
    background-color:#FFFFF0;
    border-bottom: 1px solid #999999;
}

#bsm_teachertable_list .lastrow td {
    border-bottom:1px solid grey;
    padding-bottom:7px;
    padding-top:7px;
}
#bsm_teachertable_list .bsm_teacherfooter
{
    border-top: 1px solid #999999;
    padding: 4px 1px 1px 4px;
}
/* New Teacher Details Codes */

#bsm_teachertable .teacheraddress{
    text-align:left;
}

#bsm_teachertable .teacherwebsite{
    text-align:left;}

#bsm_teachertable .teacherfacebook{
    text-align:left;
}

#bsm_teachertable .bsm_teachertwitter{
    text-align:left;
}

#bsm_teachertable .bsm_teacherblog{
    text-align:left;
}

#bsm_teachertable .bsm_teacherlink1{
    text-align:left;
}


/* New Landing Page CSS */

.landingtable {
    clear:both;
    width:auto;
    display:table;

}

.landingrow {
    display:inline;
    padding: 1em;
}
.landingcell {
    display:table-cell;
}

.landinglink a{
    display:inline;
}

/* Terms of use or donate display settings */
.termstext {
}

.termslink{
}
/* Podcast Subscription Display Settings */

.podcastsubscribe{
    clear:both;
    display:table;
    width:100%;
    background-color:#eee;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
}
.podcastsubscribe .image {
    float: left;
    padding-right: 5px;
    display: inline;
}
.podcastsubscribe .image .text {
    display:inline;
    position:relative;
    right:50px;
    bottom:-10px;
}
.podcastsubscribe .prow {
    display: table-row;
    width:auto;
    clear:both;
}
.podcastsubscribe .pcell {
    display: table-cell;
    float:left;
    background-color:#e3e2e2;
    border-radius: 15px 15px 15px 15px;
    border: 1px solid grey;
    padding: 1em;
    margin-right: 5px;
}
.podcastheader h3{
    display:table-header;
    text-align:center;
}

.podcastheader{
    font-weight: bold;
}

.podcastlinks{
    display: inline;

}

.fltlft {
  float:left;
  padding-right: 5px;
}

/* Listing Page Items */
#subscribelinks {

}

div.listingfooter ul li {
    list-style: none outside none;
}

';

			if (JBSMDbHelper::fixupcss('biblestudy', true, $new710css, null))
			{
				$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
				$db->setQuery($query);
				$result = $db->loadObject();
				JBSMDbHelper::reloadtable($result, 'Style');
				self::setemptytemplates();

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$query = 'SELECT * FROM #__bsms_styles WHERE `filename` = "biblestudy"';
			$db->setQuery($query);
			$result = $db->loadObject();

			if (!$result)
			{
				$objects            = new stdClass;
				$objects->id        = null;
				$objects->published = 1;
				$objects->filename  = 'biblestudy';
				$objects->stylecode = $db->escape($newCSS);
				$objects->asset_id  = 0;

				if (!$db->insertObject('#__bsms_styles', $objects, 'id'))
				{
					$app->enqueueMessage(JText::sprintf('JBS_INS_SQL_UPDATE_ERRORS', $db->stderr(true)), 'warning');

					return false;
				}
				$query = $db->getQuery(true);
				$query->select('*')->from('#__bsms_styles')->where($db->qn('filename') . '=' . $db->q('biblestudy'));
				$db->setQuery($query);
				JBSMDbHelper::reloadtable($db->loadObject(), 'Style');
				self::setemptytemplates();
				$app->enqueueMessage('No CSS files where found so loaded default css info', 'notice');

				return true;
			}
		}

		return true;
	}

	/**
	 * Set Empty Templates
	 *
	 * @return void
	 */
	public static function setemptytemplates()
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__bsms_templates');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as $result)
		{
			// Store new Recorde so it can be seen.
			JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
			$table = JTable::getInstance('Template', 'Table', array('dbo' => $db));

			try
			{
				$table->load($result->id);
				$table->store();
				$table->load($result->id);
				$registry = new JRegistry;
				$registry->loadString($table->params);
				$css = $registry->get('css');
				$registry->set('css', 'biblestudy.css');

				// Now write the params back into the $table array and store.
				$table->params = (string) $registry->toString();
				$table->store();
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage('Caught exception: ' . $e->getMessage(), 'warning');
			}
		}
	}

}