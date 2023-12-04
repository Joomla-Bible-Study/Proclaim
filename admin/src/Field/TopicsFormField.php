<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormField;

/**
 * Form Field class for the Topics
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class TopicsFormField extends FormField
{
    /**
     * Set type to topics
     *
     * @var string
     *
     * @since 9.0.0
     */
    public $type = 'TopicsForm';

    /**
     * Get input form
     *
     * @return string
     *
     * @since 9.0.0
     */
    protected function getInput(): string
    {
        return '<input type="hidden" id="topics" name="jform[topics]"/>';
    }
}
