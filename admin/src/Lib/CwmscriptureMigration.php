<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Library\Scripture\Helper\ScriptureHelper as CwmscriptureHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

/**
 * Finishes what the schema update started.
 *
 * The flat columns this used to read are gone as of #1623. Moving the rows is
 * now done by the SQL update file, which has to run before the DROP in the same
 * file -- Joomla executes schema updates before postflight, so PHP is too late.
 * What SQL could not do is translate a book name, so the rows it writes carry an
 * empty reference_text and this fills them in.
 *
 * Called from proclaim.script.php postflight, from the Backup and Restore
 * screens, and from the control panel's data fixes. Safe to run repeatedly.
 *
 * @since  10.1.0
 */
class CwmscriptureMigration
{
    /**
     * Rows to fill per query.
     *
     * @var int
     * @since 10.1.0
     */
    private const BATCH_SIZE = 100;

    /**
     * Give every reference that has no display text one.
     *
     * @return  int  Number of references filled in
     *
     * @since  10.1.0
     */
    public static function migrate(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array($db->getPrefix() . 'bsms_study_scriptures', $db->getTableList(), true)) {
            Log::add('Scripture junction table not found, skipping migration.', Log::INFO, 'com_proclaim');

            return 0;
        }

        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'booknumber', 'chapter_begin', 'verse_begin', 'chapter_end', 'verse_end']))
            ->from($db->quoteName('#__bsms_study_scriptures'))
            ->where($db->quoteName('reference_text') . " = ''")
            ->where($db->quoteName('booknumber') . ' > 0')
            ->setLimit(self::BATCH_SIZE);

        $filled = 0;

        do {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];

            foreach ($rows as $row) {
                $text = CwmscriptureHelper::formatReference(
                    (int) $row->booknumber,
                    (int) $row->chapter_begin,
                    (int) $row->verse_begin,
                    (int) $row->chapter_end,
                    (int) $row->verse_end
                );

                if ($text === '') {
                    continue;
                }

                $update = $db->createQuery()
                    ->update($db->quoteName('#__bsms_study_scriptures'))
                    ->set($db->quoteName('reference_text') . ' = ' . $db->quote($text))
                    ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                $db->setQuery($update);
                $db->execute();

                $filled++;
            }
            // A reference the library cannot name keeps an empty text and would
            // be selected again forever, so stop once a batch fills nothing.
        } while ($rows !== [] && $filled > 0 && \count($rows) === self::BATCH_SIZE);

        if ($filled > 0) {
            Log::add('Scripture references given display text: ' . $filled, Log::INFO, 'com_proclaim');
        }

        return $filled;
    }

    /**
     * Kept so importers and older callers do not fatal.
     *
     * @param   int  $studyId  Study primary key
     *
     * @return  int  Always 0; there are no legacy columns left to migrate
     *
     * @since       __DEPLOY_VERSION__
     * @deprecated  __DEPLOY_VERSION__  Write the junction directly with CwmscriptureHelper::saveScriptures().
     */
    public static function migrateStudy(int $studyId): int
    {
        return 0;
    }
}
