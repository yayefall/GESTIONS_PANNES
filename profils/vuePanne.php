<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
unset($_SESSION['classe']);

include('../traitement/fonction.php');
include('../traitement/requete.php');
include('../activite.php');

$userId = $_SESSION['id_user'];
$userRole = $_SESSION['profil'];
$idPanne = isset($_GET['idPanne']) ? (int)$_GET['idPanne'] : null;

// Afficher les détails d'une panne spécifique
$pannes = obtenirPanneParId($connexion, $idPanne);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="log.gif" type="image/x-icon">
    <link rel="icon" href="log.gif" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/base.css" />
    <link rel="stylesheet" href="../assets/css/vendor.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <link rel="stylesheet" href="../assets/css/login.css" />
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/js/bootstrap.min.js">
    <link rel="stylesheet" href="../assets/bootstrap/js/bootstrap.bundle.min.js">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
    .urgence-faible {
        background-color: green;
        color: white;
    }

    .urgence-moyenne {
        background-color: orange;
        color: white;
    }

    .urgence-haute {
        background-color: red;
        color: white;
    }
    th{
        text-align:center;
    }
    </style>

</head>

<body>
    <?php include('../head.php'); ?>
    <br>
    <ul class="nav justify-content-center">
        <li class="nav-item">
            <strong>Panne N°<?php echo htmlspecialchars($idPanne); ?></strong>
        </li>
    </ul>
    <br>
    <div id="refreshedContent" class="container">

        <table class="table table-striped" style="font-size: 20px; font-family: 'Times New Roman', Times, serif;">
            <tbody>
                <tr>
                    <td colspan="2" style="text-align: center;">DETAIL PANNE</td>
                </tr>

                <?php if ($pannes): ?>
                <?php foreach ($pannes as $panne): ?>
                <tr>
                    <th scope="col">N° Panne</th>
                    <td><?php echo htmlspecialchars($panne['panne_id']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Type De Panne</th>
                    <td><?php echo htmlspecialchars($panne['type_panne']); ?></td>
                </tr>
                <tr>
                    <th scope="col">localisation Exact</th>
                    <td><?php echo htmlspecialchars($panne['localisation']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Niveau D'urgence</th>
                    <td>

                        <?php if ($panne['niveau_urgence'] == 'Faible'): ?>
                        <button class="btn btn-info" style="width:;height: 30px;">Faible</button>
                        <?php elseif($panne['niveau_urgence'] == 'Moyenne'): ?>
                        <button class="btn btn-warning" style="width:;height: 30px;">Moyenne</button>
                        <?php elseif($panne['niveau_urgence'] == 'Èlevèe'): ?>
                        <button class="btn btn-danger" style="width:;height: 30px;">Èlevèe</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="col">Date de Declaration</th>
                    <td><?php  echo htmlspecialchars($panne['date_enregistrement']);  ?></td>
                </tr>
                <tr>
                    <th scope="col">Description Panne</th>
                    <td><?php echo htmlspecialchars($panne['panne_description']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Resultat</th>
                    <td>
                        <?php if ($panne['resultat'] == 'depanner'): ?>
                        <button class="btn btn-success" disabled style="width:;height: 30px;">Dépanné</button>
                        <?php elseif ($panne['resultat'] == 'en cours'): ?>
                        <button class="btn btn-warning btn-depanner" style="width:;height: 30px;">en cours...</button>
                        <?php else: ?>
                        <button class="btn btn-danger" disabled style="width:;height: 30px;">Non Depanner</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($_SESSION['profil'] != 'residence' && $panne['date_imputation'] != null) : ?>
                <tr>
                    <td colspan="2" style="text-align: center;">INTRUCTIONS CHEF S.E.M </td>
                </tr>

                <tr>
                    <th scope="col">Date D'imputation:</th>
                    <td><?php echo htmlspecialchars($panne['date_imputation']); ?></td>
                </tr>
                <tr>
                    <th scope="col">instruction:</th>
                    <td><?php echo htmlspecialchars($panne['instruction']); ?></td>
                </tr>
                <?php if ($_SESSION['profil'] == 'sem' && $panne['date_imputation'] != null) : ?>
                <tr>
                    <th scope="col">Action:</th>
                    <td>
                        <button class="btn btn-info" style="width:; height: 30px;"
                            onclick="checkPanneStatus('<?php echo $panne['resultat']; ?>', 'Modifier','<?php echo $panne['imputation_id']; ?>')">Modifier</button>
                        <button class="btn btn-danger" style="width: ; height: 30px;"
                            onclick="checkPanneStatus('<?php echo $panne['resultat']; ?>', 'Supprimer','<?php echo $panne['imputation_id']; ?>')">Supprimer</button>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>

                <?php if ($panne['date_intervention'] != null) : ?>
                <tr>
                    <td colspan="2" style="text-align: center;">INTERVENTION ATELIER </td>
                </tr>

                <tr>
                    <th scope="col">Date D'intervention</th>
                    <td><?php echo htmlspecialchars($panne['date_intervention']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Description des Actions</th>
                    <td><?php echo htmlspecialchars($panne['description_action']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Responsable de l'Intervention</th>
                    <td><?php echo htmlspecialchars($panne['personne_agent']); ?></td>
                </tr>
                <?php if ($_SESSION['profil'] == 'atelier' && $panne['date_intervention'] != null) : ?>
                <tr>
                    <th scope="col">Action:</th>
                    <td>
                        <button class="btn btn-info" style="width:; height: 30px;"
                            onclick="checkPanneStatus('<?php echo $panne['evaluation_qualite']; ?>', 'Modifier','<?php echo $panne['intervention_id']; ?>')">Modifier</button>
                        <button class="btn btn-danger" style="width: ; height: 30px;"
                            onclick="checkPanneStatus('<?php echo $panne['evaluation_qualite']; ?>', 'Supprimer','<?php echo $panne['intervention_id']; ?>')">Supprimer</button>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($panne['evaluation_qualite'] != null) : ?>

                <tr>
                    <td colspan="2" style="text-align: center;">OBSERVATIONS CHEF RESIDENCE</td>
                </tr>
                <tr>
                    <th scope="col">Evaluation :</th>
                    <td><?php echo htmlspecialchars($panne['evaluation_qualite']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Date d'Observation :</th>
                    <td><?php echo htmlspecialchars($panne['date_observation']); ?></td>
                </tr>
                <tr>
                    <th scope="col">Commentaires et Suggestion :</th>
                    <td><?php echo htmlspecialchars($panne['commentaire_suggestion']); ?></td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">Aucune panne trouvée ou erreur.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>


    </div>
    <ul class="nav justify-content-center">
        <li class="nav-item">
            <a class="nav-link active" href="javascript:history.back()"><button
                    class="btn btn-success">Retour</button></a>
        </li>
    </ul>


    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>Confirmation</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <strong>Voulez-vous vraiment supprimer cette Imputation ?</strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="imputForm" method="GET" action="../traitement/traitement.php">
                        <input type="hidden" name="imputation_id" id="ImputationIdInput" value="">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Structure -->
    <div class="modal fade" id="interventionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>dèsoler !</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>Intervention en cours</h4>
                    <p>Cette panne fait déjà l'objet d'une intervention.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Structure -->
    <div class="modal fade" id="chefAtelierModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>dèsoler !</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4>depannage en cours</h4>
                    <p>Cette panne fait déjà l'objet d'une Observation Chef Residence.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="chefAtelierDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"><strong>Confirmation</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <strong>Voulez-vous vraiment supprimer cette Intervention ?</strong>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form id="imputForm" method="GET" action="../traitement/traitement.php">
                        <input type="hidden" name="intervention_id" id="InterventionIdInput" value="">
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
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
    <script>
    // Exemple de variable pour simuler le rôle de l'utilisateur (à remplacer par votre méthode d'authentification)
    const userRoles = '<?php echo htmlspecialchars($userRole, ENT_QUOTES, 'UTF-8'); ?>';

    function checkPanneStatus(resultat, action, Id) {
        if (userRoles === 'atelier') {
            // Comportement pour le chef d'atelier
            if (resultat == 'Fait' || resultat == 'Inachevee') {
                showChefAtelierModal(); // Modal spécifique pour le chef d'atelier
            } else {
                // Comportement pour le chef d'atelier pour d'autres actions
                if (action === 'Supprimer') {
                    showChefAtelierDeleteModal(Id); // Modal spécifique pour suppression
                } else if (action === 'Modifier') {
                    window.location.href = 'dst/intervention.php?intervention_id=' + Id; // Redirection spécifique
                }
            }
        }
        if (userRoles === 'sem') {
            // Comportement pour les autres rôles
            if (resultat == 'en cours' || resultat == 'depanner') {
                showInterventionModal();
            } else {
                if (action === 'Supprimer') {
                    showDeleteModal(Id);
                } else if (action === 'Modifier') {
                    window.location.href = 'dst/imputation.php?imputation_id=' + Id; // Redirection spécifique
                }
            }
        }
    }

    function showInterventionModal() {
        const interventionModal = new bootstrap.Modal(document.getElementById('interventionModal'));
        interventionModal.show();
    }

    function showDeleteModal(imputationId) {
        const deleteModal = new bootstrap.Modal(document.getElementById('exampleModal'));
        document.getElementById('ImputationIdInput').value = imputationId;
        deleteModal.show();
    }

    function showChefAtelierModal() {
        // Modal spécifique pour le chef d'atelier
        const chefAtelierModal = new bootstrap.Modal(document.getElementById('chefAtelierModal'));
        chefAtelierModal.show();
    }

    function showChefAtelierDeleteModal(imputationId) {
        // Modal spécifique pour la suppression par le chef d'atelier
        const chefAtelierDeleteModal = new bootstrap.Modal(document.getElementById('chefAtelierDeleteModal'));
        document.getElementById('InterventionIdInput').value = imputationId;
        chefAtelierDeleteModal.show();
    }
    </script>



    <script src="../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

</body>
<script src="../../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
</script>

<?php include('../footer.php'); ?>

</html>