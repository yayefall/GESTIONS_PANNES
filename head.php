<?php
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
  header('Location: /COUD/panne/');
  exit();
}
include('activite.php');
require_once(__DIR__ . '/traitement/fonction.php');
if ($_SESSION['profil'] == 'user') {
  $inforequeteAffectEtu = getStudentChoiseLit($_SESSION['id_etu']);

  $affecter = 0;
  while ($row = $inforequeteAffectEtu->fetch_assoc()) {
    $affecter++;
  }
  $resultatReqLitEtu = getOneLitByStudent($_SESSION['id_etu']);
}
?>

<head>
  <!--- basic page needs================================================== -->
  <meta charset="utf-8" />
  <title>GESCOUD</title>
  <meta name="description" content="" />
  <meta name="author" content="" />

  <!-- mobile specific metas================================================== -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- CSS================================================== -->
  <link rel="stylesheet" href="assets/css/base.css" />
  <link rel="stylesheet" href="assets/css/vendor.css" />
  <link rel="stylesheet" href="assets/css/main.css" />

  <!-- script================================================== -->
  <script src="../assets/js/modernizr.js"></script>
  <script src="assets/js/pace.min.js"></script>

  <!-- favicons================================================== -->
  <link rel="shortcut icon" href="log.gif" type="image/x-icon" />
  <link rel="icon" href="log.gif" type="image/x-icon" />
</head>

<body id="top">
  <!-- header================================================== -->
  <header class="s-header">
    <div class="header-logo">
      <a class="site-logo" href="#"><img src="/COUD/panne/assets/images/logo.png" alt="Homepage" /></a>
      GESCOUD
    </div>
    <nav class="header-nav-wrap">
      <ul class="header-nav">
        <li class="nav-item">
          <a class="nav-link" href="/COUD/panne/" title="Déconnexion"><i class="fa fa-sign-out" aria-hidden="true"></i> Déconnexion</a>
        </li>
      </ul>
    </nav>

    <a class="header-menu-toggle" href="#0"><span>Menu</span></a>
  </header>
  <!-- end s-header -->
</body>
<section id="homedesigne" class="s-homedesigne">
  <?php if (
     ($_SESSION['profil'] == 'chef_pavillon') ||
     ($_SESSION['profil'] == 'residence')||
     ($_SESSION['profil'] == 'dst')||
     ($_SESSION['profil'] == 'atelier')||
     ($_SESSION['profil'] == 'section') || 
     ($_SESSION['profil'] == 'sem') ||
     ($_SESSION['profil'] == 'admin')
     ) { ?>
    <p class="lead">Espace Administration: Bienvenue! <br> <br> <span>
        (<?= $_SESSION['prenom'] . "  " . $_SESSION['nom'] . " | " . $_SESSION['profil2'] ?>)
      </span></p>
  <?php } elseif ($_SESSION['profil'] == 'user') { ?>
    <p class="lead">Espace etudiant: Bienvenue! <br> <br> <span>
        (<?= $_SESSION['prenom'] . "  " . $_SESSION['nom'] ?>)
      </span><br><br><span><?= $_SESSION['classe']; ?></span></p>
  <?php } ?>
</section>