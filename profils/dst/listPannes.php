<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/panne/');
    exit();
}
unset($_SESSION['classe']);

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
$userId = $_SESSION['id_user'];
$profil2 = $_SESSION['profil2'];
$profil1 = $_SESSION['profil'];
// Vérifiez le profil et définissez les variables appropriées
$isSEM = ($profil2 === 'S.E.M');
$isDst = ($profil2 === 'chef dst');
$dst = ($profil1 === 'dst');

// Nombre de lignes par page
$limit = 10;
// Numéro de la page actuelle (par exemple, à partir d'une requête GET)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    // Recherche par mot-clé dans toutes les colonnes
    $result = allPannes($connexion, $page = 1, $limit = 10, $profil2 = null, $search, $dst);
    $allPannes = $result['pannes'];
    $totalPannes = $result['total_count'];
    $totalPages = $result['total_pages'];
} else {
    $result = allPannes($connexion, $page = 1, $limit = 10, $profil2 = null, $search, $dst);
    $allPannes = $result['pannes'];
    $totalPannes = $result['total_count'];
    $totalPages = ceil($totalPannes / $limit);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../../assets/css/vendor.css" />
    <link rel="stylesheet" href="../../assets/css/main.css" />
    <link rel="stylesheet" href="../../assets/css/login.css" />
    <link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css">
    <!-- script================================================== -->
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../../assets/bootstrap/js/bootstrap.bundle.min.js">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="../assets/js/modernizr.js"></script>
    <script src="../../assets/js/pace.min.js"></script>
</head>

<body>
    <?php include('../../head.php'); ?>
    <br>
    <nav class="navbar navbar-light bg">
        <div class="container">

            <ul class="nav">
                <form class="d-flex" method="GET" action="">
                    <li class="nav-item">
                        <strong class="nav-link active" aria-current="page">
                            <input class="form-control me-2" type="search" name="search" placeholder="recherche"
                                aria-label="Search">
                        </strong>
                    </li>
                    <li class="nav-item">
                        <strong class="nav-link" href="#">
                            <button type="submit" class="btn btn-primary mb-3"
                                style="width: 100%; height:50px ">Rechercher</button>
                        </strong>
                    </li>
                </form>
                <li class="nav-item">
                    <a class="nav-link" href="listPannes">
                        <button type="reset" class="btn btn-primary mb-3"
                            style="width: 100%; height:50px ">Reinitialiser</button>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <br>
    <div id="refreshedContent" class="container-fluid">
        <div class="table-responsive">
            <table class="table table-striped" style="font-size: 20px; font-family: 'Times New Roman', Times, serif;">
                <thead>
                    <tr>
                        <th scope="col"><b>N°</b></th>
                        <th scope="col"><b>Type_Panne</b></th>
                        <th scope="col"><b>Localisation</b></th>
                        <th scope="col"><b>Niveau D'Urgence</b></th>
                        <th scope="col"><b>Date Panne</b></th>
                        <th scope="col"><b>Ètat</b></th>
                        <th scope="col"><b>Voir</b></th>
                        <?php if ($_SESSION['profil'] == 'sem' || $_SESSION['profil'] == 'dst') : ?>
                        <th scope="col"><b>Imputer</b></th>
                        <?php endif; ?>
                        <?php if ($_SESSION['profil'] == 'atelier') : ?>
                        <th scope="col"><b>Intervenir</b></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($allPannes): ?>
                    <?php foreach ($allPannes as $panne): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($panne['id']); ?></td>
                        <td><?php echo htmlspecialchars($panne['type_panne']); ?></td>
                        <td><?php echo htmlspecialchars($panne['localisation']); ?></td>
                        <!-- <td><?php// echo htmlspecialchars($panne['description']); ?></td> -->
                        <td>

                            <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                            <button class="btn btn-info" style="width:40%;height: 30px;">Faible</button>
                            <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                            <button class="btn btn-warning" style="width:40%;height: 30px;">Moyenne</button>
                            <?php elseif($panne['niveau_urgence'] == 'Èlevèe'): ?>
                            <button class="btn btn-danger" style="width:40%;height: 30px;">Èlevèe</button>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($panne['date_enregistrement']); ?></td>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <button class="btn btn-success" disabled style="width:60%;height: 30px;">Dépanné</button>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <button class="btn btn-warning" disabled style="width:60%;height: 30px;">en
                                cours...</button>
                            <?php else: ?>
                            <?php if ($_SESSION['profil'] == 'atelier'): ?>
                            <a href="intervention?idp=<?php echo $panne['id']; ?>">
                                <button class="btn btn-danger" style="width:60%;height: 30px;">Non Depanner</button>
                            </a>
                            <?php else: ?>
                            <button disabled class="btn btn-danger" style="width:60%;height: 30px;">Non
                                Depanner</button>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="../vuePanne.php?idPanne=<?php echo htmlspecialchars($panne['id']); ?>">
                                <button type="button" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                        color="blue" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                                        <path
                                            d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
                                    </svg>
                                </button>
                            </a>
                        </td>
                        <td>

                            <?php if ($isSEM): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <button disabled type="button" class="btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="25" fill="currentColor"
                                    color="grow" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
                                    <path fill-rule="evenodd"
                                        d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                </svg>
                            </button>
                            <?php else: ?>
                            <a href="imputation.php?idPanne=<?php echo htmlspecialchars($panne['id']); ?>">
                                <button type="button" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="25" fill="currentColor"
                                        color="green" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
                                        <path fill-rule="evenodd"
                                            d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                    </svg>
                                </button>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($isDst): ?>
                            <?php if ($panne['resultat_imp'] === 'imputer'): ?>
                            <button disabled type="button" class="btn">
                               <strong style="color:green;"> OUI</strong>
                            </button>
                            <?php else: ?>
                            <button disabled type="button" class="btn">
                            <strong style="color:red;"> NON</strong>
                            </button>
                            <?php endif; ?>
                            <?php endif; ?>
                            <?php if($_SESSION['profil'] == 'atelier'):?>

                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <button disabled type="button" class="btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="25" fill="currentColor"
                                    color="grow" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
                                    <path fill-rule="evenodd"
                                        d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                </svg>
                            </button>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <button disabled type="button" class="btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="25" fill="currentColor"
                                    color="grow" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
                                    <path fill-rule="evenodd"
                                        d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                </svg>
                            </button>
                            <?php else: ?>
                            <a href="intervention?idp=<?php echo $panne['id']; ?>">
                                <button type="button" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="25" fill="currentColor"
                                        color="green" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z" />
                                        <path fill-rule="evenodd"
                                            d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                                    </svg>
                                </button>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7">Aucune panne trouvée ou erreur.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-end">
                <!-- Bouton Previous -->
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <?php endif; ?>

                <!-- Numéros de page -->
                <li class="page-item active">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $page; ?></a>
                </li>

                <!-- Bouton Next -->
                <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
                <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"><strong>Confirmation</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <strong>Voulez-vous vraiment dépanner cette panne ?</strong>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form id="depannerForm" method="post" action="../../traitement/traitement">
                            <input type="hidden" name="panne_id" id="panneIdInput" value="">
                            <input type="hidden" name="action" value="depanner">
                            <button type="submit" class="btn btn-primary">Confirmer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal pour la confirmation de suppression -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel"><strong>Confirmation</strong></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <strong>Voulez-vous vraiment supprimer cette panne ?</strong>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form id="deleteForm" method="post" action="../../traitement/traitement">
                            <input type="hidden" name="panneDelete" id="deletePanneIdInput" value="">
                            <input type="hidden" name="action" value="deletePanne">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>



    <script>
    document.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('.btn-deleteBtn').forEach(button => {
            button.addEventListener('click', () => {
                const panneId = button.getAttribute('data-panne-id');
                document.getElementById('deletePanneIdInput').value = panneId;
            });
        });

        document.querySelectorAll('.btn-depanner').forEach(button => {
            button.addEventListener('click', () => {
                const panneId = button.getAttribute('data-panne-id');
                document.getElementById('panneIdInput').value = panneId;
            });
        });
    });
    </script>


    <!-- Inclure jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>


    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>

</body>
<script src="../../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>


<?php include('../../footer.php'); ?>

</html>