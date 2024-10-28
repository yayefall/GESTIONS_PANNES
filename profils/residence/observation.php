<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
// Supprimer une variable de session spécifique
unset($_SESSION['classe']);
// Sélectionnez les options à partir de la base de données avec une pagination
include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');

$idp = isset($_GET['idp']) ? (int)$_GET['idp'] : null;
$idint = isset($_GET['idInt']) ? (int)$_GET['idInt'] : null;
$idObservation = isset($_GET['idObservation']) ? (int)$_GET['idObservation'] : null;

$evaluation = '';
$commentaire = '';

// Si en mode modification, charger les données de l'observation
if ($idObservation) {
    $sql = "SELECT evaluation_qualite, commentaire_suggestion FROM observation WHERE id = ?";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $idObservation);
    $stmt->execute();
    $result = $stmt->get_result();
    $observation = $result->fetch_assoc();
    $evaluation = $observation['evaluation_qualite'];
    $commentaire = $observation['commentaire_suggestion'];
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
    <div class="container" style="width:50%;">
        <div class="contact__form1">
            <form class="justify-content-center" method="POST" action="../../traitement/traitement" style="font-size: 20px; font-family: 'Times New Roman', Times, serif;">
                <tr>
                    <td colspan="4">
                        <center>
                            <strong>VEUILLEZ RENSEIGNER LES CHAMPS</strong>
                        </center>
                    </td>
                </tr>
                <fieldset>
                    <table>
                        <tr>
                            <td>
                                <strong>Evaluation :</strong>
                                <select name="evaluation" required class="form-select" style="background-color: rgba(50, 115, 220, 0.1);">
                                    <option></option>
                                    <option value="Fait" <?php echo ($evaluation == 'Fait') ? 'selected' : ''; ?>>Fait</option>
                                    <option value="Inachevee" <?php echo ($evaluation == 'Inachevee') ? 'selected' : ''; ?>>Inachevèe</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Commentaire ou Suggestion</strong>
                                <textarea name="commentaire" required class="form-control" rows="3" style="height: 90px;font-size:15px;background-color: rgba(50, 115, 220, 0.1);"><?php echo htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </td>
                        </tr>
                    </table>

                    <div class="form-field">
                        <input type="hidden" name="idIntervention" value="<?php echo htmlspecialchars($idint, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="idPanne" value="<?php echo htmlspecialchars($idp, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="idObservation" value="<?php echo htmlspecialchars($idObservation, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn--primary"><strong>ENREGISTRER</strong></button>
                        <br><br>
                        <center> <a href="javascript:history.back()">Retour</a> </center>
                    </div>

                </fieldset>
            </form>

        </div>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->


    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>

</body>
<script src="../../assets/js/script.js"></script>

<?php include('../../footer.php'); ?>
</html>
