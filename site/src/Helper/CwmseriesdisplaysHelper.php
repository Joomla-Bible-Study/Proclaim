<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Shared item-prep logic for series-display listings.
 *
 * @package  Proclaim.Site
 * @since    10.5.6
 */
class CwmseriesdisplaysHelper
{
    /**
     * Prepare raw series rows for rendering: slug/link generation, thumbnail
     * and teacher-image resolution, and content-plugin processing on the
     * description. Shared by CwmseriesdisplaysController::paginateAjax() and
     * Cwmseriesdisplays HtmlView::display() so both entry points render
     * identically.
     *
     * @param   array          $items     Raw series rows from CwmseriesdisplaysModel::getItems()
     * @param   Registry|null  $params    Template params (passed to content plugins); may be
     *                                    absent when called outside the normal display() flow
     * @param   object|null    $template  Active template row (only ->id is used)
     *
     * @return  array  The same $items, mutated in place
     *
     * @since   10.5.6
     */
    public static function prepareItems(array $items, ?Registry $params, ?object $template): array
    {
        $pagebuilder = new Cwmpagebuilder();

        foreach ($items as $item) {
            $item->slug  = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
                . str_replace(' ', '-', htmlspecialchars_decode($item->series_text, ENT_QUOTES));
            $seriesimage = Cwmimages::getSeriesThumbnail($item->series_thumbnail);

            if ($seriesimage->path) {
                $item->image = Cwmimages::renderPicture($seriesimage, $item->series_text ?? '');
            }

            $item->serieslink = Route::_(
                'index.php?option=com_proclaim&view=cwmseriesdisplay&id=' . $item->slug . '&t=' . ($template?->id ?? '')
            );
            $teacherimage     = Cwmimages::getTeacherImage($item->thumb ?? '');

            if ($teacherimage->path) {
                $item->teacherimage = Cwmimages::renderPicture($teacherimage, $item->teachername ?? '');
            }

            if (isset($item->description)) {
                $item->text        = $item->description;
                $description       = $pagebuilder->runContentPlugins($item, $params);
                $item->description = $description->text;
            }
        }

        return $items;
    }
}
