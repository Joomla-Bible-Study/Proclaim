# YouTube Module Enhancements Plan

## Phase 0: Documentation Discovery (Complete)

### Allowed APIs & Patterns
- **WAM inline scripts**: `$wa->addInlineScript($js, [], ['name' => 'unique_name'])` — used throughout template
- **WAM inline styles**: `$wa->addInlineStyle($css)` — lines 160-181 of default.php
- **Data attributes**: Badge div (`#mod-proclaim-youtube-badge-{moduleId}`) carries all config as `data-*` attrs
- **Language strings**: `Text::_('MOD_PROCLAIM_YOUTUBE_*')` in PHP; passed to JS via `data-label-*` attributes
- **Language file**: `modules/site/mod_proclaim_youtube/language/en-GB/en-GB.mod_proclaim_youtube.ini`
- **Module params XML**: `modules/site/mod_proclaim_youtube/mod_proclaim_youtube.xml`
- **IntersectionObserver pattern**: Copy from `build/media_source/js/series-scroll.es6.js:290-302`
- **Browser Notification API**: Not yet used in codebase — standard web API, no Joomla wrapper needed
- **Existing polling JS**: Inline in `default.php:197-344`, IIFE pattern with `checkStatus()` / `schedulePoll()`
- **scheduledStartTime flow**: PHP→`data-scheduled-start` attr→JS `scheduledStart` var, updated dynamically via AJAX

### Anti-Patterns to Avoid
- Do NOT create separate `.es6.js` files for module-only JS (current pattern is inline via `addInlineScript`)
- Do NOT use `CwmlangHelper` (admin-only); use `data-label-*` attributes for JS strings
- Do NOT use `Joomla.Text._()` in inline module scripts (no bulk key registration on frontend)
- Do NOT add `joomla.asset.json` entries for module-only assets

### Key Files to Modify
| File | Purpose |
|------|---------|
| `modules/site/mod_proclaim_youtube/tmpl/default.php` | HTML, inline CSS, inline JS |
| `modules/site/mod_proclaim_youtube/mod_proclaim_youtube.xml` | Module params |
| `modules/site/mod_proclaim_youtube/language/en-GB/en-GB.mod_proclaim_youtube.ini` | Language strings |

### Existing Data Available in Template
- `$video['scheduledStartTime']` — ISO 8601 string (already on `data-scheduled-start`)
- `$video['isLive']`, `$video['isUpcoming']` — boolean flags
- `$video['videoId']` — YouTube video ID
- `$moduleId` — unique module instance ID
- `$embedUrl` — full YouTube embed URL
- `$responsive` — whether responsive player mode is on
- `$serverId` — YouTube server record ID

---

## Phase 1: Countdown Timer & Scheduled Date Display

### What to Implement
Add a countdown timer and locale-formatted date display below the badge when a video is upcoming with a known `scheduledStartTime`. The countdown shows human-friendly relative time ("Live in 2 hours 15 min", "Live in 30 minutes", "Starting soon...") and decrements every second. Below the countdown, show the absolute date/time formatted in the visitor's locale ("March 15, 2026 at 11:00 AM").

### Tasks

#### 1.1 Add module params to XML
In `mod_proclaim_youtube.xml`, Display fieldset, after `show_live_badge`:
```xml
<field name="show_countdown" type="radio" label="MOD_PROCLAIM_YOUTUBE_SHOW_COUNTDOWN" description="MOD_PROCLAIM_YOUTUBE_SHOW_COUNTDOWN_DESC" class="btn-group" default="1" showon="show_live_badge:1">
    <option value="0">JNO</option>
    <option value="1">JYES</option>
</field>
```

#### 1.2 Add language strings
In `en-GB.mod_proclaim_youtube.ini`, add:
```ini
MOD_PROCLAIM_YOUTUBE_SHOW_COUNTDOWN="Show Countdown"
MOD_PROCLAIM_YOUTUBE_SHOW_COUNTDOWN_DESC="Show countdown timer and scheduled date for upcoming live streams."
MOD_PROCLAIM_YOUTUBE_LIVE_IN="Live in %s"
MOD_PROCLAIM_YOUTUBE_STARTING_SOON="Starting soon..."
MOD_PROCLAIM_YOUTUBE_SCHEDULED_FOR="Scheduled for %s"
```

