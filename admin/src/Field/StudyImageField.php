<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\MediaField;
use Joomla\CMS\Form\FormHelper;

/**
 * Location List Form Field class for the Proclaim component
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class StudyImageField extends MediaField
{
    /**
     * @var string
     * @since 7.0.0
     */
    protected $type = 'StudyImageField';

    /**
     *
     * @return string
     *
     * @since 7.0.0
     */
    public function getInput(): string
    {
        // Code that returns HTML that will be shown as the form field
        $form = FormHelper::loadFieldClass('media');

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__extensions')
            ->where('name = "plg_filesystem_local"');
        $db->setQuery($query);
        $local   = $db->loadObject();
        $pparams = $local->params;
        $ismedia = substr_count($pparams, 'media');

        if ($ismedia !== 1) {
            if ($pparams === '{}' || is_null($pparams)) {
                $newmedia = '{"directories":{"directories0":{"directory":"media"}}}';
            } else {
                $dircount  = substr_count($pparams, 'directory');
                $end       = strlen($pparams);
                $getstring = substr($pparams, 0, $end - 2);
                $newmedia  = $getstring . ', "directories' . $dircount . '":{"directory":"media"}}}';
            }

            $newmedia = addslashes($newmedia);

            $query = $db->getQuery(true);
            $query->update('#__extensions')
                ->set('params = "' . $newmedia . '"')
                ->where('name = "plg_filesystem_local"');
            $db->setQuery($query);
            $db->execute();
        }

        return parent::getInput();
    }
}
