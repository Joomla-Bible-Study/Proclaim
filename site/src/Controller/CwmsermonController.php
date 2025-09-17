<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Controller;

use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use PHPMailer\PHPMailer\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Class for Sermon
 *
 * @package  Proclaim.Site
 * @since    7.0.0
 */
class CwmsermonController extends FormController
{
    /**
     * View item
     *
     * @var string
     * @since    1.6
     */
    protected $view_item = 'cwmsermon';

    /**
     * View list
     * @var string
     * @since    1.6
     */
    protected $view_list = 'cwmsermons';

    /**
     * Method to add a new record.
     *
     * @return    void  True if the article can be added, false if not.
     *
     * @throws \Exception
     * @since    1.6
     */
    public function add(): void
    {
        if (!parent::add()) {
            // Redirect to the return page.
            $this->setRedirect($this->getReturnPage());
        }
    }

    /**
     * Get the return URL.
     *
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function getReturnPage(): string
    {
        $return = Factory::getApplication()->input->get('return', null, 'base64');

        if (empty($return) || !Uri::isInternal(base64_decode($return))) {
            return Uri::base() . 'index.php?option=com_proclaim&view=cwmsermon';
        }

        return base64_decode($return);
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return    void  True if access level checks pass, false otherwise.
     *
     * @throws \Exception
     * @since    1.6
     */
    public function cancel($key = 'a_id'): void
    {
        parent::cancel($key);

        // Redirect to the return page.
        $this->setRedirect($this->getReturnPage());
    }

    /**
     * Method to edit an existing record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    boolean    True if access level check and checkout passes, false otherwise.
     *
     * @since    1.6
     */
    public function edit($key = null, $urlVar = 'a_id'): bool
    {
        return parent::edit($key, $urlVar);
    }

    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return    bool    True if successful, false otherwise.
     *
     * @throws \Exception
     * @since    1.6
     */
    public function save($key = null, $urlVar = 'a_id'): bool
    {
        $result = parent::save($key, $urlVar);

        // If ok, redirect to the return page.
        if ($result) {
            $this->setRedirect($this->getReturnPage());
        }

        return $result;
    }

    /**
     * Comment
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0
     */
    public function comment(): void
    {
        $input = Factory::getApplication()->input;
        /** @var CwmsermonModel $model */
        $model = $this->getModel('sermon');
        $t     = $input->get('t', '1');
        $input->set('t', $t);

        // Convert parameter fields to objects.
        $registry = new Registry();
        $registry->loadString($model->_template[0]->params);
        $params = $registry;

        if ($params->get('use_captcha') > 0) {
            // Begin reCaptcha
            $data = $input->post;
            PluginHelper::importPlugin('captcha');
            $res = Factory::getApplication()->triggerEvent('onCheckAnswer', $_POST['recaptcha_response_field']);

            if (!$res[0]) {
                // What happens when the CAPTCHA was entered incorrectly
                $mess = Text::_('JBS_STY_INCORRECT_KEY');
                echo "<script type='text/javascript'>alert('" . $mess . "')</script>";
                echo "<script type='text/javascript'>window.parent.location.reload()</script>";

                return;
            }
        }

        if ($model->storecomment()) {
            $msg = Text::_('JBS_STY_COMMENT_SUBMITTED');
        } else {
            $msg = Text::_('JBS_STY_ERROR_SUBMITTING_COMMENT');
        }

        if ($params->get('email_comments') > 0) {
            $this->commentsEmail($params);
        }

        $study_detail_id = $input->get('study_detail_id', 0, 'int');

        Factory::getApplication()->redirect(
            Route::_(
                'index.php?option=com_proclaim&id=' . $study_detail_id . '&view=cwmsermon&t=' . $t . '&msg=' . $msg,
                'Comment Added'
            )
        );
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return    BaseDatabaseModel   The model.
     *
     * @since    1.5
     */
    public function getModel(
        $name = 'Cwmsermon',
        $prefix = '',
        $config = array('ignore_request' => true)
    ): BaseDatabaseModel {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Email comment out.
     *
     * @param   Registry  $params  Params of to parse
     *
     * @return void
     *
     * @throws Exception
     * @since 7.0
     */
    public function commentsEmail($params): void
    {
        $input = Factory::getApplication()->input;

        $comment_author    = $input->get('full_name', 'Anonymous', 'string');
        $comment_study_id  = $input->get('study_detail_id', 0, 'int');
        $comment_published = $input->get('published', 0, 'int');
        $comment_date      = date('Y-m-d H:i:s');
        $config            = Factory::getApplication();
        $comment_mailfrom  = $config->get('mailfrom');

        $comment_livesite = Uri::root();
        $db               = Factory::getContainer()->get('DatabaseDriver');
        $query            = $db->getQuery(true);
        $query->select('id, studytitle, studydate')->from('#__bsms_studies')->where('id = ' . (int)$comment_study_id);
        $db->setQuery($query);
        $comment_details    = $db->loadObject();
        $comment_title      = $comment_details->studytitle;
        $comment_study_date = $comment_details->studydate;
        $mail               = Factory::getMailer();
        $ToEmail            = $params->get('recipient', '');
        $Subject            = $params->get('subject', 'Comments');

        if (empty($ToEmail)) {
            $ToEmail = $comment_mailfrom;
        }

        $Body = $comment_author . ' ' . Text::_(
            'JBS_STY_HAS_ENTERED_COMMENT'
        ) . ': ' . $comment_title . ' - ' . $comment_study_date . ' ' . Text::_(
            'JBS_STY_ON'
        ) . ': ' . $comment_date;

        if ($comment_published > 0) {
            $Body .= ' ' . Text::_('JBS_STY_COMMENT_PUBLISHED');
        } else {
            $Body .= ' ' . Text::_('JBS_STY_COMMENT_NOT_PUBLISHED');
        }

        $Body .= ' ' . Text::_('JBS_STY_REVIEW_COMMENTS_LOGIN') . ': ' . $comment_livesite;
        $mail->addRecipient($ToEmail);
        $mail->setSubject($Subject . ' ' . $comment_livesite);
        $mail->setBody($Body);
        $mail->Send();
    }

    /**
     * Download system
     *
     * @return void
     *
     * @throws Exception|\Exception
     * @since 7.0
     */
    public function download(): void
    {
        $input = Factory::getApplication()->input;
        $task  = $input->get('task');
        $mid   = $input->getInt('mid');

        if ($task === 'download') {
            $downloader = new Cwmdownload();
            $downloader->download($mid);
        }
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param   array  $data  An array of input data.
     *
     * @return    bool
     *
     * @since    1.6
     */
    protected function allowAdd($data = array()): bool
    {
        $allow = null;

        if ($allow === null) {
            // In the absence of better information, revert to the component permissions.
            return parent::allowAdd();
        }

        return $allow;
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  bool
     *
     * @since    1.6
     */
    protected function allowEdit($data = array(), $key = 'id'): bool
    {
        return true;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   int     $recordId  The primary key id for the item.
     * @param   string  $urlVar    The name of the URL variable for the id.
     *
     * @return    string    The arguments to append to the redirect URL.
     *
     * @throws \Exception
     * @since    1.6
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'a_id'): string
    {
        $this->input = Factory::getApplication()->input;

        // Need to override the parent method completely.
        $tmpl   = $this->input->get('tmpl');
        $append = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        $append .= '&layout=edit';

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }
        $return = $this->getReturnPage();

        if ($return) {
            $append .= '&return=' . base64_encode($return);
        }

        return $append;
    }
}