#### 1.3 Add countdown HTML to template
After the badge `</div>` (after line 79), add a countdown container:
```php
<?php if ((bool) $params->get('show_countdown', 1) && $isUpcoming && !empty($video['scheduledStartTime'])) : ?>
    <div id="mod-proclaim-youtube-countdown-<?php echo $moduleId; ?>"
         class="mod-proclaim-youtube__countdown mb-2"
         data-label-live-in="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_LIVE_IN'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-starting-soon="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_STARTING_SOON'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-scheduled-for="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_SCHEDULED_FOR'), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mod-proclaim-youtube__countdown-timer text-muted"></div>
        <div class="mod-proclaim-youtube__countdown-date small text-muted"></div>
    </div>
<?php endif; ?>
```

#### 1.4 Add countdown CSS
Append to the existing `addInlineStyle` block (before `CSS);`):
```css
.mod-proclaim-youtube__countdown {
    text-align: center;
}
.mod-proclaim-youtube__countdown-timer {
    font-size: 1.1em;
    font-weight: 600;
}
.mod-proclaim-youtube__countdown-date {
    margin-top: 0.25em;
}
```

#### 1.5 Add countdown JS logic
Inside the existing inline IIFE (after the `scheduledStart` variable declaration around line 218), add:
```javascript
// Countdown timer
var countdownEl = document.getElementById('mod-proclaim-youtube-countdown-{moduleId}');
if (countdownEl) {
    var timerEl = countdownEl.querySelector('.mod-proclaim-youtube__countdown-timer');
    var dateEl = countdownEl.querySelector('.mod-proclaim-youtube__countdown-date');
    var lblLiveIn = countdownEl.dataset.labelLiveIn;
    var lblStartingSoon = countdownEl.dataset.labelStartingSoon;
    var lblScheduledFor = countdownEl.dataset.labelScheduledFor;

    function formatCountdown(ms) {
        if (ms <= 0) return lblStartingSoon;
        var totalMin = Math.floor(ms / 60000);
        var hours = Math.floor(totalMin / 60);
        var minutes = totalMin % 60;
        var parts = [];
        if (hours > 0) parts.push(hours + 'h');
        if (minutes > 0 || hours === 0) parts.push(minutes + 'min');
        return lblLiveIn.replace('%s', parts.join(' '));
    }

    function updateCountdown() {
        if (!scheduledStart) { countdownEl.style.display = 'none'; return; }
        var remaining = scheduledStart - Date.now();
        timerEl.textContent = formatCountdown(remaining);
        // Format date in visitor's locale
        dateEl.textContent = lblScheduledFor.replace('%s',
            new Date(scheduledStart).toLocaleString(undefined, {
                weekday: 'long', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit'
            })
        );
        if (remaining > 0) {
            setTimeout(updateCountdown, remaining > 60000 ? 30000 : 1000);
        }
    }
    updateCountdown();
}
```

Also: when `checkStatus()` detects `isLive && !wasLive`, hide the countdown container before reloading. When AJAX returns updated `scheduledStartTime`, re-trigger `updateCountdown()`.

### Verification
- [ ] Upcoming video with `scheduledStartTime` shows countdown and formatted date
- [ ] Countdown decrements every second when < 1 hour, every 30s when > 1 hour
- [ ] Shows "Starting soon..." when remaining time <= 0
- [ ] Date displays in visitor's locale (test with different browser locales)
- [ ] Countdown hides when no `scheduledStartTime` is available
- [ ] `show_countdown=0` module param hides the feature entirely
- [ ] When stream goes live (page reload), countdown is gone — live badge shows instead
- [ ] AJAX-updated `scheduledStartTime` refreshes the countdown

---

## Phase 2: Notify Me Button

### What to Implement
A bell icon button that requests browser Notification API permission. When permission is granted and polling detects the stream went live, fire a browser notification (in addition to the page reload). The notification uses the video title and a "Watch now" action. GDPR safe — Notification API is browser-local, requires explicit opt-in, no external tracking.

### Tasks

