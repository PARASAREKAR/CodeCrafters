/**
 * ============================================================
 * main.js — Client-Side Logic for Online Event Registration
 * ------------------------------------------------------------
 * Modules:
 *   1. Theme Switcher    — 4 themes with localStorage persistence
 *   2. Form Validation   — Registration & event forms
 *   3. Search Filter      — Real-time table search
 *   4. Confirmation Dialogs — Delete / dangerous action prompts
 *   5. Toast Notifications — Auto-dismiss flash messages
 *   6. Scroll Animations   — IntersectionObserver fade-ins
 * ============================================================
 */

(function () {
  'use strict';

  /* ==========================================================
     1. THEME SWITCHER
     ========================================================== */

  /** Available theme configurations */
  const THEMES = {
    'midnight-dark': { name: 'Midnight Dark', icon: '🌑' },
    'ocean-blue':    { name: 'Ocean Blue',    icon: '🌊' },
    'forest-green':  { name: 'Forest Green',  icon: '🌲' },
    'sunset-warm':   { name: 'Sunset Warm',   icon: '🌅' }
  };

  /** Read stored theme or fall back to default */
  function getSavedTheme() {
    return localStorage.getItem('theme') || 'midnight-dark';
  }

  /** Apply a theme to the document */
  function applyTheme(themeKey) {
    document.documentElement.setAttribute('data-theme', themeKey);
    localStorage.setItem('theme', themeKey);
    updateActiveThemeOption(themeKey);
  }

  /** Highlight the active option inside the menu */
  function updateActiveThemeOption(activeKey) {
    document.querySelectorAll('.theme-option').forEach(function (opt) {
      opt.classList.toggle('active', opt.dataset.theme === activeKey);
    });
  }

  /** Initialise theme switcher UI and events */
  function initThemeSwitcher() {
    // Apply saved theme immediately
    applyTheme(getSavedTheme());

    var toggleBtn = document.querySelector('.theme-switcher-btn');
    var menu      = document.querySelector('.theme-switcher-menu');

    if (!toggleBtn || !menu) return;

    // Toggle menu visibility
    toggleBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      menu.classList.toggle('active');
    });

    // Handle theme option clicks
    menu.addEventListener('click', function (e) {
      var option = e.target.closest('.theme-option');
      if (!option) return;

      var themeKey = option.dataset.theme;
      if (themeKey && THEMES[themeKey]) {
        applyTheme(themeKey);
        // Close the menu after selection
        menu.classList.remove('active');
      }
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.theme-switcher')) {
        menu.classList.remove('active');
      }
    });

    // Close menu on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        menu.classList.remove('active');
      }
    });
  }

  /* ==========================================================
     2. FORM VALIDATION & GOOGLE-STYLE PASSWORD CHECKER
     ========================================================== */

  // ── Common / breached passwords blacklist ──
  var COMMON_PASSWORDS = [
    'password', 'password1', 'password123', 'password1234', '123456789012',
    'qwerty123456', 'letmein1234', 'welcome12345', 'admin12345', 'master12345',
    'iloveyou1234', 'trustno1234', 'sunshine1234', 'princess1234', 'football1234',
    'charlie12345', 'shadow123456', 'michael12345', 'donald123456', 'batman123456',
    'access123456', 'dragon123456', 'monkey123456', 'mustang12345', 'qwerty1234567',
    'abcdef123456', 'abc123456789', 'passw0rd1234', 'p@ssword1234', 'p@ssw0rd1234',
    '123456abcdef', 'qwertyuiop12', 'asdfghjkl123', 'zxcvbnm12345', '1234567890ab',
    'changeme1234', 'letmein12345', 'welcome1234!', 'password!234', 'test12345678',
    'password', '12345678', 'qwerty', 'abc123', 'monkey', 'master', 'dragon',
    'letmein', 'login', 'princess', 'football', 'shadow', 'sunshine', 'trustno1',
    'iloveyou', 'batman', 'access', 'hello', 'charlie', 'donald', '123123',
    '654321', 'baseball', 'michael', 'starwars', 'welcome', 'passw0rd', 'p@ssword',
    'admin', 'qwerty12', 'mustang', 'p@ssw0rd', '123abc', 'changeme'
  ];

  // ── Keyboard sequential patterns ──
  var KEYBOARD_PATTERNS = [
    'qwerty', 'qwertyui', 'qwertyuiop', 'asdfgh', 'asdfghjk', 'asdfghjkl',
    'zxcvbn', 'zxcvbnm', '1234567', '12345678', '123456789', '1234567890',
    '0987654', '09876543', '098765432', '0987654321',
    'abcdefg', 'abcdefgh', 'abcdefghi', 'abcdefghij',
    'qazwsx', 'wsxedc', 'edcrfv'
  ];

  /**
   * Check individual password requirements.
   * Returns an object with boolean flags for each rule.
   */
  function checkPasswordRequirements(pw) {
    var lower = pw.toLowerCase();
    var isCommon  = COMMON_PASSWORDS.indexOf(lower) !== -1;
    var hasPattern = false;

    for (var i = 0; i < KEYBOARD_PATTERNS.length; i++) {
      var pat = KEYBOARD_PATTERNS[i];
      var rev = pat.split('').reverse().join('');
      if (lower.indexOf(pat) !== -1 || lower.indexOf(rev) !== -1) {
        hasPattern = true;
        break;
      }
    }

    // Check for simple sequential chars (aaaa, 1111, etc.)
    if (!hasPattern && pw.length >= 4) {
      var repeats = /(.)\1{3,}/.test(pw);
      if (repeats) hasPattern = true;
    }

    return {
      length:     pw.length >= 8,
      uppercase:  /[A-Z]/.test(pw),
      lowercase:  /[a-z]/.test(pw),
      number:     /[0-9]/.test(pw),
      special:    /[^A-Za-z0-9]/.test(pw),
      noSpaces:   pw.length === 0 || (pw === pw.trim()),
      noCommon:   !isCommon,
      noPatterns: !hasPattern
    };
  }

  /**
   * Calculate password strength score (0-100).
   * Factors: length, char diversity, bonus length, penalties.
   */
  function calculateStrengthScore(pw, reqs) {
    if (!pw || pw.length === 0) return 0;

    var score = 0;

    // Length contribution (up to 30 points)
    score += Math.min(30, pw.length * 2);

    // Character class diversity (up to 40 points, 10 each)
    if (reqs.uppercase) score += 10;
    if (reqs.lowercase) score += 10;
    if (reqs.number)    score += 10;
    if (reqs.special)   score += 10;

    // Bonus for length over 16 (up to 15 points)
    if (pw.length > 16) {
      score += Math.min(15, (pw.length - 16) * 3);
    }

    // Unique character ratio bonus (up to 15 points)
    var uniqueChars = {};
    for (var i = 0; i < pw.length; i++) uniqueChars[pw[i]] = true;
    var uniqueRatio = Object.keys(uniqueChars).length / pw.length;
    score += Math.round(uniqueRatio * 15);

    // Penalties
    if (!reqs.noCommon)   score = Math.min(score, 15);
    if (!reqs.noPatterns) score = Math.max(0, score - 25);
    if (!reqs.noSpaces)   score = Math.max(0, score - 10);

    return Math.min(100, Math.max(0, score));
  }

  /**
   * Map a numeric score to a named strength level.
   */
  function getStrengthLevel(score) {
    if (score <= 20) return { key: 'very-weak',   label: 'Very Weak',   className: 'strength-very-weak' };
    if (score <= 40) return { key: 'weak',         label: 'Weak',         className: 'strength-weak' };
    if (score <= 60) return { key: 'fair',         label: 'Fair',         className: 'strength-fair' };
    if (score <= 80) return { key: 'strong',       label: 'Strong',       className: 'strength-strong' };
    return              { key: 'very-strong', label: 'Very Strong', className: 'strength-very-strong' };
  }

  /**
   * Update the DOM elements for the password strength meter and checklist.
   */
  function updatePasswordUI(pw) {
    var container     = document.getElementById('passwordStrengthContainer');
    var fill          = document.getElementById('passwordStrengthFill');
    var textEl        = document.getElementById('passwordStrengthText');
    var scoreEl       = document.getElementById('passwordStrengthScore');
    var reqsPanel     = document.getElementById('passwordRequirements');
    var breachWarning = document.getElementById('passwordBreachWarning');
    var passphraseTip = document.getElementById('passphraseTip');

    if (!container) return;

    // Show/hide elements based on whether there is input
    if (pw.length === 0) {
      container.style.display = 'none';
      if (breachWarning) breachWarning.style.display = 'none';
      if (passphraseTip) passphraseTip.style.display = 'none';
      // Reset checklist items
      document.querySelectorAll('.password-req-item').forEach(function(item) {
        item.classList.remove('met', 'unmet');
        item.querySelector('.req-icon').textContent = '✕';
      });
      return;
    }

    container.style.display = 'block';

    // Calculate requirements and score
    var reqs  = checkPasswordRequirements(pw);
    var score = calculateStrengthScore(pw, reqs);
    var level = getStrengthLevel(score);

    // Update strength meter
    container.className = 'password-strength-container ' + level.className;
    textEl.textContent   = level.label;
    scoreEl.textContent  = score + '/100';

    // Update requirements checklist
    var reqMap = {
      'length':      reqs.length,
      'uppercase':   reqs.uppercase,
      'lowercase':   reqs.lowercase,
      'number':      reqs.number,
      'special':     reqs.special,
      'no-spaces':   reqs.noSpaces,
      'no-common':   reqs.noCommon,
      'no-patterns': reqs.noPatterns
    };

    Object.keys(reqMap).forEach(function(reqKey) {
      var item = document.querySelector('.password-req-item[data-req="' + reqKey + '"]');
      if (!item) return;
      var icon = item.querySelector('.req-icon');

      if (reqMap[reqKey]) {
        item.classList.add('met');
        item.classList.remove('unmet');
        icon.textContent = '✓';
      } else {
        item.classList.remove('met');
        item.classList.add('unmet');
        icon.textContent = '✕';
      }
    });

    // Show breach warning for common passwords
    if (breachWarning) {
      breachWarning.style.display = !reqs.noCommon ? 'block' : 'none';
    }

    // Show passphrase tip when password is weak/very-weak and user has typed 4+ chars
    if (passphraseTip) {
      passphraseTip.style.display = (pw.length >= 4 && score <= 40) ? 'block' : 'none';
    }
  }

  /**
   * Show an inline error on a form control.
   * Expects a sibling .invalid-feedback-custom element.
   */
  function showError(input, message) {
    input.classList.add('is-invalid');
    var feedback = input.parentElement.querySelector('.invalid-feedback-custom');
    // Also check parent's parent (for password-field-wrapper nesting)
    if (!feedback) {
      feedback = input.closest('.mb-3, .mb-4');
      if (feedback) feedback = feedback.querySelector('.invalid-feedback-custom');
    }
    if (feedback) {
      feedback.textContent = message;
      feedback.style.display = 'block';
    }
  }

  /** Clear error state from a control */
  function clearError(input) {
    input.classList.remove('is-invalid');
    var feedback = input.parentElement.querySelector('.invalid-feedback-custom');
    if (!feedback) {
      feedback = input.closest('.mb-3, .mb-4');
      if (feedback) feedback = feedback.querySelector('.invalid-feedback-custom');
    }
    if (feedback) {
      feedback.textContent = '';
      feedback.style.display = 'none';
    }
  }

  /** Simple email format test */
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  /**
   * Validate the user registration form.
   * Returns true if all fields are valid, false otherwise.
   */
  function validateRegistrationForm(form) {
    var isValid = true;
    var fields  = form.querySelectorAll('.form-control-custom[required], .form-select-custom[required]');

    // Clear previous errors
    fields.forEach(function (f) { clearError(f); });

    fields.forEach(function (field) {
      var value = field.value.trim();

      // Required check
      if (!value) {
        showError(field, 'This field is required.');
        isValid = false;
        return;
      }

      // Email format
      if (field.type === 'email' && !isValidEmail(value)) {
        showError(field, 'Please enter a valid email address.');
        isValid = false;
        return;
      }

      // Password strength validation (Google-style)
      if (field.name === 'password') {
        var pw   = field.value;  // Don't trim – we check spaces separately
        var reqs = checkPasswordRequirements(pw);

        if (!reqs.noSpaces) {
          showError(field, 'Password cannot start or end with a space.');
          isValid = false;
          return;
        }
        if (!reqs.length) {
          showError(field, 'Password must be at least 8 characters long.');
          isValid = false;
          return;
        }
        if (!reqs.uppercase) {
          showError(field, 'Password must contain at least one uppercase letter.');
          isValid = false;
          return;
        }
        if (!reqs.lowercase) {
          showError(field, 'Password must contain at least one lowercase letter.');
          isValid = false;
          return;
        }
        if (!reqs.number) {
          showError(field, 'Password must contain at least one number.');
          isValid = false;
          return;
        }
        if (!reqs.special) {
          showError(field, 'Password must contain at least one special character.');
          isValid = false;
          return;
        }
        if (!reqs.noCommon) {
          showError(field, 'This password is too common. Please choose a more unique one.');
          isValid = false;
          return;
        }
        if (!reqs.noPatterns) {
          showError(field, 'Password contains predictable patterns. Avoid keyboard sequences.');
          isValid = false;
          return;
        }
      }
    });

    // Matching passwords
    var password        = form.querySelector('[name="password"]');
    var confirmPassword = form.querySelector('[name="confirm_password"]');
    if (password && confirmPassword) {
      if (confirmPassword.value.trim() && password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match.');
        isValid = false;
      }
    }

    return isValid;
  }

  /**
   * Validate the event creation / edit form.
   * Checks: required fields, future date, positive capacity.
   */
  function validateEventForm(form) {
    var isValid = true;
    var fields  = form.querySelectorAll('.form-control-custom[required], .form-select-custom[required]');

    fields.forEach(function (f) { clearError(f); });

    fields.forEach(function (field) {
      var value = field.value.trim();

      if (!value) {
        showError(field, 'This field is required.');
        isValid = false;
        return;
      }

      // Date must be in the future
      if (field.type === 'date' || field.type === 'datetime-local') {
        var inputDate = new Date(value);
        var now       = new Date();
        if (inputDate <= now) {
          showError(field, 'Event date must be in the future.');
          isValid = false;
          return;
        }
      }

      // Capacity must be a positive integer
      if (field.name === 'capacity' || field.name === 'max_capacity') {
        var cap = parseInt(value, 10);
        if (isNaN(cap) || cap <= 0) {
          showError(field, 'Capacity must be a positive number.');
          isValid = false;
          return;
        }
      }
    });

    return isValid;
  }

  /** Initialize password visibility toggles */
  function initPasswordToggles() {
    document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var targetId = btn.getAttribute('data-target');
        var input    = document.getElementById(targetId);
        if (!input) return;

        if (input.type === 'password') {
          input.type = 'text';
          btn.textContent = '🔒';
          btn.setAttribute('aria-label', 'Hide password');
        } else {
          input.type = 'password';
          btn.textContent = '👁️';
          btn.setAttribute('aria-label', 'Show password');
        }
        input.focus();
      });
    });
  }

  /** Initialize the real-time password strength checker */
  function initPasswordStrengthChecker() {
    var passwordInput  = document.getElementById('password');
    var reqsPanel      = document.getElementById('passwordRequirements');

    if (!passwordInput) return;

    // Show requirements panel on focus
    passwordInput.addEventListener('focus', function() {
      if (reqsPanel) reqsPanel.style.display = 'block';
    });

    // Real-time validation on input
    passwordInput.addEventListener('input', function() {
      updatePasswordUI(passwordInput.value);
    });

    // Real-time confirm password matching
    var confirmInput   = document.getElementById('confirm_password');
    var confirmFeedback = document.getElementById('confirmPasswordFeedback');
    if (confirmInput && confirmFeedback) {
      confirmInput.addEventListener('input', function() {
        if (confirmInput.value && passwordInput.value !== confirmInput.value) {
          confirmInput.classList.add('is-invalid');
          confirmFeedback.textContent = 'Passwords do not match.';
          confirmFeedback.style.display = 'block';
        } else {
          confirmInput.classList.remove('is-invalid');
          confirmFeedback.textContent = '';
          confirmFeedback.style.display = 'none';
        }
      });
    }
  }

  /** Attach validation to forms on the page */
  function initFormValidation() {
    // Registration form (from register.php – the form uses action="auth_process.php")
    var regForms = document.querySelectorAll('form[action*="auth_process"]');
    regForms.forEach(function(form) {
      // Only apply password validation to forms with a password + confirm_password field (registration)
      var hasConfirm = form.querySelector('[name="confirm_password"]');
      if (hasConfirm) {
        form.addEventListener('submit', function (e) {
          if (!validateRegistrationForm(form)) {
            e.preventDefault();
          }
        });
      }
    });

    // Event form (create / edit)
    var eventForm = document.getElementById('eventForm');
    if (eventForm) {
      eventForm.addEventListener('submit', function (e) {
        if (!validateEventForm(eventForm)) {
          e.preventDefault();
        }
      });
    }

    // Live clear-on-type: remove error when user starts correcting
    document.querySelectorAll('.form-control-custom, .form-select-custom').forEach(function (input) {
      input.addEventListener('input', function () {
        if (input.classList.contains('is-invalid')) {
          clearError(input);
        }
      });
    });

    // Initialize password-specific features
    initPasswordToggles();
    initPasswordStrengthChecker();
  }

  /* ==========================================================
     3. SEARCH / FILTER
     ========================================================== */

  /** Real-time table search: hides rows that don't match */
  function initSearchFilter() {
    var searchInputs = document.querySelectorAll('.search-input');

    searchInputs.forEach(function (input) {
      input.addEventListener('input', function () {
        var query = input.value.toLowerCase().trim();

        // Find the closest searchable table (within the same container or page)
        var container = input.closest('.search-wrapper');
        var table     = null;

        if (container) {
          // Look for a sibling or nearby table
          var parent = container.parentElement;
          table = parent ? parent.querySelector('.searchable-table') : null;
        }
        // Fallback: first searchable table on the page
        if (!table) {
          table = document.querySelector('.searchable-table');
        }
        if (!table) return;

        var rows = table.querySelectorAll('tbody tr');
        var visibleCount = 0;

        rows.forEach(function (row) {
          // Skip empty-state rows
          if (row.classList.contains('empty-row')) return;

          var cells = row.querySelectorAll('td');
          var text  = '';
          cells.forEach(function (cell) { text += cell.textContent.toLowerCase() + ' '; });

          if (query === '' || text.indexOf(query) !== -1) {
            row.style.display = '';
            visibleCount++;
          } else {
            row.style.display = 'none';
          }
        });

        // Toggle a "no results" message
        var noResults = table.querySelector('.no-search-results');
        if (visibleCount === 0 && query !== '') {
          if (!noResults) {
            var colCount = table.querySelectorAll('thead th').length || 1;
            var tr       = document.createElement('tr');
            tr.className = 'no-search-results';
            tr.innerHTML = '<td colspan="' + colCount + '" class="text-center" style="padding:2rem;color:var(--text-muted);">' +
                           '<i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;"></i>' +
                           'No results found for "<strong>' + escapeHtml(query) + '</strong>"</td>';
            table.querySelector('tbody').appendChild(tr);
          }
        } else if (noResults) {
          noResults.remove();
        }
      });
    });
  }

  /** Escape HTML to prevent XSS in dynamic content */
  function escapeHtml(str) {
    var div       = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  /* ==========================================================
     4. CONFIRMATION DIALOGS
     ========================================================== */

  /** Attach confirm() prompts to delete & dangerous-action buttons */
  function initConfirmDialogs() {
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.btn-delete, .confirm-action');
      if (!btn) return;

      var message = btn.dataset.confirmMessage ||
                    'Are you sure you want to proceed? This action cannot be undone.';

      if (!confirm(message)) {
        e.preventDefault();
        e.stopImmediatePropagation();
      }
    });
  }

  /* ==========================================================
     5. TOAST / FLASH NOTIFICATIONS
     ========================================================== */

  /** Auto-dismiss flash messages (Bootstrap alerts) after 5 seconds */
  function initToastAutoDismiss() {
    var alerts = document.querySelectorAll('.alert-custom, .alert-dismissible');

    alerts.forEach(function (alert) {
      setTimeout(function () {
        // Fade out
        alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        alert.style.opacity    = '0';
        alert.style.transform  = 'translateY(-10px)';

        setTimeout(function () {
          // Remove from DOM or use Bootstrap's dismiss
          if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
          } else {
            alert.remove();
          }
        }, 500);
      }, 5000);
    });
  }

  /* ==========================================================
     6. SCROLL ANIMATIONS (IntersectionObserver)
     ========================================================== */

  /** Add .fade-in class to .animate-on-scroll elements when they enter viewport */
  function initScrollAnimations() {
    var elements = document.querySelectorAll('.animate-on-scroll');
    if (!elements.length) return;

    // Fallback for browsers without IntersectionObserver
    if (!('IntersectionObserver' in window)) {
      elements.forEach(function (el) { el.classList.add('fade-in'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('fade-in');
          observer.unobserve(entry.target); // Animate only once
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -40px 0px'
    });

    elements.forEach(function (el) { observer.observe(el); });
  }

  /* ==========================================================
     INITIALISE EVERYTHING ON DOM READY
     ========================================================== */

  document.addEventListener('DOMContentLoaded', function () {
    initThemeSwitcher();
    initFormValidation();
    initSearchFilter();
    initConfirmDialogs();
    initToastAutoDismiss();
    initScrollAnimations();
  });

})();

