# TONTINE HDJ

Plateforme de gestion de tontines pour l'**Hôpital Dalal Jamm (HDJ)**.
Application web PHP 8.2 / MySQL / Bootstrap 5, prête pour **XAMPP** — sans framework, sans Composer.

---

## 1. Prérequis

- **XAMPP** (ou WAMP / MAMP) avec :
  - PHP **8.2** ou supérieur (testé jusqu'à 8.3)
  - MySQL / MariaDB
  - Apache avec le module **`mod_rewrite`** activé
- Un navigateur moderne (Chrome, Edge, Brave, Firefox).

> L'application crée **automatiquement** la base de données, les 6 tables et le compte administrateur au premier lancement. Aucune importation SQL manuelle n'est nécessaire.

---

## 2. Installation (3 minutes)

1. **Copier le dossier** `tontine-hdj` dans le répertoire `htdocs` de XAMPP :
   ```
   C:\xampp\htdocs\tontine-hdj
   ```

2. **Démarrer** Apache **et** MySQL depuis le panneau de contrôle XAMPP.

3. **Vérifier `mod_rewrite`** (généralement actif par défaut sous XAMPP) :
   - Dans `C:\xampp\apache\conf\httpd.conf`, la ligne `LoadModule rewrite_module modules/mod_rewrite.so` ne doit pas être commentée.
   - La directive `AllowOverride All` doit être présente pour le répertoire `htdocs`.
   - Redémarrer Apache après toute modification.

4. **Ouvrir** dans le navigateur :
   ```
   http://localhost/tontine-hdj/
   ```

5. Au premier chargement, la base `tontine_hdj` et les données de démonstration sont créées automatiquement.

---

## 3. Connexion

| Champ            | Valeur          |
|------------------|-----------------|
| **Identifiant**  | `admin`         |
| **Mot de passe** | `Admin@2026`    |

> ⚠️ **Clavier AZERTY** : le caractère `@` s'obtient avec **AltGr + à (0)**. Modifiez ce mot de passe après la première connexion via **Mon profil → Changer le mot de passe**.

Les deux rôles (**Super Administrateur** et **Président**) disposent exactement des mêmes permissions.

---

## 4. Données de démonstration

Au premier lancement, l'application crée :

- 1 compte super administrateur (`admin`)
- 1 tontine **« Tontine du Personnel HDJ »** — pot mensuel **5 000 000 FCFA**, durée **10 mois**
- **24 membres** répartis par catégorie de cotisation :
  - 4 × 100 000 (gain 1 000 000)
  - 4 × 75 000 (gain 750 000)
  - 8 × 50 000 (gain 500 000)
  - 8 × 25 000 (gain 250 000)

Vous pouvez tout supprimer et repartir de zéro depuis l'interface.

---

## 5. Utilisation

- **Dashboard** : statistiques globales + graphiques (répartition des catégories, paiements mensuels, progression des tontines).
- **Tontines** : créer / modifier / supprimer / consulter. La durée est configurable.
- **Membres** : le **gain est calculé automatiquement** = `cotisation mensuelle × durée de la tontine`. Photo optionnelle (JPG/PNG/WEBP/GIF, 3 Mo max).
- **Cotisations** : pour chaque mois, basculer chaque membre entre **Payé / Non payé** via un menu déroulant (enregistrement AJAX instantané de la date, du mois et de l'année).
- **Tirage** (la pièce maîtresse) :
  - Le moteur recherche **automatiquement** une combinaison de gagnants dont la **somme des gains est exactement égale au montant mensuel** de la tontine.
  - Plusieurs gagnants par tirage. Un membre ne gagne **qu'une seule fois** (`a_gagne = 1`) mais **continue de cotiser** jusqu'à la fin.
  - Roue animée, compte à rebours, effets lumineux et confettis.
  - Le bouton de lancement est désactivé tant qu'aucune combinaison valide n'existe (membres éligibles insuffisants).
- **Historique** : tous les tirages et gagnants sont conservés.

---

## 6. Exports

Cette version utilise des exports **natifs, sans dépendance** :

- **Excel / tableur** : export **CSV** (encodage UTF-8 avec BOM, ouverture directe dans Excel) pour les membres, cotisations et tirages.
- **PDF** : pages d'impression dédiées (**Procès-verbal**, **Rapport mensuel**) optimisées pour l'impression. Utilisez **Imprimer → Enregistrer au format PDF** du navigateur (mise en page propre, en-tête HDJ, zones de signature).

### Option : exports PDF/XLSX « binaires » via Composer

Si vous souhaitez générer directement des fichiers `.pdf` et `.xlsx` côté serveur, installez les bibliothèques suivantes (nécessite une connexion Internet) :

```bash
cd C:\xampp\htdocs\tontine-hdj
composer require dompdf/dompdf phpoffice/phpspreadsheet
```

Il faudra alors adapter `controllers/ExportController.php` pour utiliser ces bibliothèques au lieu du CSV / impression HTML. L'architecture actuelle est volontairement autonome pour rester immédiatement exploitable sous XAMPP.

---

## 7. Architecture (MVC léger)

```
tontine-hdj/
├── index.php              Front controller (point d'entrée unique)
├── .htaccess              Réécriture d'URL + protection des dossiers sensibles
├── config/
│   ├── config.php         Constantes (BD, chemins, session, admin par défaut)
│   └── database.php        Connexion PDO + création auto BD/tables/seed
├── core/
│   ├── App.php             Routeur
│   ├── Controller.php      Contrôleur de base (vues, JSON)
│   ├── Auth.php            Authentification / session
│   └── helpers.php         Fonctions utilitaires (e, url, fcfa, csrf, icônes…)
├── controllers/           Auth, Dashboard, Tontine, Membre, Cotisation, Tirage, Export, Profil
├── models/                Tontine, Membre, Cotisation, Tirage
├── views/                 layouts, auth, dashboard, tontines, membres, cotisations, tirages, profil, errors
├── assets/
│   ├── css/app.css        Design system premium HDJ
│   ├── js/app.js          Interactions globales (sidebar, AJAX cotisations, etc.)
│   ├── js/tirage.js        Moteur d'animation de la roue + confettis
│   └── img/               Logos HDJ
├── uploads/               Photos des membres (créées à l'usage)
└── sql/tontine_hdj.sql    Sauvegarde manuelle du schéma (optionnelle)
```

### Routage
URL propres (`/tontines/show/1`) via `mod_rewrite`. **Solution de repli** si la réécriture est indisponible : utiliser le paramètre `?r=` — par exemple `http://localhost/tontine-hdj/index.php?r=tontines/show/1`.

---

## 8. Configuration

Tout se règle dans **`config/config.php`** :

- Identifiants de la base (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) — par défaut `127.0.0.1` / `tontine_hdj` / `root` / *(vide)*, valeurs standard de XAMPP.
- `APP_DEBUG` : mettre à `false` en production.
- Identifiants de l'admin par défaut (créé uniquement si la table `users` est vide).

---

## 9. Sécurité

- Mots de passe hachés via `password_hash` (bcrypt).
- Protection **CSRF** sur tous les formulaires.
- Sessions sécurisées (httponly, SameSite=Lax, expiration sur inactivité de 4 h).
- Tirage basé sur `random_int` (générateur cryptographiquement sûr).
- Dossiers `config`, `core`, `models`, `controllers` protégés en accès direct via `.htaccess`.

---

## 10. Charte graphique

| Variable          | Couleur     |
|-------------------|-------------|
| `--hdj-primary`   | `#0E6B57`   |
| `--hdj-secondary` | `#0DBA98`   |
| `--hdj-accent`    | `#1CC7A5`   |
| `--hdj-dark`      | `#114538`   |
| `--hdj-emerald`   | `#14806A`   |
| `--hdj-light`     | `#F8FAF9`   |

---

*Développé pour l'Hôpital Dalal Jamm — Dakar, Sénégal.*
