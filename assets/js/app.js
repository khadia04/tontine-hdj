/* TONTINE HDJ — Scripts généraux */
(function () {
  'use strict';

  // --- Sidebar mobile ---
  const sidebar = document.getElementById('sidebar');
  const burger = document.getElementById('burger');
  const backdrop = document.getElementById('sidebarBackdrop');
  function closeSidebar() {
    sidebar && sidebar.classList.remove('open');
    backdrop && backdrop.classList.remove('show');
  }
  burger && burger.addEventListener('click', function () {
    sidebar.classList.toggle('open');
    backdrop.classList.toggle('show');
  });
  backdrop && backdrop.addEventListener('click', closeSidebar);

  // --- Confirmation de suppression via SweetAlert2 ---
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      e.preventDefault();
      const form = el.closest('form');
      const msg = el.getAttribute('data-confirm') || 'Confirmer cette action ?';
      Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#0E6B57',
        cancelButtonColor: '#9aa8a3',
        reverseButtons: true
      }).then(function (r) { if (r.isConfirmed && form) form.submit(); });
    });
  });

  // --- Toasts flash auto ---
  window.hdjToast = function (icon, title) {
    Swal.fire({
      toast: true, position: 'top-end', icon: icon, title: title,
      showConfirmButton: false, timer: 3200, timerProgressBar: true
    });
  };

  // --- Aperçu de la photo (formulaire membre) ---
  const photoInput = document.getElementById('photoInput');
  const photoPreview = document.getElementById('photoPreview');
  photoInput && photoInput.addEventListener('change', function () {
    const f = this.files[0];
    if (f && photoPreview) {
      photoPreview.src = URL.createObjectURL(f);
      photoPreview.style.display = 'block';
    }
  });

  // --- Calcul automatique du gain (formulaire membre) ---
  const cotisField = document.getElementById('cotisField');
  const gainOut = document.getElementById('gainOut');
  const dureeData = document.getElementById('dureeData');
  function refreshGain() {
    if (!cotisField || !gainOut || !dureeData) return;
    const c = parseInt(cotisField.value || '0', 10);
    const d = parseInt(dureeData.value || '0', 10);
    const g = (isNaN(c) ? 0 : c) * (isNaN(d) ? 0 : d);
    gainOut.textContent = g.toLocaleString('fr-FR') + ' FCFA';
  }
  cotisField && cotisField.addEventListener('input', refreshGain);
  refreshGain();

  // --- Bascule des cotisations (AJAX) ---
  document.querySelectorAll('.cotis-select').forEach(function (sel) {
    sel.addEventListener('change', function () {
      const data = new FormData();
      data.append('_csrf', window.CSRF);
      data.append('membre_id', sel.dataset.membre);
      data.append('tontine_id', sel.dataset.tontine);
      data.append('mois', sel.dataset.mois);
      data.append('annee', sel.dataset.annee);
      data.append('statut', sel.value);
      const previous = sel.dataset.prev || 'non_paye';

      fetch(window.BASE + 'cotisations/toggle', { method: 'POST', body: data })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res.ok) { throw new Error(res.message || 'Erreur'); }
          sel.dataset.prev = res.statut;
          // maj badge date
          const cell = document.querySelector('#date-' + sel.dataset.membre);
          if (cell) cell.textContent = res.date_paiement || '—';
          // maj total
          const totalEl = document.getElementById('totalPaye');
          if (totalEl) totalEl.textContent = res.total_paye;
          // style select
          sel.classList.toggle('paye', res.statut === 'paye');
          window.hdjToast('success', res.statut === 'paye' ? 'Cotisation validée' : 'Paiement annulé');
        })
        .catch(function (err) {
          sel.value = previous;
          window.hdjToast('error', err.message || 'Échec de la mise à jour');
        });
    });
  });

})();
