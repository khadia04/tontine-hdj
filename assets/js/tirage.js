/* TONTINE HDJ — Roue de tirage : animation, compte à rebours, confettis */
(function () {
  'use strict';
  const T = window.TIRAGE;
  if (!T) return;

  const wheel  = document.getElementById('wheel');
  const lights = document.getElementById('wheelLights');
  const count  = document.getElementById('wheelCount');
  const btn    = document.getElementById('btnTirage');

  const PALETTE = ['#0E6B57', '#14806A', '#0DBA98', '#1CC7A5'];

  // --- Construction des segments de la roue ---
  const items = T.eligibles.length ? T.eligibles : [{ nom: 'En attente', gain: 0 }];
  const n = items.length;
  const seg = 360 / n;
  let stops = [];
  items.forEach((it, i) => {
    const c = PALETTE[i % PALETTE.length];
    stops.push(`${c} ${i * seg}deg ${(i + 1) * seg}deg`);
  });
  wheel.style.background = `conic-gradient(${stops.join(',')})`;

  // Étiquettes (noms) réparties autour de la roue
  items.forEach((it, i) => {
    const lab = document.createElement('div');
    lab.className = 'wheel-label';
    const angle = i * seg + seg / 2;
    lab.style.transform = `rotate(${angle - 90}deg)`;   // étiquette disposée en rayon
    const span = document.createElement('span');
    span.textContent = it.nom.split(' ')[0];
    lab.appendChild(span);
    wheel.appendChild(lab);
  });

  // --- Ampoules lumineuses autour de la roue ---
  const NB_LIGHTS = 24;
  for (let i = 0; i < NB_LIGHTS; i++) {
    const b = document.createElement('span');
    b.className = 'bulb';
    const a = (2 * Math.PI / NB_LIGHTS) * i;
    // rayon en pourcentage du conteneur (placement responsive)
    b.style.left = (50 + 48 * Math.cos(a)) + '%';
    b.style.top = (50 + 48 * Math.sin(a)) + '%';
    b.style.animationDelay = (i * 0.06) + 's';
    lights.appendChild(b);
  }

  // --- Confettis (canvas léger, sans dépendance) ---
  function confetti(duration) {
    const cv = document.createElement('canvas');
    cv.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:9999;';
    document.body.appendChild(cv);
    const ctx = cv.getContext('2d');
    const resize = () => { cv.width = innerWidth; cv.height = innerHeight; };
    resize(); addEventListener('resize', resize);
    const colors = ['#0E6B57', '#0DBA98', '#1CC7A5', '#ffd54a', '#ffffff'];
    const parts = Array.from({ length: 160 }, () => ({
      x: Math.random() * cv.width, y: -20 - Math.random() * cv.height * 0.5,
      r: 4 + Math.random() * 6, c: colors[(Math.random() * colors.length) | 0],
      vy: 2 + Math.random() * 4, vx: -2 + Math.random() * 4,
      rot: Math.random() * 360, vr: -6 + Math.random() * 12
    }));
    const end = Date.now() + duration;
    (function frame() {
      ctx.clearRect(0, 0, cv.width, cv.height);
      parts.forEach(p => {
        p.x += p.vx; p.y += p.vy; p.rot += p.vr;
        ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot * Math.PI / 180);
        ctx.fillStyle = p.c; ctx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r * 0.6); ctx.restore();
      });
      if (Date.now() < end) requestAnimationFrame(frame);
      else cv.remove();
    })();
  }

  // --- Lancement du tirage ---
  let running = false;

  btn && btn.addEventListener('click', function () {
    if (running || btn.disabled) return;
    running = true;
    btn.disabled = true;
    lights.classList.add('on');

    // Compte à rebours 3 - 2 - 1
    let c = 3;
    count.classList.add('show');
    count.textContent = c;
    const tick = setInterval(() => {
      c--;
      if (c > 0) { count.textContent = c; bump(count); }
      else { clearInterval(tick); count.textContent = '🎲'; spin(); }
    }, 900);
  });

  function bump(el) { el.style.animation = 'none'; void el.offsetWidth; el.style.animation = 'countPop .9s ease'; }

  function arreter() {
    running = false;
    btn.disabled = false;
    count.classList.remove('show');
    lights.classList.remove('on');
  }

  function spin() {
    // Rotation spectaculaire pendant l'appel serveur (le tirage constitue un lot)
    const turns = 6 + Math.random() * 4;
    const finalDeg = turns * 360 + Math.random() * 360;
    wheel.style.transition = 'transform 4.2s cubic-bezier(.15,.85,.2,1)';
    wheel.style.transform = `rotate(${finalDeg}deg)`;

    const data = new FormData();
    data.append('_csrf', window.CSRF);
    data.append('tontine_id', T.tontineId);
    // Mois / année choisis dans les listes déroulantes (sinon valeurs par défaut)
    var moisSel  = document.getElementById('tirageMois');
    var anneeSel = document.getElementById('tirageAnnee');
    data.append('mois', moisSel ? moisSel.value : T.mois);
    data.append('annee', anneeSel ? anneeSel.value : T.annee);

    const anim = new Promise(res => setTimeout(res, 4300));
    const req = fetch(window.BASE + 'tirages/run', { method: 'POST', body: data }).then(r => r.json());

    Promise.all([anim, req]).then(([, res]) => {
      count.classList.remove('show');
      lights.classList.remove('on');
      if (!res.ok) {
        arreter();
        window.hdjToast('error', res.message || 'Tirage impossible');
        return;
      }
      reveal(res);
    }).catch(() => {
      arreter();
      window.hdjToast('error', 'Erreur réseau pendant le tirage');
    });
  }

  // Ajoute le tirage en tête de l'historique, sans recharger la page.
  function majHistorique(res) {
    var list = document.getElementById('histList');
    if (!list) return;
    var vide = document.getElementById('histEmpty');
    if (vide) vide.remove();

    var pvUrl = window.BASE + 'tirages/show/' + res.tirage_id;
    var badges = res.gagnants.map(function (g) {
      return '<span class="badge-hdj badge-win">' + g.nom + '</span>';
    }).join('');

    var card = document.createElement('div');
    card.className = 'hist-card';
    card.style.cssText = 'border:1px solid var(--line);border-radius:var(--radius-sm);padding:12px;margin-bottom:10px;';
    card.innerHTML =
      '<div class="d-flex justify-content-between align-items-center">' +
        '<b style="color:var(--hdj-dark);">' + res.periode + '</b>' +
        '<a href="' + pvUrl + '" target="_blank" class="icon-btn" title="PV">🖨️</a>' +
      '</div>' +
      '<small style="color:var(--muted);">' + res.date_label + ' · ' + res.nb + ' gagnant' + (res.nb > 1 ? 's' : '') + '</small>' +
      '<div class="mt-2 d-flex flex-wrap gap-1">' + badges + '</div>';
    list.insertBefore(card, list.firstChild);
  }

  // Met à jour les compteurs (Éligibles, Déjà gagnants, Tirages effectués).
  function majCompteurs(res) {
    var setVal = function (id, delta) {
      var el = document.getElementById(id);
      if (!el) return;
      var n = parseInt((el.textContent || '0').replace(/\D/g, ''), 10) || 0;
      el.textContent = Math.max(0, n + delta);
    };
    setVal('statEligibles', -res.nb);
    setVal('statGagnants', res.nb);
    setVal('statTirages', 1);

    // Retire du carrousel de la roue les gagnants désormais inéligibles.
    res.gagnants.forEach(function (g) {
      T.eligibles = (T.eligibles || []).filter(function (m) { return m.id !== g.id; });
    });
  }

  function reveal(res) {
    confetti(4000);
    const card = document.getElementById('resultCard');
    const list = document.getElementById('resList');
    const total = document.getElementById('resTotal');
    const pv = document.getElementById('resPv');
    total.textContent = res.total;
    pv.href = T.base + res.tirage_id;
    list.innerHTML = '';
    res.gagnants.forEach((g, i) => {
      const row = document.createElement('div');
      row.className = 'list-row';
      row.style.animation = 'fadeUp .5s ease both';
      row.style.animationDelay = (i * 0.12) + 's';
      const ini = (g.nom || '?').trim().charAt(0).toUpperCase();
      row.innerHTML =
        '<span class="avatar-sm">' + ini + '</span>' +
        '<div class="meta"><b>' + g.nom + '</b><small>🏆 Gagnant</small></div>';
      list.appendChild(row);
    });
    card.style.display = 'block';
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });

    majHistorique(res);
    majCompteurs(res);

    Swal.fire({
      icon: 'success',
      title: '🎉 Tirage réussi !',
      html: '<b>' + res.nb + '</b> gagnant(s) · ' + res.total + ' distribué(s)',
      confirmButtonColor: '#0E6B57',
      confirmButtonText: 'Voir les résultats'
    });
  }
})();
