// Interaksi panel admin: modal konfirmasi hapus, salin URL, toast otomatis hilang.
(function () {
  'use strict';

  // Modal/Konfirmasi Hapus
  var dialog = document.getElementById('confirm-delete');
  if (dialog) {
    var form = dialog.querySelector('[data-delete-form]');
    var nameEl = dialog.querySelector('[data-delete-name]');
    var slugEl = dialog.querySelector('[data-delete-slug]');

    document.querySelectorAll('[data-delete-action]').forEach(function (button) {
      button.addEventListener('click', function () {
        form.setAttribute('action', button.getAttribute('data-delete-action'));
        nameEl.textContent = button.getAttribute('data-delete-name');
        slugEl.textContent = button.getAttribute('data-delete-slug');
        if (typeof dialog.showModal === 'function') {
          dialog.showModal();
        } else if (window.confirm('Hapus portofolio ' + nameEl.textContent + '?')) {
          form.submit();
        }
      });
    });

    dialog.querySelectorAll('[data-modal-close]').forEach(function (button) {
      button.addEventListener('click', function () { dialog.close(); });
    });
  }

  // Toast
  function showToast(message) {
    var stack = document.querySelector('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    var toast = document.createElement('div');
    toast.className = 'toast toast-success';
    toast.setAttribute('role', 'status');
    var dot = document.createElement('span');
    dot.className = 'toast-dot';
    var text = document.createElement('span');
    text.textContent = message;
    toast.appendChild(dot);
    toast.appendChild(text);
    stack.appendChild(toast);
    hideLater(toast);
  }

  function hideLater(toast) {
    setTimeout(function () {
      toast.classList.add('is-hidden');
      setTimeout(function () { toast.remove(); }, 400);
    }, 4000);
  }

  document.querySelectorAll('[data-toast]').forEach(hideLater);

  // Salin URL publik
  document.querySelectorAll('[data-copy-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      var input = document.querySelector(button.getAttribute('data-copy-target'));
      if (!input) return;
      var done = function () { showToast('URL disalin'); };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(input.value).then(done, function () {
          input.select();
          document.execCommand('copy');
          done();
        });
      } else {
        input.select();
        document.execCommand('copy');
        done();
      }
    });
  });
})();
