<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('_JEXEC') or die;

/**
 * Update for 9.1.5 class
 *
 * @package  Proclaim.Admin
 * @since    9.1.5
 */
class Migration915
{
	/**
	 * Call Script for Updates of 9.0.1
	 *
	 * @param   JDatabaseDriver  $db  Joomla Data bass driver
	 *
	 * @return bool
	 *
	 * @since 9.0.1
	 */
	public function up($db)
	{
		$this->newCompParams();

		return true;
	}

	/**
	 * Update Component Params
	 *
	 * @since 9.1.5
	 *
	 * @return void;
	 */
	protected function newCompParams()
	{
		JBSMParams::setCompParams(
			[
				'upload_extensions' => 'key,pps,pptx,docx,aac,m4a,f4a,mp3,ogg,oga,mp4,m4v,f4v,mov,flv,webm,m3u8,mpd,DVR,' .
					'bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,TXT,XCF,XLS',
				'upload_maxsize' => '10',
				'restrict_uploads' => '1',
				'check_mime' => '1',
				'image_extensions' => 'mp3,mp4,m4v,mov,bmp,gif,jpg,png',
				'ignore_extensions' => '',
				'upload_mime' => 'application/x-iwork-keynote-sffkey,application/vnd.openxmlformats-officedocument.presentationml.presentation,' .
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document,audio/aac,audio/mp4,audio/ogg,audio/mpeg,' .
					'audio/x-wav,application/annodex,application/mp4,application/ogg,application/vnd.rn-realmedia,application/x-matroska,' .
					'video/3gpp,video/3gpp2,video/annodex,video/divx,video/flv,video/h264,video/mp4,video/mp4v-es,video/mpeg,video/mpeg-2,' .
					'video/mpeg4,video/ogg,video/ogm,video/quicktime,video/ty,video/vdo,video/vivo,video/vnd.rn-realvideo,video/vnd.vivo,' .
					'video/webm,video/x-bin,video/x-cdg,video/x-divx,video/x-dv,video/x-flv,video/x-la-asf,video/x-m4v,video/x-matroska,' .
					'video/x-motion-jpeg,video/x-ms-asf,video/x-ms-dvr,video/x-ms-wm,video/x-ms-wmv,video/x-msvideo,video/x-sgi-movie,video/x-tivo,' .
					'video/avi,video/x-ms-asx,video/x-ms-wvx,video/x-ms-wmx,,image/jpeg,image/gif,image/png,image/bmp,application/x-shockwave-flash,' .
					'application/msword,application/excel,application/pdf,application/powerpoint,text/plain,application/x-zip',
				'upload_mime_illegal' => 'text/html'
			]
		);
	}
}
