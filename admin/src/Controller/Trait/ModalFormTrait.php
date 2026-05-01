<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller\Trait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Router\Route;

/**
 * Modal form handling for FormController subclasses.
 *
 * Provides cancel and post-save redirects for modal layout context
 * (ModalSelectField Create/Edit buttons). When layout=modal:
 * - Cancel → redirects to modalreturn layout
 * - Save   → redirects to modalreturn (sends PostMessage to parent)
 * - Apply  → stays in modal layout with tmpl=component
 *
 * Usage: `use ModalFormTrait;` in a FormController subclass.
 * Call `$this->handleModalCancel($result)` from cancel() and
 * `$this->handleModalPostSave($id)` from postSaveHook().
 *
 * @since  10.3.0
 */
trait ModalFormTrait
{
    /**
     * Handle cancel redirect for modal layout.
     *
     * Call from cancel() after parent::cancel(). Returns true if handled
     * (modal redirect set), false if not in modal context.
     *
     * @param   bool  $result  The result from parent::cancel()
     *
     * @return  bool  True if modal redirect was set
     *
     * @since   10.3.0
     */
    protected function handleModalCancel(bool $result): bool
    {
        if (!$result || $this->input->get('layout') !== 'modal') {
            return false;
        }

        $id     = $this->input->get('id');
        $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item
            . $this->getRedirectToItemAppend($id) . '&layout=modalreturn&from-task=cancel';

        $this->setRedirect(Route::_($return, false));

        return true;
    }

    /**
     * Handle post-save redirect for modal layout.
     *
     * Call from postSaveHook(). Returns true if handled (modal redirect set),
     * false if not in modal context. Caller can then proceed with non-modal logic.
     *
     * @param   int  $id  The saved record ID
     *
     * @return  bool  True if modal redirect was set
     *
     * @since   10.3.0
     */
    protected function handleModalPostSave(int $id): bool
    {
        if ($this->input->get('layout') !== 'modal') {
            return false;
        }

        if ($this->task === 'save') {
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend($id) . '&layout=modalreturn&from-task=save';
            $this->setRedirect(Route::_($return, false));
        } elseif ($this->task === 'apply') {
            $return = 'index.php?option=' . $this->option . '&task=' . $this->view_item . '.edit'
                . $this->getRedirectToItemAppend($id) . '&layout=modal&tmpl=component';
            $this->setRedirect(Route::_($return, false));
        }

        return true;
    }
}
