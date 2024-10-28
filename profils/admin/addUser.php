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
    <script>
    // Fonction pour remplir le select Profile 2 en fonction du Profile 1 sélectionné
    function updateProfile2() {
        // Obtenir la valeur du profil 1 sélectionné
        const profile1 = document.querySelector('select[name="profil1"]').value;

        // Sélecteur du profil 2
        const profile2Select = document.querySelector('select[name="profil2"]');

        // Effacer les anciennes options
        profile2Select.innerHTML = '<option value="" disabled selected>Choisir...</option>';

        // Liste des options en fonction du profil 1
        let options = [];

        if (profile1 === 'DST') {
            options = ['DST', 'S.E.M'];
        } else if (profile1 === 'Atelier') {
            options = ['chef d\'atelier'];
        } else if (profile1 === 'Residence') {
            options = ['PAV_A', 'PAV_B', 'PAV_C', 'PAV_D', 'PAV_E', 'PAV_F', 'PAV_G', 'PAV_H', 'PAV_I', 'PAV_J',
                'PAV_K', 'PAV_L', 'PAV_M', 'PAV_N', 'PAV_O', 'PAV_P', 'PAV_Q', 'PAV_R'
            ];
        } else if (profile1 === 'Admin') {
            options = ['Admin'];
        } else if (profile1 === 'Section') {
            options = ['Plomberie', 'Maçonnerie', 'Électricité', 'Menuserie_bois', 'Menuserie_allu',
                'Menuserie_metallique', 'Froid', 'Peinture'
            ];
        }

        // Ajouter les nouvelles options à Profile 2
        options.forEach(option => {
            const newOption = document.createElement('option');
            newOption.value = option;
            newOption.textContent = option;
            profile2Select.appendChild(newOption);
        });
    }
    </script>
</head>

<body>
    <?php include('../../head.php'); ?>
    <div class="container" style="width:50%;">
        <div class="contact__form1">
            <form class="justify-content-center" method="POST" action="./../../traitement/traitement">
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
                                <p>Username</p>
                                <input type="text" name="username" placeholder="entrez le nom utilisateur" required class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Nom</p>
                                <input type="text" name="nom" required class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Prenom</p>
                                <input type="text" name="prenom" required class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Tèlèphone</p>
                                <input type="text" name="telephone" placeholder="telephone: 00 000 00 00" required class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Email</p>
                                <input type="text" name="email" placeholder="example@gmail.com" required class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Mot de Passe</p>
                                <input type="password" name="password" id="password" required class="form-control"
                                    placeholder="Entrez le mot de passe">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Confirmer le Mot de Passe</p>
                                <input type="password" name="confirm_password" id="confirm_password" required
                                    class="form-control" placeholder="Confirmez le mot de passe"
                                    onblur="checkPasswords()">
                                <!-- Balise <i> pour afficher l'erreur -->
                                <i id="error-message" style="color: red; display: none;">Les mots de passe ne
                                    correspondent pas</i>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Profile 1</p>
                                <select name="profil1" required class="form-select"
                                    style="background-color: rgba(50, 115, 220, 0.1);" onchange="updateProfile2()">
                                    <option value="" disabled selected>Choisir...</option>
                                    <option value="Residence">Résidence</option>
                                    <option value="Section">Section</option>
                                    <option value="Atelier">Atelier</option>
                                    <option value="DST">DST</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>Profile 2</p>
                                <select name="profil2" required class="form-select"
                                    style="background-color: rgba(50, 115, 220, 0.1);">
                                    <option value="" disabled selected>Choisir...</option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <div class="form-field">
                        <button type="submit" class=" btn--primary"><strong>ENREGISTRER</strong></button>
                        <br><br>
                        <center> <a href="javascript:history.back()">Retour</a> </center>
                    </div>

                </fieldset>
            </form>

        </div>
    </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->

    <script>
        function checkPasswords() {
            // Récupérer les valeurs des champs de mot de passe
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var errorMessage = document.getElementById("error-message");

            // Vérifier si les mots de passe correspondent
            if (password !== confirmPassword) {
                errorMessage.style.display = "block"; // Afficher le message d'erreur
            } else {
                errorMessage.style.display = "none"; // Cacher le message d'erreur si les mots de passe correspondent
            }
        }
    </script>



    <script src="../../assets/js/jquery-3.2.1.min.js"></script>
    <script src="../../assets/js/plugins.js"></script>
    <script src="../../assets/js/main.js"></script>

</body>
<script src="../../assets/js/script.js"></script>

<?php include('../../footer.php'); ?>

</html>