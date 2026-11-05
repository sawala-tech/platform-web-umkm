// Saran slug dari nama usaha + tampilkan konfirmasi perubahan slug portofolio Published.
(function () {
  'use strict';

  var source = document.querySelector('[data-slug-source]');
  var target = document.querySelector('[data-slug-target]');
  if (!source || !target) return;

  var confirmBox = document.querySelector('[data-slug-confirm]');
  var originalSlug = target.getAttribute('data-original-slug') || '';
  // Slug dianggap "disentuh manual" bila sudah berisi nilai saat halaman dibuka.
  var touched = target.value.trim() !== '';

  // Aturan sama dengan normalize_slug() di server (server tetap sumber kebenaran).
  function normalizeSlug(value) {
    return value
      .normalize('NFD').replace(/[̀-ͯ]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '')
      .slice(0, 150)
      .replace(/-$/, '');
  }

  function updateConfirm() {
    if (!confirmBox || !originalSlug) return;
    var current = normalizeSlug(target.value || source.value);
    confirmBox.hidden = current === originalSlug || current === '';
  }

  source.addEventListener('input', function () {
    if (!touched) {
      target.value = normalizeSlug(source.value);
    }
    updateConfirm();
  });

  target.addEventListener('input', function () {
    touched = target.value.trim() !== '';
    updateConfirm();
  });

  target.addEventListener('blur', function () {
    if (target.value.trim() !== '') {
      target.value = normalizeSlug(target.value);
    }
    updateConfirm();
  });
})();
