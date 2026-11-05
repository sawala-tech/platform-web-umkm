// Lightbox Galeri Produk: buka foto di halaman yang sama, zoom (klik/double-tap, wheel, pinch),
// geser saat di-zoom, navigasi ←/→/swipe, dan tombol Download.
(function () {
  'use strict';

  var dialog = document.getElementById('lightbox');
  var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
  if (!dialog || !items.length || typeof dialog.showModal !== 'function') {
    return; // tanpa dukungan <dialog>: link biasa tetap membuka gambar
  }

  var stage = dialog.querySelector('[data-lb-stage]');
  var img = dialog.querySelector('[data-lb-img]');
  var counter = dialog.querySelector('[data-lb-counter]');
  var download = dialog.querySelector('[data-lb-download]');
  var zoomBtn = dialog.querySelector('[data-lb-zoom]');
  var zoomInIcon = dialog.querySelector('[data-lb-zoom-in]');
  var zoomOutIcon = dialog.querySelector('[data-lb-zoom-out]');
  var prevBtn = dialog.querySelector('[data-lb-prev]');
  var nextBtn = dialog.querySelector('[data-lb-next]');

  var MIN = 1, MAX = 4, STEP = 2.5;
  var current = 0;
  var opener = null;
  var scale = 1, tx = 0, ty = 0;

  // ---- Tampilan ----
  function apply() {
    img.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ')';
    var zoomed = scale > 1;
    dialog.classList.toggle('is-zoomed', zoomed);
    zoomInIcon.hidden = zoomed;
    zoomOutIcon.hidden = !zoomed;
    zoomBtn.setAttribute('aria-label', zoomed ? 'Perkecil' : 'Perbesar');
  }

  // Batasi geser agar gambar tidak keluar dari layar.
  function clamp() {
    var maxX = Math.max(0, (img.offsetWidth * scale - stage.clientWidth) / 2);
    var maxY = Math.max(0, (img.offsetHeight * scale - stage.clientHeight) / 2);
    tx = Math.min(maxX, Math.max(-maxX, tx));
    ty = Math.min(maxY, Math.max(-maxY, ty));
  }

  // Zoom ke skala baru dengan titik (cx, cy) relatif ke tengah stage tetap di tempat.
  function zoomTo(next, cx, cy) {
    next = Math.min(MAX, Math.max(MIN, next));
    cx = cx || 0;
    cy = cy || 0;
    var ratio = next / scale;
    tx = cx - (cx - tx) * ratio;
    ty = cy - (cy - ty) * ratio;
    scale = next;
    if (scale === 1) { tx = 0; ty = 0; }
    clamp();
    apply();
  }

  function resetZoom() {
    scale = 1; tx = 0; ty = 0;
    apply();
  }

  function pointFromEvent(e) {
    var r = stage.getBoundingClientRect();
    return { x: e.clientX - (r.left + r.width / 2), y: e.clientY - (r.top + r.height / 2) };
  }

  function show(index) {
    current = (index + items.length) % items.length;
    var item = items[current];
    var thumb = item.querySelector('img');
    resetZoom();
    img.src = item.getAttribute('href');
    img.alt = thumb ? thumb.alt : '';
    download.href = item.getAttribute('href');
    download.setAttribute('download', item.getAttribute('data-download') || '');
    counter.textContent = (current + 1) + ' / ' + items.length;
  }

  function open(index, from) {
    opener = from;
    show(index);
    dialog.showModal();
  }

  // ---- Buka / tutup ----
  items.forEach(function (item, i) {
    item.addEventListener('click', function (e) {
      if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return; // biarkan buka tab baru
      e.preventDefault();
      open(i, item);
    });
  });

  dialog.querySelector('[data-lb-close]').addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('close', function () {
    resetZoom();
    img.removeAttribute('src');
    if (opener) opener.focus();
  });
  // Klik area kosong (bukan gambar/tombol) menutup lightbox.
  dialog.addEventListener('click', function (e) {
    if (e.target === dialog || e.target === stage) dialog.close();
  });

  // ---- Navigasi ----
  if (prevBtn) prevBtn.addEventListener('click', function () { show(current - 1); });
  if (nextBtn) nextBtn.addEventListener('click', function () { show(current + 1); });
  dialog.addEventListener('keydown', function (e) {
    if (items.length < 2) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); show(current - 1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); show(current + 1); }
  });

  // ---- Zoom tombol & wheel ----
  zoomBtn.addEventListener('click', function () { zoomTo(scale > 1 ? 1 : STEP); });
  stage.addEventListener('wheel', function (e) {
    e.preventDefault();
    var p = pointFromEvent(e);
    zoomTo(scale * (e.deltaY < 0 ? 1.2 : 1 / 1.2), p.x, p.y);
  }, { passive: false });

  // ---- Pointer: klik/double-tap zoom, drag geser, pinch, swipe ----
  var pointers = {};
  var dragStart = null;   // {x, y, tx, ty}
  var pinchStart = null;  // {dist, scale}
  var moved = false;
  var lastTap = 0;

  function pointerList() {
    return Object.keys(pointers).map(function (k) { return pointers[k]; });
  }
  function distance(a, b) { return Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY); }

  img.addEventListener('dragstart', function (e) { e.preventDefault(); });

  stage.addEventListener('pointerdown', function (e) {
    if (e.target !== img) return;
    img.setPointerCapture(e.pointerId);
    pointers[e.pointerId] = e;
    var list = pointerList();
    moved = false;
    if (list.length === 2) {
      pinchStart = { dist: distance(list[0], list[1]), scale: scale };
      dragStart = null;
    } else {
      dragStart = { x: e.clientX, y: e.clientY, tx: tx, ty: ty };
    }
  });

  stage.addEventListener('pointermove', function (e) {
    if (!pointers[e.pointerId]) return;
    pointers[e.pointerId] = e;
    var list = pointerList();
    if (list.length === 2 && pinchStart) {
      moved = true;
      var mid = pointFromEvent({
        clientX: (list[0].clientX + list[1].clientX) / 2,
        clientY: (list[0].clientY + list[1].clientY) / 2
      });
      zoomTo(pinchStart.scale * distance(list[0], list[1]) / pinchStart.dist, mid.x, mid.y);
      return;
    }
    if (!dragStart) return;
    var dx = e.clientX - dragStart.x;
    var dy = e.clientY - dragStart.y;
    if (Math.abs(dx) > 4 || Math.abs(dy) > 4) moved = true;
    if (scale > 1) {
      tx = dragStart.tx + dx;
      ty = dragStart.ty + dy;
      clamp();
      apply();
    }
  });

  function endPointer(e) {
    if (!pointers[e.pointerId]) return;
    delete pointers[e.pointerId];
    var remaining = pointerList().length;
    if (remaining === 1) {
      // Lanjut geser dengan jari yang tersisa setelah pinch.
      var p = pointerList()[0];
      dragStart = { x: p.clientX, y: p.clientY, tx: tx, ty: ty };
      pinchStart = null;
      return;
    }
    if (remaining > 0) return;
    pinchStart = null;

    if (dragStart && moved && scale === 1 && items.length > 1) {
      // Swipe horizontal saat tidak di-zoom → ganti foto.
      var dx = e.clientX - dragStart.x;
      var dy = e.clientY - dragStart.y;
      if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) show(current + (dx < 0 ? 1 : -1));
    } else if (dragStart && !moved && e.type === 'pointerup') {
      var now = Date.now();
      var isTouch = e.pointerType === 'touch';
      // Mouse: satu klik toggle zoom. Sentuh: double-tap toggle zoom.
      if (!isTouch || now - lastTap < 300) {
        var pt = pointFromEvent(e);
        zoomTo(scale > 1 ? 1 : STEP, pt.x, pt.y);
        lastTap = 0;
      } else {
        lastTap = now;
      }
    }
    dragStart = null;
  }
  stage.addEventListener('pointerup', endPointer);
  stage.addEventListener('pointercancel', endPointer);

  window.addEventListener('resize', function () {
    if (dialog.open) { clamp(); apply(); }
  });
})();
