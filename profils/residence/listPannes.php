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

// Nombre de lignes par page
$limit = 10;
// Numéro de la page actuelle (par exemple, à partir d'une requête GET)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search) {
    // Recherche par mot-clé dans toutes les colonnes
    $result = rechercherPannesParMotCle($connexion, $userId, $search, $page = 1, $limit = 10);
    $allPannes = $result['pannes'];
    $totalPannes = $result['total_count'];
    $totalPages = $result['total_pages'];
} else {
    $result = allPannesByUser($connexion, $userId, $page = 1, $limit = 10);
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

            <ul class="nav justify-content-end">
                <li class="nav-item">
                    <a class="nav-link active" href="ajoutPanne.php">
                        <button type="button" class="btn btn-success btn-lg">AJOUTER-PANNE</button>
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
                        <th scope="col"><b>Type</b></th>
                        <th scope="col"><b>Localisation</b></th>
                        <th scope="col"><b>Niveau D'Urgence</b></th>
                        <th scope="col"><b>Date</b></th>
                        <th scope="col"><b>Ètat</b></th>
                        <th scope="col"><b>Supr</b></th>
                        <th scope="col"><b>Obs</b></th>
                        <th scope="col"><b>Voir</b></th>
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
                        <td><?php  echo htmlspecialchars($panne['date_enregistrement']);  ?></td>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner'): ?>
                            <button class="btn btn-success" disabled style="width:60%;height: 30px;">Dépanné</button>
                            <?php elseif ($panne['resultat'] == 'en cours'): ?>
                            <button class="btn btn-warning" style="width:60%;height: 30px;">en cours...</button>
                            <?php else: ?>
                            <button disabled class="btn btn-danger" style="width:60%;height: 30px;">Non
                                Depanner</button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($panne['resultat'] == 'depanner' || $panne['resultat'] == 'en cours' || $panne['instruction'] != null ): ?>
                            <button type="button" class="btn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                    class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                                </svg>
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-deleteBtn deleteBtn" data-bs-toggle="modal"
                                data-bs-target="#deleteModal"
                                data-panne-id="<?php echo htmlspecialchars($panne['id']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor"
                                    color="red" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                                </svg>
                            </button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($panne['resultat'] == 'en cours' || $panne['resultat'] == 'depanner'): ?>
                            <a
                                href="observation?idp=<?php echo $panne['id']; ?>&idInt=<?php echo $panne['idIntervention']; ?>&idObservation=<?php echo $panne['idObservation']; ?>">
                                <button type="button" class="btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" color="green"
                                        fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path
                                            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                        <path fill-rule="evenodd"
                                            d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                    </svg>
                                </button>
                            </a>
                            <?php else: ?>
                            <button disabled type="button" class="btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" color="grow"
                                    fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                    <path
                                        d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                    <path fill-rule="evenodd"
                                        d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                </svg>
                            </button>
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