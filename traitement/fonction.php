<?php
// Connectez-vous à votre base de données MySQL
function connexionBD()
{
    $connexion = mysqli_connect("localhost", "root", "", "supercoud_panne");
    // Vérifiez la connexion
    if ($connexion === false) {
        die("Erreur : Impossible de se connecter. " . mysqli_connect_error());
    }
    return $connexion;
}
$connexion = connexionBD();
//####################### Fonction pour obtenir les pannes enregistrées par l'utilisateur connecté ###########################
function allPannesByUser($connexion, $user_id, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat,i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.profil1,u.profil2,u.prenom, 
               o.evaluation_qualite,o.id AS idObservation,o.date_observation, o.commentaire_suggestion,m.instruction
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        LEFT JOIN Imputation m ON p.id = m.id_panne
        WHERE p.id_chef_residence = ?
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('iii', $user_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne
        WHERE id_chef_residence = ?
    ";
    $stmtCount = $connexion->prepare($sqlCount);
    $stmtCount->bind_param('i', $user_id);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    return ['pannes' => $pannes, 'total_count' => $totalCount];
}
// ###############       FIN DE LA FONCTION      ####################

//############## Fonction de connexion dans l'espace utilisateur ############################
function login($username, $password)
{
    global $connexion;
    // Hacher le mot de passe avec SHA-1
    $hashed_password = sha1($password);

    $users = "SELECT * FROM `utilisateur` where `username`='$username' and `password`='$hashed_password'";
    $info = $connexion->query($users);
    return $info->fetch_assoc();
}
// ###############       FIN DE LA FONCTION login     ####################


// ###############       DEBUT DE LA FONCTION POUR RECHERCHER LES PANNES UTLISATEUR PAR MOTS CLE     ####################
function rechercherPannesParMotCle($connexion, $userId, $searchTerm, $page = 1, $limit = 10) {
    $offset = ($page - 1) * $limit;
    $likeTerm = '%' . $searchTerm . '%';

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat,i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.profil1,u.profil2,u.prenom, 
               o.evaluation_qualite,o.id AS idObservation,o.date_observation, o.commentaire_suggestion
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        WHERE p.id_chef_residence = ? AND (
            p.type_panne LIKE ? OR
            p.localisation LIKE ? OR
            p.description LIKE ? OR
            p.niveau_urgence LIKE ? OR
            p.date_enregistrement LIKE ? OR
            i.resultat LIKE ? OR
            i.description_action LIKE ? OR
            i.personne_agent LIKE ? OR
            o.evaluation_qualite LIKE ? OR
            o.commentaire_suggestion LIKE ?
        )
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('issssssssssii', $userId, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes correspondant aux critères de recherche
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        WHERE p.id_chef_residence = ? AND (
            p.type_panne LIKE ? OR
            p.localisation LIKE ? OR
            p.description LIKE ? OR
            p.niveau_urgence LIKE ? OR
            p.date_enregistrement LIKE ? OR
            i.resultat LIKE ? OR
            i.description_action LIKE ? OR
            i.personne_agent LIKE ? OR
            o.evaluation_qualite LIKE ? OR
            o.commentaire_suggestion LIKE ?
        )
    ";

    $stmtCount = $connexion->prepare($sqlCount);
    $stmtCount->bind_param('issssssssss', $userId, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
//  #################  FIN DE LA  FONCTION     ##########################


//  #################   FONCTION POUR RECUPERER LES DETAILS DE LA PANNE    ##########################
function obtenirPanneParId($connexion, $panneId) {
    $sql = "
        SELECT 
            p.id AS panne_id, 
            p.type_panne, 
            p.date_enregistrement, 
            p.description AS panne_description, 
            p.localisation, 
            p.niveau_urgence,
            u.nom AS chef_nom, 
            u.profil1 AS chef_role,
            i.id AS intervention_id, 
            i.date_intervention, 
            i.description_action, 
            i.resultat, 
            i.personne_agent,
            o.id AS observation_id, 
            o.evaluation_qualite,
            o.date_observation, 
            o.commentaire_suggestion,
            m.id AS imputation_id,
            m.id_chef_dst,
            m.instruction,
            m.date_imputation
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Observation o ON p.id = o.id_panne
        LEFT JOIN Imputation m ON p.id = m.id_panne
        WHERE p.id = ?
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('i', $panneId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
//  ################# FIN DE LA  FONCTION  ##########################


// ###############       debut DE LA FONCTION insererPanne     ####################
function insertPanne($connexion, $type_panne, $date_enregistrement, $description, $localisation, $niveau_urgence, $id_chef_residence) {
    $sql = "
        INSERT INTO Panne (type_panne, date_enregistrement, description, localisation, niveau_urgence, id_chef_residence)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $connexion->prepare($sql);
    $stmt->bind_param('sssssi', $type_panne, $date_enregistrement, $description, $localisation, $niveau_urgence, $id_chef_residence);
    if ($stmt->execute()) {
        return $stmt->insert_id;
    } else {
        return false;
    }
}
// ###############       FIN DE LA FONCTION insererPanne     ####################

// ###############   DEBUT DE LA FONCTION enregistrerObservation   ###########################
function enregistrerObservation($connexion, $idPanne, $idUtilisateur, $idIntervention, $evaluationQualite, $date_observation, $commentaireSuggestion, $idObservation = null) {
    if ($idObservation) {
        // Mise à jour de l'observation existante
        $sql = "
            UPDATE observation
            SET evaluation_qualite = ?,date_observation = ?, commentaire_suggestion = ?
            WHERE id = ?
        ";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('sssi', $evaluationQualite, $date_observation, $commentaireSuggestion, $idObservation);
    } else {
        // Insertion d'une nouvelle observation
        $sql = "
            INSERT INTO observation (id_panne, id_chef_residence, id_intervention, evaluation_qualite, date_observation, commentaire_suggestion)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $connexion->prepare($sql);
        $stmt->bind_param('iiisss', $idPanne, $idUtilisateur, $idIntervention, $evaluationQualite, $date_observation, $commentaireSuggestion);
    }

    if ($stmt->execute()) {
        if ($evaluationQualite === 'Fait') {
            $sqlUpdateIntervention = "
                UPDATE intervention
                SET resultat = 'depanner'
                WHERE id = ?
            ";
        } elseif ($evaluationQualite === 'Inachevee') {
            $sqlUpdateIntervention = "
                UPDATE intervention
                SET resultat = 'en cours'
                WHERE id = ?
            ";
        } else {
            $sqlUpdateIntervention = null;
        }

        $stmtUpdate = $connexion->prepare($sqlUpdateIntervention);
        $stmtUpdate->bind_param('i', $idIntervention);
        $stmtUpdate->execute();
        return true;
    } else {
        return false;
    }
}
// ###############       FIN DE LA FONCTION enregistrerObservation    ####################

// Fonction pour obtenir les pannes enregistrées par l'utilisateur connecté
function allPannes1($connexion, $page = 1, $limit = 10, $profil2 = null) {
    $offset = ($page - 1) * $limit;

    // Initialiser la clause WHERE
    $whereClause = '';
    $params = [];
    $types = '';

    // Ajouter la condition pour filtrer par profil2 et type_panne
    if ($profil2 !== null) {
        $whereClause = " WHERE p.type_panne = ?";
        $params[] = $profil2;
        $types .= 's'; // 's' pour string
    }

    // Requête pour récupérer les pannes paginées
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat, i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.prenom, u.profil1, u.profil2,
               o.evaluation_qualite, o.id AS idObservation,o.date_observation, o.commentaire_suggestion
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        $whereClause
        ORDER BY 
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
        LIMIT ? OFFSET ?
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Ajouter les paramètres pour la requête préparée
    if ($profil2 !== null) {
        // Pour les requêtes qui utilisent des paramètres, assurez-vous de les ajouter à la liste des paramètres
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii'; // 'i' pour integer

        // Lier les paramètres
        $stmt->bind_param($types, ...$params);
    } else {
        // Pas de filtre, seulement les paramètres de LIMIT et OFFSET
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii'; // 'i' pour integer

        // Lier les paramètres
        $stmt->bind_param($types, ...$params);
    }
    
    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();
    $pannes = $result->fetch_all(MYSQLI_ASSOC);

    // Requête pour compter le nombre total de pannes
    $sqlCount = "
        SELECT COUNT(*) as total_count
        FROM Panne p
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        $whereClause
    ";
    
    // Préparer la requête de comptage
    $stmtCount = $connexion->prepare($sqlCount);
    
    // Ajouter les paramètres pour la requête préparée
    if ($profil2 !== null) {
        $stmtCount->bind_param('s', $profil2);
    }
    
    // Exécuter la requête
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $totalCount = $resultCount->fetch_assoc()['total_count'];

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
// ###############       FIN DE LA FONCTION      ####################


// ###############  DEBUT DE LA FONCTION  RECHERCHERPANNES()  #####################################
function rechercherPannes($connexion, $profil2 = null, $search = '', $isChefDst = false) {
    // Initialiser la clause WHERE
    $whereClauses = [];
    $params = [];
    $types = '';

    // Ajouter la condition pour filtrer par profil2 si fourni
    if ($profil2 !== null) {
        $whereClauses[] = "p.type_panne = ?";
        $params[] = $profil2;
        $types .= 's'; // 's' pour string
    }

    // Ajouter la condition pour la recherche par mots-clés seulement si un terme de recherche est spécifié
    if (!empty($search)) {
        $searchConditions = " (p.id LIKE ? OR p.type_panne LIKE ? OR p.date_enregistrement LIKE ? OR 
                               p.description LIKE ? OR p.localisation LIKE ? OR 
                               p.niveau_urgence LIKE ? OR 
                               i.id LIKE ? OR i.resultat LIKE ? OR i.date_intervention LIKE ? OR 
                               i.description_action LIKE ? OR i.personne_agent LIKE ? OR 
                               u.id LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ? OR u.profil1 LIKE ? OR 
                               u.profil2 LIKE ? OR 
                               o.id LIKE ? OR o.evaluation_qualite LIKE ? OR o.commentaire_suggestion LIKE ? OR
                               m.id_chef_dst LIKE ? OR m.date_imputation LIKE ?)";
        $whereClauses[] = $searchConditions;

        // Créez le tableau des paramètres pour correspondre au nombre de ? dans la requête
        $params = array_merge($params, array_fill(0, 21, "%$search%"));
        $types .= str_repeat('s', 21); // 's' pour string répété pour chaque paramètre de recherche
    }

    // Ajouter la jointure obligatoire avec la table Imputation pour les utilisateurs autres que chef DST
    $joinImputation = "";
    if (!$isChefDst) {
        $joinImputation = "INNER JOIN Imputation m ON p.id = m.id_panne";
    } else {
        $joinImputation = "LEFT JOIN Imputation m ON p.id = m.id_panne";
    }

    // Construire la clause WHERE finale
    $whereClause = '';
    if (!empty($whereClauses)) {
        $whereClause = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Requête pour récupérer les pannes filtrées par recherche avec les clauses ORDER BY
    $sql = "
        SELECT p.id, p.type_panne, p.date_enregistrement, p.description, p.localisation, p.niveau_urgence,
               i.resultat, i.id AS idIntervention, i.date_intervention, i.description_action, i.personne_agent,
               u.nom, u.prenom, u.profil1, u.profil2,
               o.evaluation_qualite, o.id AS idObservation, o.date_observation, o.commentaire_suggestion,
               m.id_chef_dst,m.resultat AS resultat_imp, m.instruction, m.date_imputation
        FROM Panne p
        LEFT JOIN Intervention i ON p.id = i.id_panne
        LEFT JOIN Utilisateur u ON p.id_chef_residence = u.id
        LEFT JOIN Observation o ON p.id = o.id_panne
        $joinImputation
        $whereClause
        ORDER BY 
            (CASE 
                WHEN m.id_chef_dst IS NULL THEN 1 
                ELSE 2 
            END) ASC,
            (CASE 
                WHEN i.resultat IS NULL THEN 1 
                WHEN i.resultat = 'en cours' THEN 2 
                ELSE 3 
            END) ASC, 
            (CASE 
                WHEN p.niveau_urgence = 'Èlevèe' THEN 1 
                WHEN p.niveau_urgence = 'Moyenne' THEN 2 
                WHEN p.niveau_urgence = 'Faible' THEN 3 
                ELSE 4 
            END) ASC, 
            p.date_enregistrement DESC
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Lier les paramètres si nécessaires
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
// ##################    FIN DE LA FONCTION      ####################

//######################### DEBUT la fonction pour AllPAnnes() ####################################
function allPannes($connexion, $page = 1, $limit = 10, $profil2 = null, $search = '', $isChefDst = false) {
    $offset = ($page - 1) * $limit;

    // Appeler la fonction de recherche
    $pannesFiltrees = rechercherPannes($connexion, $profil2, $search, $isChefDst);

    // Appliquer la pagination
    $totalCount = count($pannesFiltrees);
    $pannes = array_slice($pannesFiltrees, $offset, $limit);

    // Calcul du nombre total de pages
    $totalPages = ceil($totalCount / $limit);

    return ['pannes' => $pannes, 'total_count' => $totalCount, 'total_pages' => $totalPages, 'current_page' => $page];
}
// ###############       FIN DE LA FONCTION      ####################

//######################### DEBUT la fonction pour enregistrer des interventions ####################################
function enregistrerIntervention($connexion, $date_intervention, $description_action, $personne_agent, $date_sys, $resultat, $id_chef_atelier, $id_panne, $intervention_id = null) {
    if ($intervention_id) {
        // Requête de mise à jour pour modifier uniquement les champs spécifiés
        $sql = "
            UPDATE Intervention
            SET date_intervention = ?, description_action = ?, personne_agent = ?
            WHERE id = ?
        ";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Lier les paramètres
        $stmt->bind_param('sssi', $date_intervention, $description_action, $personne_agent, $intervention_id);
    } else {
        // Requête d'insertion pour ajouter une nouvelle intervention
        $sql = "
            INSERT INTO Intervention (date_intervention, date_sys, description_action, resultat, personne_agent, id_chef_atelier, id_panne)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Lier les paramètres
        $stmt->bind_param('sssssii', $date_intervention, $date_sys, $description_action, $resultat, $personne_agent, $id_chef_atelier, $id_panne);
    }

    // Exécuter la requête
    if ($stmt->execute()) {
        // Retourner l'ID de l'intervention insérée ou mise à jour
        return $intervention_id ? $intervention_id : $stmt->insert_id;
    } else {
        // En cas d'erreur, retourner false
        return false;
    }
}
//######################### FIN la fonction pour enregistrer des interventions ####################################

//************************************************************************************************************** */

//######################### DEBUT la fonction pour enregistrer des Imputation ####################################
function enregistrerImputation($connexion, $idPanne, $idChefDst, $instruction, $resultat, $dateImputation, $imputationId = null) {
    if ($imputationId != null) {
        // Préparer la requête de mise à jour pour le champ instruction uniquement
        $sql = "UPDATE Imputation SET instruction = ? WHERE id = ?";

        // Préparer la requête
        $stmt = $connexion->prepare($sql);

        // Vérifier si la préparation de la requête a échoué
        if ($stmt === false) {
            throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
        }

        // Lier les paramètres
        $stmt->bind_param('si', $instruction, $imputationId);

        // Exécuter la requête
        if ($stmt->execute() === false) {
            throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
        }

        // Fermer la requête
        $stmt->close();
        return true;
    } 

    // Préparer la requête d'insertion
    $sql = "INSERT INTO Imputation (id_panne, id_chef_dst, instruction, resultat, date_imputation) VALUES (?, ?, ?, ?, ?)";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres
    $stmt->bind_param('iisss', $idPanne, $idChefDst, $instruction, $resultat, $dateImputation);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();

    return true; // Retourner vrai si l'insertion a réussi
}
//######################### FIN la fonction pour enregistrer des Imputation ####################################

// ####################### supprimer imputation ######################################################
function supprimerImputation($connexion, $idImputation) {
    // Préparer la requête de suppression
    $sql = "DELETE FROM Imputation WHERE id_imputation = ?";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Failed to prepare statement: ' . $connexion->error);
    }

    // Lier le paramètre
    $stmt->bind_param('i', $idImputation);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();

    return true; // Retourner vrai si la suppression a réussi
}
// ####################### Fin supprimer imputation ######################################################

// Fonction pour obtenir tous les utilisateurs avec pagination
function allUtilisateurs($connexion) {
    // Requête pour récupérer tous les utilisateurs
    $sql = "
        SELECT id, username,email, telephone, nom, prenom, profil1, profil2
        FROM Utilisateur
    ";

    // Préparer la requête
    $stmt = $connexion->prepare($sql);

    // Vérifier si la préparation de la requête a échoué
    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Exécuter la requête
    $stmt->execute();
    $result = $stmt->get_result();

    // Récupérer tous les utilisateurs dans un tableau associatif
    $utilisateurs = $result->fetch_all(MYSQLI_ASSOC);

    // Fermer la requête
    $stmt->close();

    // Retourner la liste des utilisateurs
    return $utilisateurs;
}


function enregistrerUtilisateur($connexion, $username, $nom, $prenom, $email, $telephone, $motDePasse, $profil1, $profil2)
 {
    // Vérifier si l'utilisateur existe déjà par email
    $sqlCheck = "SELECT COUNT(*) AS count FROM Utilisateur WHERE email = ?";
    $stmtCheck = $connexion->prepare($sqlCheck);

    if ($stmtCheck === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier le paramètre email
    $stmtCheck->bind_param('s', $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    // Si l'utilisateur existe déjà, retourner une erreur
    if ($rowCheck['count'] > 0) {
        throw new Exception("L'utilisateur avec cet email existe déjà.");
    }

    // Fermer la requête de vérification
    $stmtCheck->close();

    // Hacher le mot de passe avec SHA-1
    $motDePasseHashe = sha1($motDePasse);

    // Requête d'insertion pour créer un nouvel utilisateur
    $sql = "INSERT INTO Utilisateur (username, nom, prenom, email, telephone, password, profil1, profil2) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connexion->prepare($sql);

    if ($stmt === false) {
        throw new Exception('Échec de la préparation de la requête : ' . $connexion->error);
    }

    // Lier les paramètres
    $stmt->bind_param('ssssssss',$username, $nom, $prenom, $email, $telephone, $motDePasseHashe, $profil1, $profil2);

    // Exécuter la requête
    if ($stmt->execute() === false) {
        throw new Exception('Échec de l\'exécution de la requête : ' . $stmt->error);
    }

    // Fermer la requête
    $stmt->close();
    
    return true;
}

