<?php

/**
 * Setup Wizard template — 5-step first-run configuration.
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var \CWM\Component\Proclaim\Administrator\View\Cwmsetupwizard\HtmlView $this */
?>

<script>window.ProcSetupWizard = <?php echo $this->getJsData(); ?>;</script>

<div id="setup-wizard" class="container-fluid">

    <!-- Progress Bar -->
    <div class="wizard-progress mb-4">
        <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: 20%;"
                 aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" id="wizard-progress-bar"></div>
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span class="wizard-step-label badge bg-success" data-step="1"><?php echo Text::_('JBS_WIZARD_STEP_STYLE'); ?></span>
            <span class="wizard-step-label badge bg-secondary" data-step="2"><?php echo Text::_('JBS_WIZARD_STEP_SETTINGS'); ?></span>
            <span class="wizard-step-label badge bg-secondary" data-step="3"><?php echo Text::_('JBS_WIZARD_STEP_CONTENT'); ?></span>
            <span class="wizard-step-label badge bg-secondary" data-step="4"><?php echo Text::_('JBS_WIZARD_STEP_MEDIA'); ?></span>
            <span class="wizard-step-label badge bg-secondary" data-step="5"><?php echo Text::_('JBS_WIZARD_STEP_REVIEW'); ?></span>
        </div>
    </div>

    <!-- Step 1: Ministry Style -->
    <div class="wizard-step active" data-step="1">
        <h2><?php echo Text::_('JBS_WIZARD_STYLE_HEADING'); ?></h2>
        <p class="lead"><?php echo Text::_('JBS_WIZARD_STYLE_INTRO'); ?></p>

        <div class="row g-4 mt-3" id="style-cards">
            <?php foreach ($this->presets as $key => $preset) : ?>
                <div class="col-md-4">
                    <div class="card h-100 style-card border-2" data-style="<?php echo $key; ?>" role="button" tabindex="0">
                        <div class="card-body text-center">
                            <i class="fa-solid <?php echo $preset['icon']; ?> fa-3x mb-3 text-primary"></i>
                            <h4 class="card-title"><?php echo Text::_($preset['label']); ?></h4>
                            <p class="card-text text-muted"><?php echo Text::_($preset['description']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" id="wizard-ministry-style" value="">
    </div>

    <!-- Step 2: Essential Settings -->
    <div class="wizard-step d-none" data-step="2">
        <h2><?php echo Text::_('JBS_WIZARD_SETTINGS_HEADING'); ?></h2>
        <p class="lead"><?php echo Text::_('JBS_WIZARD_SETTINGS_INTRO'); ?></p>

        <div class="row mt-3">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="wizard-org-name" class="form-label fw-bold">
                        <?php echo Text::_('JBS_WIZARD_ORG_NAME'); ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="wizard-org-name"
                           placeholder="<?php echo Text::_('JBS_WIZARD_ORG_NAME_PLACEHOLDER'); ?>"
                           value="<?php echo $this->escape($this->currentState['org_name'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="wizard-bible-version" class="form-label fw-bold">
                        <?php echo Text::_('JBS_WIZARD_BIBLE_VERSION'); ?>
                    </label>
                    <select class="form-select" id="wizard-bible-version">
                        <option value="kjv" <?php echo ($this->currentState['default_bible_version'] ?? 'kjv') === 'kjv' ? 'selected' : ''; ?>>King James Version (KJV)</option>
                        <option value="esv" <?php echo ($this->currentState['default_bible_version'] ?? '') === 'esv' ? 'selected' : ''; ?>>English Standard Version (ESV)</option>
                        <option value="niv" <?php echo ($this->currentState['default_bible_version'] ?? '') === 'niv' ? 'selected' : ''; ?>>New International Version (NIV)</option>
                        <option value="nlt" <?php echo ($this->currentState['default_bible_version'] ?? '') === 'nlt' ? 'selected' : ''; ?>>New Living Translation (NLT)</option>
                        <option value="nasb" <?php echo ($this->currentState['default_bible_version'] ?? '') === 'nasb' ? 'selected' : ''; ?>>New American Standard Bible (NASB)</option>
                        <option value="nkjv" <?php echo ($this->currentState['default_bible_version'] ?? '') === 'nkjv' ? 'selected' : ''; ?>>New King James Version (NKJV)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="wizard-teacher-name" class="form-label fw-bold">
                        <?php echo Text::_('JBS_WIZARD_TEACHER_NAME'); ?>
                    </label>
                    <input type="text" class="form-control" id="wizard-teacher-name"
                           placeholder="<?php echo Text::_('JBS_WIZARD_TEACHER_NAME_PLACEHOLDER'); ?>">
                    <div class="form-text"><?php echo Text::_('JBS_WIZARD_TEACHER_NAME_DESC'); ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold"><?php echo Text::_('JBS_WIZARD_BIBLE_PROVIDER'); ?></label>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="wizard-bible-provider" id="provider-getbible" value="getbible" checked>
                        <label class="form-check-label" for="provider-getbible">
                            <?php echo Text::_('JBS_WIZARD_PROVIDER_GETBIBLE'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="wizard-bible-provider" id="provider-apibible" value="apibible">
                        <label class="form-check-label" for="provider-apibible">
                            <?php echo Text::_('JBS_WIZARD_PROVIDER_APIBIBLE'); ?>
                        </label>
                    </div>
                </div>

                <!-- API.Bible key (shown when API.Bible selected) -->
                <div class="mb-3 d-none" id="wizard-apibible-config">
                    <label for="wizard-apibible-key" class="form-label"><?php echo Text::_('JBS_WIZARD_APIBIBLE_KEY'); ?></label>
                    <input type="text" class="form-control" id="wizard-apibible-key" placeholder="">
                    <div class="form-text"><?php echo Text::_('JBS_WIZARD_APIBIBLE_KEY_DESC'); ?></div>
                </div>

                <div class="mb-3">
                    <label for="wizard-upload-path" class="form-label fw-bold">
                        <?php echo Text::_('JBS_WIZARD_UPLOAD_PATH'); ?>
                    </label>
                    <input type="text" class="form-control" id="wizard-upload-path"
                           value="<?php echo $this->escape($this->currentState['uploadpath'] ?? '/images/biblestudy/media/'); ?>">
                    <div class="form-text"><?php echo Text::_('JBS_WIZARD_UPLOAD_PATH_DESC'); ?></div>
                </div>

                <div class="mb-3">
                    <label for="wizard-metadesc" class="form-label fw-bold"><?php echo Text::_('JBS_WIZARD_METADESC'); ?></label>
                    <textarea class="form-control" id="wizard-metadesc" rows="2"
                              placeholder="<?php echo Text::_('JBS_WIZARD_METADESC_PLACEHOLDER'); ?>"></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 3: Content Structure -->
    <div class="wizard-step d-none" data-step="3">
        <h2><?php echo Text::_('JBS_WIZARD_CONTENT_HEADING'); ?></h2>
        <p class="lead"><?php echo Text::_('JBS_WIZARD_CONTENT_INTRO'); ?></p>

        <div class="row mt-3">
            <div class="col-md-8">
                <div class="alert alert-info" id="wizard-content-note">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_CONTENT_PRESET_NOTE'); ?>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="wizard-use-series" checked>
                        <label class="form-check-label" for="wizard-use-series">
                            <?php echo Text::_('JBS_WIZARD_USE_SERIES'); ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="wizard-use-topics">
                        <label class="form-check-label" for="wizard-use-topics">
                            <?php echo Text::_('JBS_WIZARD_USE_TOPICS'); ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="wizard-use-locations">
                        <label class="form-check-label" for="wizard-use-locations">
                            <?php echo Text::_('JBS_WIZARD_USE_LOCATIONS'); ?>
                        </label>
                    </div>
                </div>

                <!-- Simple Mode: template choice (shown when Simple Ministry selected) -->
                <div class="mb-4 d-none" id="wizard-simple-options">
                    <hr>
                    <label class="form-label fw-bold"><?php echo Text::_('JBS_WIZARD_TEMPLATE_CHOICE'); ?></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-simple-template" id="simple-tpl1" value="simple_mode1" checked>
                        <label class="form-check-label" for="simple-tpl1">
                            <?php echo Text::_('JBS_WIZARD_TEMPLATE_LIST'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-simple-template" id="simple-tpl2" value="simple_mode2">
                        <label class="form-check-label" for="simple-tpl2">
                            <?php echo Text::_('JBS_WIZARD_TEMPLATE_GRID'); ?>
                        </label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="wizard-text-overlay" checked>
                        <label class="form-check-label" for="wizard-text-overlay">
                            <?php echo Text::_('JBS_WIZARD_TEXT_OVERLAY'); ?>
                        </label>
                    </div>
                </div>

                <!-- Location details (shown for Full Media and Multi-Campus) -->
                <div class="d-none" id="wizard-location-details">
                    <hr>
                    <h5><?php echo Text::_('JBS_WIZARD_LOCATION_HEADING'); ?></h5>
                    <p class="text-muted"><?php echo Text::_('JBS_WIZARD_LOCATION_DESC'); ?></p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="wizard-loc-address" class="form-label"><?php echo Text::_('JBS_WIZARD_LOC_ADDRESS'); ?></label>
                            <input type="text" class="form-control" id="wizard-loc-address" placeholder="123 Main Street">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="wizard-loc-city" class="form-label"><?php echo Text::_('JBS_WIZARD_LOC_CITY'); ?></label>
                            <input type="text" class="form-control" id="wizard-loc-city">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="wizard-loc-state" class="form-label"><?php echo Text::_('JBS_WIZARD_LOC_STATE'); ?></label>
                            <input type="text" class="form-control" id="wizard-loc-state">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="wizard-loc-postcode" class="form-label"><?php echo Text::_('JBS_WIZARD_LOC_POSTCODE'); ?></label>
                            <input type="text" class="form-control" id="wizard-loc-postcode">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="wizard-loc-phone" class="form-label"><?php echo Text::_('JBS_WIZARD_LOC_PHONE'); ?></label>
                            <input type="text" class="form-control" id="wizard-loc-phone">
                        </div>
                    </div>
                </div>

                <!-- Multi-Campus note (shown when Multi-Campus selected) -->
                <div class="alert alert-primary d-none" id="wizard-campus-note">
                    <i class="fa-solid fa-church me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_CAMPUS_NOTE'); ?>
                </div>

                <!-- Default images toggle -->
                <hr>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="wizard-default-images" checked>
                    <label class="form-check-label" for="wizard-default-images">
                        <strong><?php echo Text::_('JBS_WIZARD_DEFAULT_IMAGES'); ?></strong><br>
                        <small class="text-muted"><?php echo Text::_('JBS_WIZARD_DEFAULT_IMAGES_DESC'); ?></small>
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="wizard-sample-content">
                    <label class="form-check-label" for="wizard-sample-content">
                        <strong><?php echo Text::_('JBS_WIZARD_CREATE_SAMPLE'); ?></strong><br>
                        <small class="text-muted"><?php echo Text::_('JBS_WIZARD_CREATE_SAMPLE_DESC'); ?></small>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Media & Integrations -->
    <div class="wizard-step d-none" data-step="4">
        <h2><?php echo Text::_('JBS_WIZARD_MEDIA_HEADING'); ?></h2>
        <p class="lead"><?php echo Text::_('JBS_WIZARD_MEDIA_INTRO'); ?></p>

        <div class="row mt-3">
            <div class="col-md-8">
                <div class="mb-4">
                    <label class="form-label fw-bold"><?php echo Text::_('JBS_WIZARD_PRIMARY_MEDIA'); ?></label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-media" id="media-local" value="local" checked>
                        <label class="form-check-label" for="media-local">
                            <i class="fa-solid fa-hard-drive me-1"></i> <?php echo Text::_('JBS_WIZARD_MEDIA_LOCAL'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-media" id="media-youtube" value="youtube">
                        <label class="form-check-label" for="media-youtube">
                            <i class="fa-brands fa-youtube me-1 text-danger"></i> <?php echo Text::_('JBS_WIZARD_MEDIA_YOUTUBE'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-media" id="media-vimeo" value="vimeo">
                        <label class="form-check-label" for="media-vimeo">
                            <i class="fa-brands fa-vimeo me-1 text-info"></i> <?php echo Text::_('JBS_WIZARD_MEDIA_VIMEO'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="wizard-media" id="media-direct" value="direct">
                        <label class="form-check-label" for="media-direct">
                            <i class="fa-solid fa-link me-1"></i> <?php echo Text::_('JBS_WIZARD_MEDIA_DIRECT'); ?>
                        </label>
                    </div>
                </div>

                <!-- YouTube config (shown when YouTube selected) -->
                <div class="card mb-3 d-none" id="wizard-youtube-config">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-brands fa-youtube text-danger me-1"></i> <?php echo Text::_('JBS_WIZARD_YOUTUBE_CONFIG'); ?></h5>
                        <div class="mb-2">
                            <label for="wizard-yt-api-key" class="form-label"><?php echo Text::_('JBS_WIZARD_YOUTUBE_API_KEY'); ?></label>
                            <input type="text" class="form-control" id="wizard-yt-api-key" placeholder="AIza...">
                            <div class="form-text"><?php echo Text::_('JBS_WIZARD_YOUTUBE_API_KEY_DESC'); ?></div>
                        </div>
                        <div class="mb-2">
                            <label for="wizard-yt-channel" class="form-label"><?php echo Text::_('JBS_WIZARD_YOUTUBE_CHANNEL'); ?></label>
                            <input type="text" class="form-control" id="wizard-yt-channel" placeholder="UC...">
                        </div>
                        <div class="form-text text-muted">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            <?php echo Text::_('JBS_WIZARD_YOUTUBE_SKIP_NOTE'); ?>
                        </div>
                    </div>
                </div>

                <!-- Vimeo config (shown when Vimeo selected) -->
                <div class="card mb-3 d-none" id="wizard-vimeo-config">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-brands fa-vimeo text-info me-1"></i> <?php echo Text::_('JBS_WIZARD_VIMEO_CONFIG'); ?></h5>
                        <div class="mb-2">
                            <label for="wizard-vimeo-token" class="form-label"><?php echo Text::_('JBS_WIZARD_VIMEO_TOKEN'); ?></label>
                            <input type="text" class="form-control" id="wizard-vimeo-token">
                            <div class="form-text"><?php echo Text::_('JBS_WIZARD_VIMEO_TOKEN_DESC'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Podcast toggle (shown for Full Media and Multi-Campus) -->
                <div class="mb-3 d-none" id="wizard-podcast-section">
                    <hr>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="wizard-enable-podcast">
                        <label class="form-check-label" for="wizard-enable-podcast">
                            <strong><?php echo Text::_('JBS_WIZARD_ENABLE_PODCAST'); ?></strong><br>
                            <small class="text-muted"><?php echo Text::_('JBS_WIZARD_ENABLE_PODCAST_DESC'); ?></small>
                        </label>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="wizard-enable-backup">
                        <label class="form-check-label" for="wizard-enable-backup">
                            <strong><?php echo Text::_('JBS_WIZARD_ENABLE_BACKUP'); ?></strong><br>
                            <small class="text-muted"><?php echo Text::_('JBS_WIZARD_ENABLE_BACKUP_DESC'); ?></small>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="wizard-enable-ai">
                        <label class="form-check-label" for="wizard-enable-ai">
                            <?php echo Text::_('JBS_WIZARD_ENABLE_AI'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 5: Review & Apply -->
    <div class="wizard-step d-none" data-step="5">
        <h2><?php echo Text::_('JBS_WIZARD_REVIEW_HEADING'); ?></h2>
        <p class="lead"><?php echo Text::_('JBS_WIZARD_REVIEW_INTRO'); ?></p>

        <div class="row mt-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body" id="wizard-review-summary">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>

                <div class="alert alert-warning mt-3" id="wizard-apply-note">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <?php echo Text::_('JBS_WIZARD_REVIEW_NOTE'); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <div>
            <button type="button" class="btn btn-outline-secondary d-none" id="wizard-prev-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> <?php echo Text::_('JPREVIOUS'); ?>
            </button>
            <button type="button" class="btn btn-link text-muted" id="wizard-dismiss-btn">
                <?php echo Text::_('JBS_WIZARD_SKIP'); ?>
            </button>
        </div>
        <div>
            <button type="button" class="btn btn-primary" id="wizard-next-btn" disabled>
                <?php echo Text::_('JNEXT'); ?> <i class="fa-solid fa-arrow-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-success d-none" id="wizard-apply-btn">
                <i class="fa-solid fa-check me-1"></i> <?php echo Text::_('JBS_WIZARD_APPLY'); ?>
            </button>
        </div>
    </div>
</div>
