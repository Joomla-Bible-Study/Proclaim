<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No direct access
defined('_JEXEC') or die();

/**
 * Directory View
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
class BiblestudyViewDir extends JViewLegacy
{
	public $folders;

	public $breadcrumbs;

	public $files;

	public $imgURL;

	public $currentFolder;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since    7.0.0
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$mediaDir = JUri::root() . "media/com_biblestudy/";

		// Add style sheet
		$document->addStyleSheet($mediaDir . 'css/com_biblestudy.css');
		$bodyStyle = "body {background-color: #F4F4F4;}";
		$document->addStyleDeclaration($bodyStyle);

		// Add scripts
		$document->addScript($mediaDir . 'js/jquery.min.js');
		$document->addScript($mediaDir . 'js/jquery.tooltip.js');
		$script = $this->_dirBrowserScript();
		$document->addScriptDeclaration($script);

		// Setup template vars
		$this->breadcrumbs   = $this->get('Breadcrumbs');
		$this->folders       = $this->get('Folders');
		$this->files         = $this->get('Files');
		$this->imgURL        = $mediaDir . 'img/';
		$this->currentFolder = base64_encode($this->folders[0]->fullPath);

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Dir Browser Script
	 *
	 * @return string
	 *
	 * @since    7.0.0
	 */
	private function _dirBrowserScript()
	{
		ob_start();
		?>
		$.noConflict();

		jQuery(document).ready(function ($) {

		$('input.delete').attr('checked', false);

		function ajaxReq(dataString, action) {
		$('span#proccess').addClass('loading');
		$.ajax({
		type: 'POST',
		url: action,
		data: dataString,
		dataType : 'json',
		success: function(response) {
		$('span#proccess').removeClass('loading');

		if(response.error) {
		var msgCont = $('#system-message-container');
		var msgHTML = '';
		//clean container
		msgCont.html(' ');

		msgHTML+= '
		<dl id="system-message">';
			msgHTML+= '
			<dt class="error">Error</dt>
			';
			msgHTML+= '
			<dd class="error message">';
				msgHTML+= '
				<ul>
					<li>' + response.msg + '</li>
				</ul>
				';
				msgHTML+= '
			</dd>
			';
			msgHTML+= '
		</dl>';

		msgCont.html(msgHTML);
		} else {
		window.location.reload();
		}
		}
		});
		}

		function rmPath( fileName ) {

		var action = 'index.php?option=com_biblestudy&no_html=1&task=path.delete';
		var currentFolder = $('input#current_folder').val();
		var token = '<?php echo JSession::getFormToken() ?>';
		var dataString = '';

		dataString+= 'paths[]=' + fileName + '&';
		dataString+= 'current_folder=' + currentFolder + '&';
		dataString+=  token + '=1';

		ajaxReq(dataString, action);

		}

		$('#new_folder').click(function () {
		$('#folder_input_form').slideToggle('slow');
		});

		$('#input_form').submit(function () {
		var folder_name = $('input#folder_name').val();

		if(folder_name == '') {
		alert('Enter a name for new folder');
		} else {
		var dataString = $('#input_form').serialize();
		var action = 'index.php?option=com_biblestudy&no_html=1&task=folder.create';
		ajaxReq(dataString, action);
		}
		return false;
		});

		$('a#select').click(function() {
		var elClass = this.className;
		if(elClass == 'select') {
		this.className = 'deselect';
		$('input.delete').attr('checked', true);
		} else {
		this.className = 'select';
		$('input.delete').attr('checked', false);
		}
		});

		//create tooltip
		$('a.finfo').mouseover(function() {
		var elId = this.id;

		$("a#" + elId + "").tooltip({
		tip : "div." + elId + "",
		delay : 20,
		position : 'right right',
		offset: [80, 3]
		})
		});

		$('a.path_rm_btn').click(function() {
		var cnfTxt = '<?php echo JText::_('JBSM_DIR_BROSWER_CONFIRM_DELETE'); ?>';
		if(confirm(cnfTxt)) {
		rmPath( this.name );
		} else {
		return false;
		}
		});
		});

		<?php
		$script = ob_get_contents();
		ob_clean();

		return $script;
	}
}