#### 2.1 Add module param
In `mod_proclaim_youtube.xml`, Display fieldset:
```xml
<field name="show_notify_button" type="radio" label="MOD_PROCLAIM_YOUTUBE_SHOW_NOTIFY" description="MOD_PROCLAIM_YOUTUBE_SHOW_NOTIFY_DESC" class="btn-group" default="1" showon="show_live_badge:1">
    <option value="0">JNO</option>
    <option value="1">JYES</option>
</field>
```

#### 2.2 Add language strings
```ini
MOD_PROCLAIM_YOUTUBE_SHOW_NOTIFY="Show Notify Button"
MOD_PROCLAIM_YOUTUBE_SHOW_NOTIFY_DESC="Show a bell button that lets visitors opt in to browser notifications when the stream goes live. Uses the browser Notification API (local, no external tracking)."
MOD_PROCLAIM_YOUTUBE_NOTIFY_ME="Notify me when live"
MOD_PROCLAIM_YOUTUBE_NOTIFY_ENABLED="You'll be notified"
MOD_PROCLAIM_YOUTUBE_NOTIFY_DENIED="Notifications blocked"
MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_TITLE="Live Now!"
MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_BODY="%s is now live. Watch now!"
```

#### 2.3 Add notify button HTML
After the countdown container (or after badge if countdown disabled), for upcoming videos only:
```php
<?php if ((bool) $params->get('show_notify_button', 1) && $isUpcoming) : ?>
    <div id="mod-proclaim-youtube-notify-<?php echo $moduleId; ?>"
         class="mod-proclaim-youtube__notify mb-2"
         data-video-title="<?php echo htmlspecialchars($video['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
         data-label-notify="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ME'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-enabled="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ENABLED'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-denied="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_DENIED'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-live-title="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_TITLE'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-live-body="<?php echo htmlspecialchars(Text::sprintf('MOD_PROCLAIM_YOUTUBE_NOTIFY_LIVE_BODY', $video['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
        <button type="button" class="btn btn-outline-secondary btn-sm mod-proclaim-youtube__notify-btn">
            <span class="fas fa-bell me-1" aria-hidden="true"></span>
            <span class="mod-proclaim-youtube__notify-label"><?php echo Text::_('MOD_PROCLAIM_YOUTUBE_NOTIFY_ME'); ?></span>
        </button>
    </div>
<?php endif; ?>
```

#### 2.4 Add notify JS
Inside the inline IIFE, after countdown logic:
```javascript
// Notify Me
var notifyEl = document.getElementById('mod-proclaim-youtube-notify-{moduleId}');
var notifyGranted = false;
if (notifyEl && 'Notification' in window) {
    var notifyBtn = notifyEl.querySelector('.mod-proclaim-youtube__notify-btn');
    var notifyLabel = notifyEl.querySelector('.mod-proclaim-youtube__notify-label');

    // Check existing permission
    if (Notification.permission === 'granted') {
        notifyGranted = true;
        notifyLabel.textContent = notifyEl.dataset.labelEnabled;
        notifyBtn.classList.replace('btn-outline-secondary', 'btn-outline-success');
    } else if (Notification.permission === 'denied') {
        notifyLabel.textContent = notifyEl.dataset.labelDenied;
        notifyBtn.disabled = true;
    }

    notifyBtn.addEventListener('click', function() {
        if (Notification.permission === 'granted') {
            notifyGranted = true;
            return;
        }
        Notification.requestPermission().then(function(perm) {
            if (perm === 'granted') {
                notifyGranted = true;
                notifyLabel.textContent = notifyEl.dataset.labelEnabled;
                notifyBtn.classList.replace('btn-outline-secondary', 'btn-outline-success');
            } else {
                notifyLabel.textContent = notifyEl.dataset.labelDenied;
                notifyBtn.disabled = true;
            }
        });
    });
} else if (notifyEl) {
    // Notification API not supported — hide button
    notifyEl.style.display = 'none';
}
```

In the `checkStatus()` function, when `isLive && !wasLive` (before the reload setTimeout):
```javascript
// Fire browser notification
if (notifyGranted) {
    try {
        new Notification(notifyEl.dataset.labelLiveTitle, {
            body: notifyEl.dataset.labelLiveBody,
            icon: 'https://img.youtube.com/vi/' + badgeEl.dataset.currentVideo + '/default.jpg',
            tag: 'proclaim-live-' + badgeEl.dataset.serverId
        });
    } catch(e) { /* notification failed, page reload will handle it */ }
}
```

