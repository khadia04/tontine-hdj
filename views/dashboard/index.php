<?php /** Dashboard — TONTINE HDJ */ ?>

<!-- Cartes statistiques -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat stat-feature animate-up">
      <div class="ic"><?= ic('cash') ?></div>
      <div class="lab">Montant du mois</div>
      <div class="val sm"><?= e(fcfa($montantMois)) ?></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat animate-up delay-1">
      <div class="ic"><?= ic('users') ?></div>
      <div class="lab">Membres</div>
      <div class="val"><?= e(nb($nbMembres)) ?></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat animate-up delay-2">
      <div class="ic"><?= ic('trophy') ?></div>
      <div class="lab">Gagnants</div>
      <div class="val"><?= e(nb($nbGagnants)) ?></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat animate-up delay-3">
      <div class="ic"><?= ic('calendar') ?></div>
      <div class="lab">Restant à tirer</div>
      <div class="val"><?= e(nb($nbRestants)) ?></div>
    </div>
  </div>
</div>

<!-- Graphiques -->
<div class="row g-3 mb-4">
  <div class="col-12 col-xl-8">
    <div class="card-hdj animate-up">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-1">Paiements mensuels</h2>
        <p style="color:var(--muted);font-size:.85rem;margin:0 0 14px;">Cotisations encaissées — année <?= date('Y') ?></p>
        <canvas id="chartPaiements" height="110"></canvas>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card-hdj animate-up delay-2">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-1">Répartition des catégories</h2>
        <p style="color:var(--muted);font-size:.85rem;margin:0 0 14px;">Cotisation mensuelle des membres</p>
        <?php if (!empty($catData)): ?>
          <canvas id="chartCategories" height="220"></canvas>
        <?php else: ?>
          <div class="empty"><div class="ic"><?= ic('users') ?></div>Aucun membre enregistré.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card-hdj animate-up">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-1">Progression des tontines</h2>
        <p style="color:var(--muted);font-size:.85rem;margin:0 0 14px;">Part des membres ayant déjà gagné</p>
        <?php if (!empty($progRows)): ?>
          <canvas id="chartProgression" height="90"></canvas>
        <?php else: ?>
          <div class="empty"><div class="ic"><?= ic('layers') ?></div>Aucune tontine pour le moment.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Listes récentes -->
<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card-hdj animate-up">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Tontines actives</h2>
        <?php if (empty($tontinesActives)): ?>
          <p style="color:var(--muted);font-size:.88rem;">Aucune tontine active.</p>
        <?php else: foreach ($tontinesActives as $t):
          $pct = $t['nb_membres'] > 0 ? round(100 * $t['nb_gagnants'] / $t['nb_membres']) : 0; ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between" style="font-size:.9rem;font-weight:700;color:var(--hdj-dark);">
              <a href="<?= url('tontines/show/' . $t['id']) ?>" style="color:inherit;"><?= e($t['nom']) ?></a>
              <span><?= e(fcfa($t['montant_mensuel'])) ?></span>
            </div>
            <div class="progress-hdj mt-2"><span style="width:<?= $pct ?>%"></span></div>
            <small style="color:var(--muted);"><?= (int)$t['nb_gagnants'] ?>/<?= (int)$t['nb_membres'] ?> gagnants · <?= $pct ?>%</small>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card-hdj animate-up delay-1">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Dernières cotisations</h2>
        <?php if (empty($dernieresCotis)): ?>
          <p style="color:var(--muted);font-size:.88rem;">Aucune cotisation enregistrée.</p>
        <?php else: foreach ($dernieresCotis as $c): ?>
          <div class="list-row">
            <span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($c['nom_complet'], 0, 1))) ?></span>
            <div class="meta">
              <b><?= e($c['nom_complet']) ?></b>
              <small><?= e($c['tontine_nom']) ?> · <?= e(dateFr($c['date_paiement'])) ?></small>
            </div>
            <span class="amt">+<?= e(fcfa($c['montant'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card-hdj animate-up delay-2">
      <div class="cardbody">
        <h2 class="card-title-hdj mb-3">Derniers gagnants</h2>
        <?php if (empty($derniersGagnants)): ?>
          <p style="color:var(--muted);font-size:.88rem;">Aucun tirage effectué.</p>
        <?php else: foreach ($derniersGagnants as $g): ?>
          <div class="list-row">
            <span class="avatar-sm"><?= e(mb_strtoupper(mb_substr($g['nom_complet'], 0, 1))) ?></span>
            <div class="meta">
              <b><?= e($g['nom_complet']) ?></b>
              <small><?= e($g['tontine_nom']) ?></small>
            </div>
            <span class="amt">🏆 <?= e(fcfa($g['gain'])) ?></span>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
// Scripts des graphiques (injectés après le chargement de Chart.js)
ob_start(); ?>
<script>
(function(){
  const grad = (ctx) => {
    const g = ctx.createLinearGradient(0,0,0,260);
    g.addColorStop(0,'rgba(13,186,152,.55)'); g.addColorStop(1,'rgba(13,186,152,.02)');
    return g;
  };
  const mois = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];

  const elP = document.getElementById('chartPaiements');
  if (elP) new Chart(elP, {
    type:'line',
    data:{ labels:mois, datasets:[{ label:'Payé (FCFA)', data:<?= json_encode($paieParMois) ?>,
      borderColor:'#0E6B57', backgroundColor:(c)=>grad(c.chart.ctx), borderWidth:3, fill:true,
      tension:.4, pointBackgroundColor:'#0E6B57', pointRadius:4 }]},
    options:{ plugins:{legend:{display:false}}, scales:{ y:{ beginAtZero:true, ticks:{ callback:v=>v.toLocaleString('fr-FR') }, grid:{color:'#eef1f0'} }, x:{grid:{display:false}} } }
  });

  const elC = document.getElementById('chartCategories');
  if (elC) new Chart(elC, {
    type:'doughnut',
    data:{ labels:<?= json_encode($catLabels) ?>, datasets:[{ data:<?= json_encode($catData) ?>,
      backgroundColor:['#0E6B57','#14806A','#0DBA98','#1CC7A5','#7fe0cd'], borderWidth:0 }]},
    options:{ cutout:'62%', plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:11}}}} }
  });

  const elPr = document.getElementById('chartProgression');
  if (elPr) new Chart(elPr, {
    type:'bar',
    data:{ labels:<?= json_encode(array_map(fn($r)=>$r['nom'], $progRows)) ?>,
      datasets:[
        { label:'Gagnants', data:<?= json_encode(array_map(fn($r)=>(int)$r['gagnes'], $progRows)) ?>, backgroundColor:'#0DBA98', borderRadius:6 },
        { label:'En attente', data:<?= json_encode(array_map(fn($r)=>max(0,(int)$r['total']-(int)$r['gagnes']), $progRows)) ?>, backgroundColor:'#d8efe9', borderRadius:6 }
      ]},
    options:{ plugins:{legend:{position:'bottom',labels:{boxWidth:12}}}, scales:{ x:{stacked:true,grid:{display:false}}, y:{stacked:true,beginAtZero:true,grid:{color:'#eef1f0'},ticks:{precision:0}} } }
  });
})();
</script>
<?php $scripts = ob_get_clean(); ?>
