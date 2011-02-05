<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs613Install{
    
function upgrade613()
{
$result_table = '<table><tr><td>This routine updaters the database to reflect changes to the way the media player is accessed. If no mediafile records are indicated, then no changes were needed. CSS is also added to support the Landing Page view.</td></tr>';

			$query = "CREATE TABLE IF NOT EXISTS `#__bsms_admin` (
					  `id` int(11) NOT NULL,
					  `podcast` text,
					  `series` text,
					  `study` text,
					  `teacher` text,
					  `media` text,
					  `download` text,
					  `main` text,
					  `showhide` char(255) DEFAULT NULL,
					  `params` text,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

			$query = "CREATE TABLE IF NOT EXISTS `#__bsms_share` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(250) DEFAULT NULL,
				  `params` text,
				  `published` tinyint(1) NOT NULL DEFAULT '1',
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

			$query = "CREATE TABLE IF NOT EXISTS `#__bsms_templates` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `type` varchar(255) NOT NULL,
				  `tmpl` longtext NOT NULL,
				  `published` int(1) NOT NULL DEFAULT '1',
				  `params` longtext,
				  `title` text,
				  `text` text,
				  `pdf` text,
				  PRIMARY KEY (`id`)
				  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

				  $query = "INSERT INTO `#__bsms_admin` VALUES (1, '', '', '', '', 'speaker24.png', 'download.png', 'openbible.png', '0', 'compat_mode=0 drop_tables=0 admin_store=1 studylistlimit=10 popular_limit=1 series_imagefolder= media_imagefolder= teachers_imagefolder= study_images= podcast_imagefolder= location_id= teacher_id= series_id= booknumber= topic_id= messagetype= avr=0 download= target= server= path= podcast=0 mime=0 allow_entry_study=0 entry_access=23 study_publish=0 socialnetworking=1')";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

					$query = "INSERT INTO `#__bsms_share` (`id`, `name`, `params`, `published`) VALUES 
	(NULL, 'FaceBook', 'mainlink=http://www.facebook.com/sharer.php? item1prefix=u= item1=200 item1custom= item2prefix=t= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4=8 item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/facebook.png shareimageh=33px shareimagew=33px totalcharacters= alttext=FaceBook  ', 1), 
	(NULL, 'Twitter', 'mainlink=http://twitter.com/home? item1prefix=status= item1=200 item1custom= item2prefix= item2=5 item2custom= item3prefix= item3=1 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/twitter.png shareimagew=33px shareimageh=33px totalcharacters=140 alttext=Twitter', 1), 
	(NULL, 'Delicious', 'mainlink=http://delicious.com/save? item1prefix=url= item1=200 item1custom= item2prefix=&amp;title= item2=5 item2custom= item3prefix= item3=6 item3custom= item4prefix= item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/delicious.png shareimagew=33px shareimageh=33px totalcharacters= alttext=Delicious', 1),
	(NULL, 'MySpace', 'mainlink=http://www.myspace.com/index.cfm? item1prefix=fuseaction=postto&amp;t= item1=5 item1custom= item2prefix=&amp;c= item2=6 item2custom= item3prefix=&amp;u= item3=200 item3custom= item4prefix=&amp;l=1 item4= item4custom= use_bitly=0 username= api= shareimage=components/com_biblestudy/images/myspace.png\nshareimagew=33px\nshareimageh=33px\ntotalcharacters=\nalttext=MySpace', 1)";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

			$query = "INSERT INTO `#__bsms_templates` VALUES (1, 'tmplList', '', 1, 'itemslimit=10\n compatibilityMode=0\n studieslisttemplateid=1\n detailstemplateid=1\n teachertemplateid=1\n serieslisttemplateid=1\n seriesdetailtemplateid=1\n teacher_id=\n show_teacher_list=0\n series_id=0\n booknumber=0\n topic_id=0\n messagetype=0\n locations=0\n default_order=DESC\n show_page_image=1\n tooltip=1\n show_verses=0\n stylesheet=\n date_format=2\n duration_type=1\n useavr=0\n popuptype=window\n media_player=0\n player_width=290\n show_filesize=1\n store_page=flypage.tpl\n show_page_title=1\n page_title=Bible\n Studies\n use_headers_list=1\n list_intro=\n intro_show=1\n listteachers=1\n teacherlink=1\n details_text=Study\n Details\n show_book_search=1\n show_teacher_search=1\n show_series_search=1\n show_type_search=1\n show_year_search=1\n show_order_search=1\n show_topic_search=1\n show_locations_search=1\n show_popular=1\n tip_title=Sermon\n Information\n tip_item1_title=Title\n tip_item1=5\n tip_item2_title=Details\n tip_item2=6\n tip_item3_title=Teacher\n tip_item3=7\n tip_item4_title=Reference\n tip_item4=1\n tip_item5_title=Date\n tip_item5=10\n row1col1=18\n r1c1custom=\n r1c1span=1\n rowspanr1c1=1\n linkr1c1=0\n row1col2=5\n r1c2custom=\n r1c2span=1\n rowspanr1c2=1\n linkr1c2=1\n row1col3=1\n r1c3custom=\n r1c3span=1\n rowspanr1c3=1\n linkr1c3=0\n row1col4=20\n r1c4custom=\n rowspanr1c4=1\n linkr1c4=0\n row2col1=6\n r2c1custom=\n r2c1span=4\n rowspanr2c1=1\n linkr2c1=0\n row2col2=0\n r2c2custom=\n r2c2span=1\n rowspanr2c2=1\n linkr2c2=0\n row2col3=0\n r2c3custom=\n r2c3span=1\n rowspanr2c3=1\n linkr2c3=0\n row2col4=0\n r2c4custom=\n rowspanr2c4=1\n linkr2c4=0\n row3col1=0\n r3c1custom=\n r3c1span=1\n rowspanr3c1=1\n linkr3c1=0\n row3col2=0\n r3c2custom=\n r3c2span=1\n linkr3c2=0\n row3col3=0\n r3c3custom=\n r3c3span=1\n rowspanr3c3=1\n linkr3c3=0\n row3col4=0\n r3c4custom=\n rowspanr3c4=1\n linkr3c4=0\n row4col1=0\n r4c1custom=\n r4c1span=1\n rowspanr4c1=1\n linkr4c1=0\n row4col2=0\n r4c2custom=\n r4c2span=1\n rowspanr4c2=1\n linkr4c2=0\n row4col3=0\n r4c3custom=\n r4c3span=1\n rowspanr4c3=1\n linkr4c3=0\n row4col4=0\n r4c4custom=\n rowspanr4c4=1\n linkr4c4=0\n show_print_view=1\n show_pdf_view=1\n show_teacher_view=1\n show_passage_view=1\n use_headers_view=1\n list_items_view=0\n title_line_1=1\n customtitle1=\n title_line_2=4\n customtitle2=\n view_link=1\n link_text=Return\n to\n Studies\n List\n show_scripture_link=1\n show_comments=0\n comment_access=1\n comment_publish=0\n use_captcha=1\n email_comments=1\n recipient=\n subject=Comments\n on\n studies\n body=Comments\n entered.\n moduleitems=3\n teacher_title=Our\n Teachers\n show_teacher_studies=1\n studies=5\n label_teacher=Latest\n Messages\n teacherlink=1\n series_title=Our\n Series\n show_series_title=1\n show_page_image_series=1\n series_show_description=1\n series_characters=\n search_series=1\n series_limit=5\n serieselement1=1\n seriesislink1=1\n serieselement2=1\n seriesislink2=1\n serieselement3=1\n seriesislink3=1\n serieselement4=1\n seriesislink4=1\n series_detail_sort=1\n series_detail_order=DESC\n series_detail_show_link=1\n series_detail_limit=\n series_list_return=1\n series_detail_1=5\n series_detail_islink1=1\n series_detail_2=7\n series_detail_islink2=0\n series_detail_3=10\n series_detail_islink3=0\n series_detail_4=20\n series_detail_islink4=0', 'Default','textfile24.png','pdf24.png')";
$msg = $this->performdb($query);
$msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN thumbnailm TEXT NULL AFTER studytext";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN thumbhm INT NULL AFTER thumbnailm";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN thumbwm INT NULL AFTER thumbhm";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_studies ADD COLUMN params TEXT NULL AFTER thumbwm";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_podcast ADD COLUMN episodetitle INT NULL AFTER published";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_podcast ADD COLUMN custom VARCHAR( 200 ) NULL AFTER episodetitle";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_podcast ADD COLUMN detailstemplateid INT NULL AFTER custom";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_series ADD COLUMN series_thumbnail VARCHAR(150) NULL AFTER series_text";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_series ADD COLUMN description TEXT NULL AFTER series_text";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_series ADD COLUMN teacher INT(3) NULL AFTER series_text";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_media ADD COLUMN path2 VARCHAR(150) NOT NULL AFTER media_image_path";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "INSERT INTO #__bsms_media SET `media_text` = 'Article',`media_image_name` = 'Article',`path2` = 'textfile24.png', `media_alttext` = 'Article',`published` = '1'";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "INSERT INTO #__bsms_media SET `media_text` = 'Download',`media_image_name` = 'Download',`path2` = 'download.png', `media_alttext` = 'Download',`published` = '1'";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN teacher_thumbnail TEXT NULL AFTER id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_teachers ADD COLUMN teacher_image TEXT NULL AFTER id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN docMan_id INT NULL AFTER published";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN article_id INT NULL AFTER docMan_id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN comment TEXT NULL AFTER article_id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN virtueMart_id INT NULL AFTER comment";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN params TEXT NULL AFTER virtueMart_id";
            $msg = $this->performdb($query);
            $msg2 = $msg2.$msg;
			

$result_table .= $msg2;

//Read current css file, add share information if not already there, write and close
jimport('joomla.filesystem.file');
$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';


//Now we are going to update the db. We no longer use the field for AVR but it happens in a param so we need to get rid of the internal_viewer after setting the param accordingly
$database = &JFactory::getDBO();
$database->setQuery("UPDATE #__bsms_mediafiles SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL");
	$database->query();
	if ($database->getErrorNum() > 0)
			{
				$error = $database->getErrorMsg();
				$result_table .= '<tr><td>An error occured while updating mediafiles table: '.$error.'</td></tr>';
			}
	else
	{
		$result = $database->getAffectedRows();
		if ($result < 1)
		{
			$result_table .= '<tr><td>No Media File records found that needed updating</td></tr>';
		}
		else
		{
			$result_table .= '<tr><td>'.$result.' Mediafiles records updated</td></tr>';
		}
		
	}
//All Videos Reloaded has a problem with Bible Study. If there is no Itemid (like from the module) then AVR will break with Popup Database Error. We created a special file for the popup view.html.php file and we copy it over, backing up the old one. It will be reinstated on a full uninstall of Bible Study
$src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'avr'.DS.'view.html.php';
$dest = JPATH_SITE.DS.'components'.DS.'com_avreloaded'.DS.'views'.DS.'popup'.DS.'view.html.php';
$avrbackup = JPATH_SITE.DS.'components'.DS.'com_avreloaded'.DS.'views'.DS.'popup'.DS.'view2.html.php';
$avrexists = JFile::exists($dest);
if ($avrexists)
	{
		$avrread = JFile::read($dest);
		$isbsms = substr_count($avrread,'JoomlaBibleStudy'); 
		if (!$isbsms)
		{
			JFile::copy($dest, $avrbackup);
			JFile::copy($src, $dest);
			$result_table .= '<tr><td>AVR Edited File installed</td></tr>';
		}
		
	}
//Now we look inside the css to see if there are share items, if not, we'll add them


	$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
    $src = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css.dist';
    $cssexists = JFile::exists($dest);
    if (!$cssexists) {JFile::copy($src, $dest);}
	$shareread = JFile::read($dest);
    
	$shareexists = 1;
	$shareexists = substr_count($shareread,'#bsmsshare'); 
	if ($shareexists < 1)
	{
		$csssharecode = '
		/*Social Networking Items */
		#bsmsshare {
		  margin: 0;
		  border-collapse:separate;
		  float:right;
		  border: 1px solid #CFCFCF;
		  background-color: #F5F5F5;
		}
		#bsmsshare th, #bsmsshare td {
		  text-align:center;
		  padding:0 0 0 0;
		  border:none;
		}
		#bsmsshare th {
			color:#0b55c4;
			font-weight:bold;
		}';
			$sharewrite = $shareread.$csssharecode;
			if (!JFile::write($dest, $sharewrite))
			{
				$result_table .= '<tr><td>There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org</td></tr>';
			}
			else
			{
				$result_table .= '<tr><td>Social Networking css code written to file /assets/css/biblestudy.css </td></tr>';
			}
	}


	$result_table .= '</table>';
	return $result_table;
 }
 
 function performdb($query)
    {
        $db = JFactory::getDBO();
        $results = '';
        if (!$query){$results = "Error. No query found"; return $results;}
        $db->setQuery($query);
        $db->query();
        
        		if ($db->getErrorNum() != 0)
					{
						$error = "DB function failed with error number ".$db->getErrorNum()."<br /><font color=\"red\">";
						$error .= $db->stderr(true);
						$error .= "</font>";
					}
					else
					{
						$error = "";
						
					}
                    $results .= '<tr><td><div >'.$error.'<pre>';
                    $results .= $query.'</pre></div></td>';
       return $results;
    }
}
?>