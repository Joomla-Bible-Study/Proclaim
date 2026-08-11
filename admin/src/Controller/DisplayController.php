<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;

/**
 * Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'cwmcpanel';

    /**
     * The extension for which the categories apply.
     *
     * @var    string
     * @since  1.6
     */
    protected mixed $extension;

    /**
     * Constructor.
     *
     * @param   array                     $config    An optional associative array of configuration settings.
     *                                               Recognized key values include 'name', 'default_task',
     *                                               'model_path', and 'view_path' (this list is not meant to be
     *                                               comprehensive).
     * @param   ?MVCFactoryInterface      $factory   The factory.
     * @param   ?CMSApplicationInterface  $app       The Application for the dispatcher
     * @param   ?Input                    $input     Input
     *
     * @since   7.0.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplicationInterface $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Guess the Text message prefix. Defaults to the option.
        if (empty($this->extension)) {
            $this->extension = $this->input->get('extension', 'com_proclaim');
        }
    }

    /**
     * Where a refused edit request goes back to, keyed by edit view.
     *
     * Routing only; which section governs an entity is
     * `Cwmassets::sectionForView()`.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
     */
    private const EDIT_VIEW_LISTS = [
        'cwmcomment'      => 'cwmcomments',
        'cwmlocation'     => 'cwmlocations',
        'cwmmediafile'    => 'cwmmediafiles',
        'cwmmessage'      => 'cwmmessages',
        'cwmmessagetype'  => 'cwmmessagetypes',
        'cwmplaylist'     => 'cwmplaylists',
        'cwmpodcast'      => 'cwmpodcasts',
        'cwmserie'        => 'cwmseries',
        'cwmserver'       => 'cwmservers',
        'cwmteacher'      => 'cwmteachers',
        'cwmtemplate'     => 'cwmtemplates',
        'cwmtemplatecode' => 'cwmtemplatecodes',
        'cwmtopic'        => 'cwmtopics',
    ];

    /**
     * Method to display a view.
     *
     * @param   bool   $cachable   If true, the view output will be cached
     * @param   array  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link FilterInput::clean()}.
     *
     * @return  static  This object to support chaining.
     *
     * @throws \Exception
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = []): static
    {
        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', 'cwmcpanel');
        $lName   = $this->input->get('layout', 'default', 'string');
        $id      = $this->input->getInt('id');

        // Check for an edit form.
        if ($lName === 'edit' && isset(self::EDIT_VIEW_LISTS[$vName])) {
            $redirect = Route::_('index.php?option=com_proclaim&view=' . self::EDIT_VIEW_LISTS[$vName], false);
            $section  = Cwmassets::sectionForView($vName);

            // A task-less GET never reaches the entity controller, so
            // allowAdd()/allowEdit() do not run. Without this the form renders
            // for a section the user is denied, and only refuses on save.
            if ($section !== '' && !$this->app->getIdentity()->authorise($id ? 'core.edit' : 'core.create', 'com_proclaim.' . $section)) {
                $this->setMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
                $this->setRedirect($redirect);

                return $this;
            }

            if (!$this->checkEditId('com_proclaim.edit.' . $vName, $id)) {
                // Somehow the person just went to the form - we don't allow that.
                if (!\count($this->app->getMessageQueue())) {
                    $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
                }

                $this->setRedirect($redirect);

                return $this;
            }
        }

        $safeurlparams = [
            'catid'            => 'INT',
            'id'               => 'INT',
            'cid'              => 'ARRAY',
            'year'             => 'INT',
            'month'            => 'INT',
            'limit'            => 'UINT',
            'limitstart'       => 'UINT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter'           => 'STRING',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter-search'    => 'STRING',
            'print'            => 'BOOLEAN',
            'lang'             => 'CMD',
        ];

        return parent::display($cachable, $safeurlparams);
    }
}
