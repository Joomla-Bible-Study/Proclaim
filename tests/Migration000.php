<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace Test\Migration000;

// phpcs:disable PSR1.Files.SideEffects
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Update for 0.0.0 class
 *
 * @package  Proclaim.Admin
 * @since    0.0.0
 */
class Migration000
{
    /**
     * Call Script for Updates of 0.0.0
     *
     * @param   DatabaseDriver  $dbo  Joomla Database driver
     *
     * @return bool
     *
     * @since 0.0.0
     */
    public function up(DatabaseDriver $dbo): bool
    {
        $this->deleteUnexistingFiles();

        return true;
    }

    /**
     * Remove Old Files and Folders
     *
     * @since      0.0.0
     *
     * @return   void
     */
    protected function deleteUnexistingFiles(): void
    {
        $path = [
            BIBLESTUDY_PATH_ADMIN . '/models/style.php',
        ];

        foreach ($path as $file) {
            if (File_exists($file)) {
                File::delete($file);
            }
        }

        $folders = [
            BIBLESTUDY_PATH_ADMIN . '/views/styles'];

        foreach ($folders as $folder) {
            if (file_exists($folder)) {
                Folder::delete($folder);
            }
        }
    }
}
