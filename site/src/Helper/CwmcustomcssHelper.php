<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Emits the administrator's own CSS for Proclaim's front-end views.
 *
 * Two sheets, component then template, so a template refines the site-wide one
 * rather than replacing it. Both live in params (`#__bsms_admin` and
 * `#__bsms_templates`) so they are covered by Proclaim's backup and survive an
 * update untouched — a file under `media/com_proclaim/` would be neither, since
 * the installer prunes that directory. See discussion #1719.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcustomcssHelper
{
    /**
     * The param both levels store their CSS under.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    public const PARAM = 'custom_css';

    /**
     * Guards against emitting twice within one request.
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    private static bool $applied = false;

    /**
     * Add the component and template sheets to the document, in that order.
     *
     * Inline rather than a generated file: the asset manifest is built from
     * `build/media_source/` and overwritten on every build, so a user sheet
     * cannot be declared in it. Revisit if sheets grow large enough that losing
     * browser caching matters.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function apply(): void
    {
        if (self::$applied) {
            return;
        }

        self::$applied = true;

        $css = '';

        foreach ([self::componentCss(), self::templateCss()] as $sheet) {
            $sheet = self::sanitise($sheet);

            if ($sheet !== '') {
                $css .= $sheet . "\n";
            }
        }

        if ($css === '') {
            return;
        }

        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineStyle(
            $css,
            ['name' => 'com_proclaim.custom-css']
        );
    }

    /**
     * Forget that this request already emitted. For tests.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function reset(): void
    {
        self::$applied = false;
    }

    /**
     * Make a stored sheet safe to place inside a `<style>` element.
     *
     * ⚠️ The only escape that matters here is a closing tag: anything able to
     * end the element turns styling into markup injection. Everything else CSS
     * can do — including `url()` fetching a remote resource — is inherent to
     * the feature and available to anyone who can already edit a template.
     *
     * @param   string  $css  Raw stored CSS
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function sanitise(string $css): string
    {
        $css = trim($css);

        if ($css === '') {
            return '';
        }

        // Matches </style, </STYLE, </ style and the like, which is what a
        // browser's tokeniser accepts as ending the element.
        return (string) preg_replace('#<\s*/\s*style#i', '<\\/style', $css);
    }

    /**
     * The site-wide sheet from `#__bsms_admin.params`.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function componentCss(): string
    {
        $admin = Cwmparams::getAdmin();

        if (!isset($admin->params)) {
            return '';
        }

        $params = $admin->params instanceof Registry ? $admin->params : new Registry($admin->params);

        return (string) $params->get(self::PARAM, '');
    }

    /**
     * The active template's sheet from `#__bsms_templates.params`.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function templateCss(): string
    {
        $template = Cwmparams::getTemplateparams();

        if (!isset($template->params) || !$template->params instanceof Registry) {
            return '';
        }

        return (string) $template->params->get(self::PARAM, '');
    }
}
