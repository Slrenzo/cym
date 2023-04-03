<?php
/**
 * Permet d'authentifié un utilisateur sur l'api
 * @return void
 */
function authentification() {
    if (isset($_SERVER["HTTP_APIKEY"])) {
        $apiKey=$_SERVER["HTTP_APIKEY"];
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT api_key 
                          FROM api_user
                          WHERE api_key LIKE :apiKey");
            $stmt->execute([
                "apiKey" => $apiKey
            ]);
            if ($stmt->rowCount() == 0) {
                $infos['statut']="KO";
                $infos['message']="APIKEY invalide.";
                sendJSON($infos, 403) ;
                die();
            }
        } catch (PDOException $e) {
            $infos['statut']="KO";
            $infos['message']="Erreur BD : ".$e->getMessage();
            sendJSON($infos, 500) ;
        }
    }else {
        // Pas de clé API envoyée, pas d'accès à l'Api
        $infos['statut']="KO";
        $infos['message']="Authentification necessaire par APIKEY.";
        sendJSON($infos, 401) ;
        die();
    }
}

/**
 * Verifie qu'un utilisateur existe avec un login et un mot de passe
 * @param String $login login a verifier
 * @param String $pwd mot de passe a verifier
 * @return void
 */
function verifLoginPassword($login, $pwd) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT api_key 
                              FROM api_user 
                              WHERE login LIKE :login
                              AND pwd LIKE :pwd");
        $pwd = md5($pwd);
        $stmt->execute([
            "login" => $login,
            "pwd" => $pwd
        ]);
        $apiKey = (array) $stmt->fetch();
        if ($stmt->rowCount() != 0) {
            //login correct (envoie de l'api key)
            $infos["statut"] = "OK";
            $infos["apiKeyCym"] = $apiKey["api_key"];
            sendJSON($infos, 200);
        } else {
            // Login incorrect
            $infos['statut']="KO";
            $infos['message']="Logins incorrects.";
            sendJSON($infos, 401) ;
            die();
        }
    } catch (PDOException $e) {
        $infos['statut']="KO";
        $infos['message']="Erreur BD : ".$e->getMessage();
        sendJSON($infos, 500) ;
    }
}
