<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// No Direct Access

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use Joomla\Registry\Registry;

/**
 * Class custom helper
 *
 * @package  Proclaim.Site
 * @since    8.0.0
 * */
class Cwmcustom
{
    /**
     * Get Custom page
     *
     * @param   int                      $rowid     ID of Row
     * @param   string                   $custom    Custom String
     * @param   object                   $row       Row info
     * @param   Registry                 $params    Params for intro
     * @param   CwmtemplateTable|Object  $template  Template ID
     *
     * @return string
     *
     * @throws \Exception
     * @since    8.0.0
     */
    public function getCustom($rowid, $custom, $row, $params, $template): string
    {
        $isCustom     = $rowid === 24;
        $JBSMElements = new Cwmlisting();

        $custom = preg_replace_callback('/{([^}]+)}/', function ($matches) use ($rowid, $isCustom, $row, $params, $template, $JBSMElements) {
            $subcustom  = $matches[1];
            $localRowId = $rowid;

            if (!$localRowId || $isCustom) {
                $localRowId = self::getElementnumber($subcustom);
            }

            return (string) $JBSMElements->getElement((string) $localRowId, $row, $params, $template, '0');
        }, (string) $custom);

        return (string) $custom;
    }

    /**
     * Get Element Number.
     *
     * @param   string  $row  Row ID
     *
     * @return int
     *
     * @since    8.0.0
     */
    public static function getElementnumber($row): int
    {
        $lookup = [
            'scripture1'         => 1,
            'scripture2'         => 2,
            'secondary'          => 3,
            'duration'           => 4,
            'studytitle'         => 5,
            'studyintro'         => 6,
            'teachername'        => 7,
            'teacher-title-name' => 8,
            'teacher-image'      => 30,
            'series_text'        => 9,
            'date'               => 10,
            'submitted'          => 11,
            'hits'               => 12,
            'studynumber'        => 13,
            'topic_text'         => 14,
            'location_text'      => 15,
            'message_type'       => 16,
            'details-text'       => 17,
            'details-text-pdf'   => 18,
            'details-pdf'        => 19,
            'media'              => 20,
            'store'              => 22,
            'filesize'           => 23,
            'thumbnail'          => 25,
            'series_thumbnail'   => 26,
            'series_description' => 27,
            'plays'              => 28,
            'downloads'          => 29,
        ];

        return $lookup[$row] ?? 0;
    }
}
