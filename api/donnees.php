<?php

/**
 * @return PDO une connexion à la bd avec PDO
 *                  ou un JSON signalant une erreur
 */
function getPDO(){
    $host='localhost';	// Serveur de BD
    $db='CYM';		// Nom de la BD
    $user='root';		// User
    $pass='root';		// Mot de passe
    $charset='utf8mb4';	// charset utilisé

    // Constitution variable DSN
    $dsn="mysql:host=$host;dbname=$db;charset=$charset";

    // Réglage des options
    $options=[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES=>false];

    try{	// Bloc try bd injoignable ou si erreur SQL
        $pdo=new PDO($dsn,$user,$pass,$options);
        return $pdo ;
    } catch(PDOException $e){
        //Il y a eu une erreur de connexion
        $infos['statut']="KO";
        $infos['message']="Problème connexion base de données";
        sendJSON($infos, 500) ;
        die();
    }
}

/**
 * Retourne un utilisateur en fonction de son email et de son mot de passe
 * @param String $email email à vérifier
 * @param String $pwd mot de passe à vérifier
 * @return void
 */
function getUserByNameAndPwd($name, $pwd) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT User_ID as id, User_Name as name, User_Email as email, 
                                  User_Birthdate as birthdate, User_Gender as gender, User_Password as pwdCrypte
                                  FROM user
                                  WHERE User_Name LIKE :name
                                  AND User_Password LIKE  :pwd");
        $stmt->execute([
            "name" => $name,
            "pwd" => md5($pwd)
        ]);
        if ($stmt->rowCount() == 0) {
            $infos["statut"] = "KO";
            $infos["message"] = "Aucun utilisateur existant avec ces logins";
            sendJSON($infos, 404);
        } else {
            sendJSON($stmt->fetch(), 200);
        }
    } catch (PDOException $exception) {
        $infos["statut"] = "KO";
        $infos["message"] = "Erreur coté serveur avec la bd";
        sendJSON($infos, 500);
    }
}

/**
 * Retourne toutes les humeurs d'un compte
 * @param int $idAccount id du compte dont l'on souhaite les humeurs
 * @return void
 */
function getAllMoodsByIdAccount($idAccount) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT Humeur_Libelle as libelle, Humeur_Emoji as emoji,
                              Humeur_Time as time, Humeur_Description as description, Humeur_TimeConst as timeConst
                              FROM humeur
                              WHERE CODE_User = :idAccount
                              ORDER BY Humeur_Time DESC");
        $stmt->execute([
            "idAccount" => $idAccount
        ]);
        sendJSON($stmt->fetchAll(), 200);
    } catch (PDOException $exception) {
        $infos["statut"] = "KO";
        $infos["message"] = "Erreur coté serveur avec la bd";
        sendJSON($infos, 500);
    }
}

/**
 * Envoie les dernières humeurs d'un utilisateur
 * @param int $id id de l'utilisateur dont on veut les humeurs
 * @param int $nbr nombre d'humeur que l'on souhaite récupérer
 * @return void
 */
function getLastMoods($id,$nbr) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT Humeur_Libelle as libelle, Humeur_Emoji as emoji 
                                , Humeur_Time as time, Humeur_Description as description
                                , Humeur_TimeConst as timeConst
                                FROM humeur
                                WHERE CODE_User = :id
                                ORDER BY Humeur_Time DESC
                                LIMIT :nbr");

        $stmt->execute([
            "id" => $id,
            "nbr" => $nbr
        ]);
        sendJSON($stmt->fetchAll(), 200);
    } catch (PDOException $exception) {
        $infos["statut"] = "KO";
        $infos["message"] = "Erreur coté serveur avec la bd";
        sendJSON($infos, 500);
    }
}

/**
 * Récupère toutes les humeurs qu'un utilisateur peut enregistrer
 * @return void
 */
function getAllMoods() {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT ID_Hum as id, Libelle , Emoji 
                          FROM def_humeur");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $infos["statut"] = "KO";
            $infos["message"] = "erreur aucune humeur dans la BD";
            sendJSON($infos, 404);
        } else {
            sendJSON($stmt->fetchAll(), 200);
        }
    } catch (PDOException $exception) {
        $infos["statut"] = "KO";
        $infos["message"] = "Erreur coté serveur avec la bd";
        sendJSON($infos, 500);
    }
}

/**
 * Permet d'ajouter une humeur à un utilisateur
 * @param array<mixed> $donnees données à insérer dans la bd
 * @return void
 */
function addMood($donnees) {
    if (!isset($donnees["idAccount"])
        || !isset($donnees["libelle"])
        || !isset($donnees["smiley"])
        || !isset($donnees["description"])) {
        $infos['statut']="KO";
        $infos['message']="Données incomplètes";
        sendJSON($infos, 400) ;
    }
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO `humeur`(`CODE_User`, `Humeur_Libelle`, `Humeur_Emoji`, `Humeur_Time`, `Humeur_Description`, `Humeur_TimeConst`) 
                              VALUES (:id,:libelle,:smiley,CURRENT_TIMESTAMP,:description,CURRENT_TIMESTAMP)");
        $stmt->execute([
            "id" => $donnees["idAccount"],
            "libelle" => $donnees["libelle"],
            "smiley" => $donnees["smiley"],
            "description" => $donnees["description"]
        ]);
        $infos['statut'] = "OK";
        $infos['message'] = "Insertion réussie";
        sendJSON($infos, 201) ;
    } catch (PDOException $exception) {
        $infos["statut"] = "KO";
        $infos["message"] = "Erreur coté serveur avec la bd";
        sendJSON($infos, 500);
    }
}