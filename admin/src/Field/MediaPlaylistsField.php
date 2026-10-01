<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Field\Trait\SearchableFancySelectTrait;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Media Playlists field.
 *
 * A searchable, tag-style multi-select of the Proclaim Playlists a media file is
 * assigned to — modelled on {@see PodcastsField}. Options are scoped to the
 * playlists that belong to the media file's own server, so a video is only ever
 * offered playlists it could actually live in. Assignment is persisted to the
 * #__bsms_playlist_items junction (source = 'manual') by the media-file model,
 * not to a column on this field.
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class MediaPlaylistsField extends ListField
{
    use SearchableFancySelectTrait;

    /**
     * The field type.
     *
     * @var  string
     *
     * @since 10.3.3
     */
    protected $type = 'MediaPlaylists';

    /**
     * Set up the field, switching to the fancy-select (Choices.js) layout when
     * searchable="true" — a searchable, tag-style multi-select instead of the
     * native ctrl-click listbox. Mirrors PodcastsField for a consistent media-tab
     * selector style.
     *
     * @param   \SimpleXMLElement  $element  The XML element.
     * @param   mixed              $value    The field value.
     * @param   string             $group    The field group.
     *
     * @return  bool
     *
     * @since   10.3.3
     */
    #[\Override]
    public function setup(\SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->applySearchableFancySelect($element);
        }

        return $result;
    }

    /**
     * Build the option list: published playlists belonging to the media file's
     * server. When no server is chosen yet (a brand-new media file), every
     * published playlist is offered rather than an empty list.
     *
     * @return  array  An array of JHtml options.
     *
     * @since 10.3.3
     */
    #[\Override]
    protected function getOptions(): array
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__bsms_playlists'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('title') . ' ASC');

        // Scope to the media file's server when one is selected.
        $serverId = (int) $this->form->getValue('server_id');

        if ($serverId > 0) {
            $query->where($db->quoteName('server_id') . ' = :sid')
                ->bind(':sid', $serverId, \Joomla\Database\ParameterType::INTEGER);
        }

        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $playlist) {
            $options[] = HTMLHelper::_('select.option', $playlist->id, $playlist->title);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
