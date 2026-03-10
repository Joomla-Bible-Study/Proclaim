<?php

/**
 * Types html - card-based server type picker
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

$app = Factory::getApplication();

if ($app->isClient('site')) {
    Session::checkToken('get') or die(Text::_('JINVALID_TOKEN'));
}

/** @var CWM\Component\Proclaim\Administrator\View\Cwmservers\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('core')
    ->useStyle('com_proclaim.server-types')
    ->addInlineScript(
        "document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                var card = e.target.closest('[data-type-payload]');
                if (!card) return;
                var type = card.getAttribute('data-type-payload');
                window.parent.Joomla.submitbutton('cwmserver.setType', type);
                window.parent.Joomla.Modal.getCurrent().close();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                var card = e.target.closest('[data-type-payload]');
                if (!card) return;
                e.preventDefault();
                var type = card.getAttribute('data-type-payload');
                window.parent.Joomla.submitbutton('cwmserver.setType', type);
                window.parent.Joomla.Modal.getCurrent().close();
            });
        });"
    );

$this->recordId = $app->getInput()->getInt('recordId');

// Icon and brand color per server type key (lowercase addon directory name)
$typeIcons = [
    'local'   => ['icon' => 'fas fa-server',      'color' => '#555555'],
    'direct'  => ['icon' => 'fas fa-link',        'color' => '#2E7D32'],
    'youtube' => ['icon' => 'fab fa-youtube',      'color' => '#FF0000'],
    'vimeo'   => ['icon' => 'fab fa-vimeo',        'color' => '#1AB7EA'],
    'wistia'  => ['icon' => 'fas fa-play-circle',  'color' => '#54BBFF'],
    'resi'        => ['icon' => 'fas fa-signal',       'color' => '#00AEEF'],
    'soundcloud'  => ['icon' => 'fab fa-soundcloud',  'color' => '#FF5500'],
    'dailymotion' => ['icon' => 'fas fa-play-circle', 'color' => '#00D2F3'],
    'rumble'      => ['icon' => 'fas fa-play-circle', 'color' => '#85C742'],
    'facebook'    => ['icon' => 'fab fa-facebook',    'color' => '#1877F2'],
    'embed'       => ['icon' => 'fas fa-code',        'color' => '#666666'],
    'article'     => ['icon' => 'fas fa-newspaper',  'color' => '#1C3D5C'],
    'virtuemart'  => ['icon' => 'fas fa-shopping-cart', 'color' => '#2B71B8'],
    'docman'      => ['icon' => 'fas fa-file-alt',   'color' => '#5C6BC0'],
    'legacy'      => ['icon' => 'fas fa-archive',     'color' => '#888888'],
];
?>
<div class="container-fluid p-3">
    <?php if (empty($this->types)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <h6 class="text-body-secondary mb-3"><?php echo Text::_('JBS_CMN_SELECT_SERVERTYPE'); ?></h6>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
            <?php foreach ($this->types as $item) :
                $typeKey  = strtolower($item->name);
                $iconData = $typeIcons[$typeKey] ?? ['icon' => 'fas fa-plug', 'color' => '#555555'];
                $encoded  = base64_encode(json_encode(['id' => $this->recordId, 'name' => $item->name]));
            ?>
                <div class="col">
                    <div class="card h-100 border-2 server-type-card"
                         role="button"
                         tabindex="0"
                         data-type-payload="<?php echo $this->escape($encoded); ?>">
                        <div class="card-body text-center py-4">
                            <div class="mb-3">
                                <span class="<?php echo $this->escape($iconData['icon']); ?> fa-3x"
                                      style="color: <?php echo $this->escape($iconData['color']); ?>;"
                                      aria-hidden="true"></span>
                            </div>
                            <h5 class="card-title mb-1"><?php echo $this->escape($item->title); ?></h5>
                            <p class="card-text text-body-secondary small mb-0"><?php echo $this->escape($item->description); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</div>