#### 2.5 Add notify CSS
```css
.mod-proclaim-youtube__notify {
    text-align: center;
}
```

### Verification
- [ ] Bell button shows for upcoming videos when `show_notify_button=1`
- [ ] Clicking requests Notification permission (browser prompt)
- [ ] After granting: button shows "You'll be notified" with green outline
- [ ] After denying: button shows "Notifications blocked" and is disabled
- [ ] Already-granted permission: button starts in green state
- [ ] When stream goes live: browser notification fires with video title + thumbnail
- [ ] Notification `tag` prevents duplicate notifications
- [ ] Button hidden when `show_notify_button=0`
- [ ] Button hidden when browser doesn't support Notification API
- [ ] No external requests — GDPR safe (notification icon is YouTube CDN thumbnail which is already loaded by iframe)

---

## Phase 3: Picture-in-Picture Mini-Player

### What to Implement
When a live video is playing and the user scrolls the iframe out of view, collapse it into a small fixed mini-player in the bottom-right corner. Uses IntersectionObserver on the player wrapper. Close button dismisses the mini-player for the session. Expand button scrolls back to the original position.

### Tasks

#### 3.1 Add module param
In `mod_proclaim_youtube.xml`, Display fieldset:
```xml
<field name="enable_mini_player" type="radio" label="MOD_PROCLAIM_YOUTUBE_MINI_PLAYER" description="MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_DESC" class="btn-group" default="1" showon="show_live_badge:1">
    <option value="0">JNO</option>
    <option value="1">JYES</option>
</field>
```

#### 3.2 Add language strings
```ini
MOD_PROCLAIM_YOUTUBE_MINI_PLAYER="Enable Mini-Player"
MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_DESC="When a live video is playing and scrolled out of view, show a floating mini-player in the bottom-right corner."
MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_CLOSE="Close mini-player"
MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_EXPAND="Back to video"
```

#### 3.3 Add mini-player container HTML
After the player `</div>` (after line 109), add a hidden mini-player shell. The JS will clone/move the iframe into it:
```php
<?php if ((bool) $params->get('enable_mini_player', 1) && $isLive) : ?>
    <div id="mod-proclaim-youtube-miniplayer-<?php echo $moduleId; ?>"
         class="mod-proclaim-youtube__miniplayer"
         style="display:none;"
         data-label-close="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_CLOSE'), ENT_QUOTES, 'UTF-8'); ?>"
         data-label-expand="<?php echo htmlspecialchars(Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_EXPAND'), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="mod-proclaim-youtube__miniplayer-controls">
            <button type="button" class="mod-proclaim-youtube__miniplayer-expand btn btn-sm btn-light" aria-label="<?php echo Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_EXPAND'); ?>">
                <span class="fas fa-expand" aria-hidden="true"></span>
            </button>
            <button type="button" class="mod-proclaim-youtube__miniplayer-close btn btn-sm btn-light" aria-label="<?php echo Text::_('MOD_PROCLAIM_YOUTUBE_MINI_PLAYER_CLOSE'); ?>">
                <span class="fas fa-times" aria-hidden="true"></span>
            </button>
        </div>
        <div class="mod-proclaim-youtube__miniplayer-frame"></div>
    </div>
<?php endif; ?>
```

#### 3.4 Add mini-player CSS
```css
.mod-proclaim-youtube__miniplayer {
    position: fixed;
    bottom: 1rem;
    right: 1rem;
    width: 320px;
    z-index: 1050;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    transition: opacity 0.3s, transform 0.3s;
}
.mod-proclaim-youtube__miniplayer-controls {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    z-index: 1;
    display: flex;
    gap: 0.25rem;
}
.mod-proclaim-youtube__miniplayer-controls .btn {
    opacity: 0;
    transition: opacity 0.2s;
    padding: 0.15rem 0.35rem;
    font-size: 0.75rem;
}
.mod-proclaim-youtube__miniplayer:hover .mod-proclaim-youtube__miniplayer-controls .btn {
    opacity: 0.9;
}
.mod-proclaim-youtube__miniplayer-frame iframe {
    width: 320px;
    height: 180px;
    border: 0;
    display: block;
}
@media (max-width: 480px) {
    .mod-proclaim-youtube__miniplayer {
        width: 240px;
    }
    .mod-proclaim-youtube__miniplayer-frame iframe {
        width: 240px;
        height: 135px;
    }
}
```