/**
 * Flash notification when Support Centre is clicked
 */
window.showSupportUnderProcess = function (e) {
  if (e && e.preventDefault) e.preventDefault();

  var existing = document.getElementById('support-process-toast');
  if (existing) existing.remove();

  var toast = document.createElement('div');
  toast.id = 'support-process-toast';
  toast.className = 'alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-4 shadow-lg border-0';
  toast.style.zIndex = '999999';
  toast.style.minWidth = '360px';
  toast.style.borderRadius = '12px';
  toast.style.background = 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)';
  toast.style.color = '#ffffff';
  toast.style.borderLeft = '5px solid #38bdf8';
  toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.35)';

  toast.innerHTML = `
    <div class="d-flex align-items-center py-1">
      <i class="bi bi-gear-wide-connected me-3 fs-3 text-info"></i>
      <div class="pe-3">
        <strong class="d-block text-white mb-1" style="font-size: 1.05rem;">Support Centre</strong>
        <span class="text-light" style="font-size: 0.92rem;">Support Centre is currently under process.</span>
      </div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  `;

  document.body.appendChild(toast);

  setTimeout(function () {
    if (toast && toast.parentNode) {
      toast.classList.remove('show');
      setTimeout(function () { if (toast.parentNode) toast.remove(); }, 300);
    }
  }, 4500);
};

// Close offcanvas mobile navbar when clicking outside
document.addEventListener('click', function(e) {
  var navbarCollapse = document.querySelector('.navbar-collapse.show');
  var navbarToggler = document.querySelector('.navbar-toggler');
  
  // If the mobile navbar is open
  if (navbarCollapse && navbarToggler) {
    // Check if click was outside the menu and not on the hamburger icon
    if (!navbarCollapse.contains(e.target) && !navbarToggler.contains(e.target)) {
      // Trigger Bootstrap's collapse by clicking the toggler
      navbarToggler.click();
    }
  }
});
