/**
 * Proclaim Setup Wizard — multi-step first-run configuration.
 *
 * Reads initial state from window.ProcSetupWizard (injected by HtmlView).
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      10.3.0
 */

'use strict';

(function () {
  const config = window.ProcSetupWizard || {};
  const token = config.token || '';
  const baseUrl = config.baseUrl || '';
  const presets = config.presets || {};

  let currentStep = 1;
  const totalSteps = 5;

  // DOM references
  const progressBar = document.getElementById('wizard-progress-bar');
  const prevBtn = document.getElementById('wizard-prev-btn');
  const nextBtn = document.getElementById('wizard-next-btn');
  const applyBtn = document.getElementById('wizard-apply-btn');
  const dismissBtn = document.getElementById('wizard-dismiss-btn');
  const reviewSummary = document.getElementById('wizard-review-summary');

  /**
   * Navigate to a specific step.
   *
   * @param {number} step  Step number (1-5).
   */
  function goToStep(step) {
    if (step < 1 || step > totalSteps) return;

    // Hide all steps
    document.querySelectorAll('.wizard-step').forEach(el => {
      el.classList.add('d-none');
      el.classList.remove('active');
    });

    // Show target step
    const target = document.querySelector(`.wizard-step[data-step="${step}"]`);
    if (target) {
      target.classList.remove('d-none');
      target.classList.add('active');
    }

    currentStep = step;

    // Update progress bar
    const pct = Math.round((step / totalSteps) * 100);
    progressBar.style.width = pct + '%';
    progressBar.setAttribute('aria-valuenow', pct);

    // Update step labels
    document.querySelectorAll('.wizard-step-label').forEach(label => {
      const labelStep = parseInt(label.dataset.step, 10);
      label.classList.remove('bg-success', 'bg-secondary');
      label.classList.add(labelStep <= step ? 'bg-success' : 'bg-secondary');
    });

    // Button visibility
    prevBtn.classList.toggle('d-none', step === 1);
    nextBtn.classList.toggle('d-none', step === totalSteps);
    applyBtn.classList.toggle('d-none', step !== totalSteps);
    dismissBtn.classList.toggle('d-none', step === totalSteps);

    // Build review summary on last step
    if (step === totalSteps) {
      buildReview();
    }

    // Update next button state
    updateNextButton();
  }

  /**
   * Check if the current step is valid and enable/disable Next.
   */
  function updateNextButton() {
    let valid = true;

    if (currentStep === 1) {
      valid = document.getElementById('wizard-ministry-style').value !== '';
    } else if (currentStep === 2) {
      valid = document.getElementById('wizard-org-name').value.trim() !== '';
    }

    nextBtn.disabled = !valid;
  }

  /**
   * Apply preset defaults when a ministry style is selected (Step 1).
   *
   * @param {string} styleKey  Preset key.
   */
  function applyPresetDefaults(styleKey) {
    const preset = presets[styleKey];
    if (!preset) return;

    // Update content toggles (Step 3)
    const seriesEl = document.getElementById('wizard-use-series');
    const topicsEl = document.getElementById('wizard-use-topics');
    const locationsEl = document.getElementById('wizard-use-locations');

    if (seriesEl) seriesEl.checked = preset.use_series !== false;
    if (topicsEl) topicsEl.checked = preset.use_topics === true;
    if (locationsEl) locationsEl.checked = preset.use_locations === true;

    // Show/hide conditional sections based on style
    const simpleOpts = document.getElementById('wizard-simple-options');
    const campusNote = document.getElementById('wizard-campus-note');
    const podcastSection = document.getElementById('wizard-podcast-section');

    if (simpleOpts) simpleOpts.classList.toggle('d-none', styleKey !== 'simple');
    if (campusNote) campusNote.classList.toggle('d-none', styleKey !== 'multi_campus');
    if (podcastSection) podcastSection.classList.toggle('d-none', styleKey === 'simple');
  }

  /**
   * Show/hide media-specific config panels based on radio selection.
   */
  function updateMediaConfig() {
    const selected = document.querySelector('input[name="wizard-media"]:checked')?.value || 'local';
    const ytConfig = document.getElementById('wizard-youtube-config');
    const vimeoConfig = document.getElementById('wizard-vimeo-config');

    if (ytConfig) ytConfig.classList.toggle('d-none', selected !== 'youtube');
    if (vimeoConfig) vimeoConfig.classList.toggle('d-none', selected !== 'vimeo');
  }

  /**
   * Collect all wizard data from form fields.
   *
   * @returns {Object}  Wizard data payload.
   */
  function collectData() {
    const style = document.getElementById('wizard-ministry-style').value;
    const media = document.querySelector('input[name="wizard-media"]:checked')?.value || 'local';

    const data = {
      ministry_style: style,
      org_name: document.getElementById('wizard-org-name').value.trim(),
      default_bible_version: document.getElementById('wizard-bible-version').value,
      uploadpath: document.getElementById('wizard-upload-path').value.trim(),
      provider_getbible: 1,
      primary_media: media,
      create_sample_content: document.getElementById('wizard-sample-content').checked,
      enable_ai: document.getElementById('wizard-enable-ai').checked,
      enable_podcast: document.getElementById('wizard-enable-podcast')?.checked || false,
      enable_backup: document.getElementById('wizard-enable-backup')?.checked || false,
      analytics_enabled: 1,
    };

    // Simple mode template choice
    if (style === 'simple') {
      data.simple_mode_template = document.querySelector('input[name="wizard-simple-template"]:checked')?.value || 'simple_mode1';
      data.simplegridtextoverlay = document.getElementById('wizard-text-overlay')?.checked ? 1 : 0;
    }

    // YouTube config
    if (media === 'youtube') {
      data.youtube_api_key = document.getElementById('wizard-yt-api-key')?.value.trim() || '';
      data.youtube_channel_id = document.getElementById('wizard-yt-channel')?.value.trim() || '';
    }

    // Vimeo config
    if (media === 'vimeo') {
      data.vimeo_access_token = document.getElementById('wizard-vimeo-token')?.value.trim() || '';
    }

    return data;
  }

  /**
   * Build the review summary (Step 5).
   */
  function buildReview() {
    const data = collectData();
    const preset = presets[data.ministry_style] || {};
    const styleLabel = preset.label ? Joomla.Text._(preset.label) || data.ministry_style : data.ministry_style;

    const mediaLabels = {
      local: 'Local Uploads',
      youtube: 'YouTube',
      vimeo: 'Vimeo',
      direct: 'Direct Links',
    };

    let html = '<table class="table table-striped mb-0">';
    html += `<tr><th style="width:40%">Ministry Style</th><td>${styleLabel}</td></tr>`;
    html += `<tr><th>Organization</th><td>${data.org_name || '<em>Not set</em>'}</td></tr>`;
    html += `<tr><th>Bible Version</th><td>${data.default_bible_version.toUpperCase()}</td></tr>`;
    html += `<tr><th>Upload Path</th><td><code>${data.uploadpath}</code></td></tr>`;
    html += `<tr><th>Primary Media</th><td>${mediaLabels[data.primary_media] || data.primary_media}</td></tr>`;

    if (data.primary_media === 'youtube' && data.youtube_api_key) {
      html += `<tr><th>YouTube API Key</th><td><code>${data.youtube_api_key.substring(0, 8)}...</code></td></tr>`;
    }
    if (data.primary_media === 'vimeo' && data.vimeo_access_token) {
      html += `<tr><th>Vimeo Token</th><td><code>${data.vimeo_access_token.substring(0, 8)}...</code></td></tr>`;
    }
    if (data.enable_podcast) {
      html += `<tr><th>Podcasting</th><td>Enabled</td></tr>`;
    }

    html += `<tr><th>Sample Content</th><td>${data.create_sample_content ? 'Yes' : 'No'}</td></tr>`;
    html += `<tr><th>AI Assistant</th><td>${data.enable_ai ? 'Enabled' : 'Disabled'}</td></tr>`;
    html += '</table>';

    reviewSummary.innerHTML = html;
  }

  /**
   * Send the wizard data to the server.
   */
  async function applyWizard() {
    const data = collectData();
    applyBtn.disabled = true;
    applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Applying...';

    const formData = new FormData();
    formData.append(token, '1');
    formData.append('wizard_data', JSON.stringify(data));

    try {
      const response = await fetch(
        `${baseUrl}?option=com_proclaim&task=cwmsetupwizard.apply&format=json`,
        { method: 'POST', body: formData }
      );

      const result = await response.json();

      if (result.success) {
        Joomla.renderMessages({ message: [result.message || 'Setup complete!'] });

        if (result.data?.redirect) {
          setTimeout(() => {
            window.location.href = result.data.redirect;
          }, 1500);
        }
      } else {
        Joomla.renderMessages({ error: [result.message || 'An error occurred.'] });
        applyBtn.disabled = false;
        applyBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Apply';
      }
    } catch (err) {
      Joomla.renderMessages({ error: [err.message || 'Network error.'] });
      applyBtn.disabled = false;
      applyBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Apply';
    }
  }

  /**
   * Dismiss the wizard without applying.
   */
  async function dismissWizard() {
    if (!confirm(Joomla.Text._('JBS_WIZARD_CONFIRM_DISMISS') || 'Skip the setup wizard?')) {
      return;
    }

    const formData = new FormData();
    formData.append(token, '1');

    try {
      await fetch(
        `${baseUrl}?option=com_proclaim&task=cwmsetupwizard.dismiss&format=json`,
        { method: 'POST', body: formData }
      );
    } catch { /* ignore */ }

    window.location.href = `${baseUrl}?option=com_proclaim&view=cwmcpanel`;
  }

  // --- Event Listeners ---

  // Style card selection (Step 1)
  document.querySelectorAll('.style-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.style-card').forEach(c => c.classList.remove('border-primary', 'shadow'));
      card.classList.add('border-primary', 'shadow');
      document.getElementById('wizard-ministry-style').value = card.dataset.style;
      applyPresetDefaults(card.dataset.style);
      updateNextButton();
    });

    card.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        card.click();
      }
    });
  });

  // Org name validation (Step 2)
  document.getElementById('wizard-org-name')?.addEventListener('input', updateNextButton);

  // Media source radio — show/hide platform config panels (Step 4)
  document.querySelectorAll('input[name="wizard-media"]').forEach(radio => {
    radio.addEventListener('change', updateMediaConfig);
  });

  // Navigation
  nextBtn.addEventListener('click', () => goToStep(currentStep + 1));
  prevBtn.addEventListener('click', () => goToStep(currentStep - 1));
  applyBtn.addEventListener('click', applyWizard);
  dismissBtn.addEventListener('click', dismissWizard);

  // Initialize
  goToStep(1);
})();