#### 3.5 Add mini-player JS
Separate inline script block (add after the polling script block, still gated on `$isLive` and `enable_mini_player`):

Copy the IntersectionObserver pattern from `build/media_source/js/series-scroll.es6.js:290-302`:
```javascript
// Picture-in-Picture mini-player
(function() {
    'use strict';
    var playerEl = document.querySelector('#mod-proclaim-youtube-badge-{moduleId}')
        ?.closest('.mod-proclaim-youtube')
        ?.querySelector('.mod-proclaim-youtube__player');
    var miniEl = document.getElementById('mod-proclaim-youtube-miniplayer-{moduleId}');
    if (!playerEl || !miniEl) return;

    var iframe = playerEl.querySelector('iframe');
    if (!iframe) return;

    var miniFrame = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-frame');
    var closeBtn = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-close');
    var expandBtn = miniEl.querySelector('.mod-proclaim-youtube__miniplayer-expand');
    var dismissed = false;
    var miniActive = false;

    function showMini() {
        if (dismissed || miniActive) return;
        // Clone iframe src into mini-player (don't move — keeps original position)
        var miniIframe = document.createElement('iframe');
        miniIframe.src = iframe.src;
        miniIframe.allow = iframe.allow;
        miniIframe.allowFullscreen = true;
        miniIframe.title = iframe.title;
        miniFrame.innerHTML = '';
        miniFrame.appendChild(miniIframe);
        miniEl.style.display = 'block';
        miniActive = true;
    }

    function hideMini() {
        miniEl.style.display = 'none';
        miniFrame.innerHTML = '';
        miniActive = false;
    }

    closeBtn.addEventListener('click', function() {
        dismissed = true;
        hideMini();
    });

    expandBtn.addEventListener('click', function() {
        hideMini();
        playerEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) {
                showMini();
            } else {
                hideMini();
            }
        });
    }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.3
    });

    observer.observe(playerEl);
})();
```

### Verification
- [ ] Live video: scrolling iframe out of view shows mini-player in bottom-right
- [ ] Mini-player iframe plays the same video
- [ ] Scrolling back hides the mini-player
- [ ] Close button dismisses permanently for the session
- [ ] Expand button scrolls smoothly back to original player position
- [ ] Controls (close/expand) appear on hover, hidden otherwise
- [ ] Mobile: mini-player is 240px wide (responsive)
- [ ] Mini-player does NOT appear for non-live videos
- [ ] `enable_mini_player=0` disables the feature entirely
- [ ] IntersectionObserver threshold 0.3 — mini-player shows when < 30% visible

---

## Phase 4: Integration & Polish

### Tasks

#### 4.1 Coordinate countdown + notify + badge interactions
- When AJAX `checkStatus()` detects live transition:
  1. Fire browser notification (if granted)
  2. Hide countdown container
  3. Update badge to LIVE
  4. Reload page after 2s (existing behavior)
- When `scheduledStartTime` updates via AJAX: re-trigger countdown `updateCountdown()`
- When stream ends (not live, not upcoming): hide countdown, hide notify button

#### 4.2 Run language sync
```bash
composer sync-languages
```

#### 4.3 Run linting
```bash
composer lint:fix
composer lint:syntax
```

#### 4.4 Run tests
```bash
composer test
npm test
```

### Verification
- [ ] `composer lint` passes (0 fixable files)
- [ ] `composer test` passes (523+ tests)
- [ ] `npm test` passes (200+ tests)
- [ ] All 7 language files updated via sync-languages
- [ ] Module works with all three features enabled simultaneously
- [ ] Module works with all three features disabled
- [ ] Module works on mobile viewport (≤480px)
- [ ] No console errors in browser dev tools
- [ ] GDPR compliant: no external requests beyond YouTube iframe (already present)