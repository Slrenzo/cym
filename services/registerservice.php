<?php

namespace services;

use PDOException;

class RegisterService
{

    /**
     * Création d'un compte, insertion des données de l'utilisateur
     * @param $pdo  la connexion à la base de données
     * @param $username  le nom de l'utilisateur
     * @param $email  l'email de l'utilisateur
     * @param $birthDate  la date de naissance de l'utilisateur au format préféfini par l'input correspondant
     * @param $gender  le genre de l'utilisateur
     * @param $password  le mot de passe de l'utilisateur
     * @return chaîne vide si la création du compte a pu être réalisé avec succès
     *                le message d'erreur correspondant à l'erreur renvoyé 
     *                par mySQL si la création n'a pas pu être faite.
     */
    public static function insertUserValues($pdo, $username, $email, $birthDate, $gender, $password, $confirmPassword) {
        try {
            $insert = $pdo->prepare('INSERT INTO user (User_Name,User_Email,User_BirthDate,User_Gender,User_Password) 
                                    VALUES (:username,:email,:birthDate,:gender,:pswd)');
            if ($password != $confirmPassword) {
                return "Les deux mots de passe ne sont pas identique";
            }
            /* cryptage du mot de passe en md5*/
            $password = md5($password);
            $insert->execute(array('username'=>$username,'email'=>$email,'birthDate'=>$birthDate,'gender'=>$gender,'pswd'=>$password));
            return "";
        } catch (PDOException $e) {
            $errorMessage = "création du compte impossible (le nom d'utilisateur est indisponible ou l'email est déjà utilisé) ou la base de données est inaccessible";
            return $errorMessage;
        }
    }

    /**
     * Récupère l'ID de l'utilisateur si le nom d'utilisateur et le mot de passe sont correct
     * @param $username nom d'utilisateur
     * @param $password mot de passe
     * @return l'id de l'utilisateur si le nom d'utilisateur et le mot de passe sont correct,
     *              Un message d'erreur dans le cas contraire
     */
    public static function getLoginIn($pdo, $username, $password) {
        $sql = "SELECT `User_ID` FROM `user` WHERE User_Name = :name AND User_Password = :pass";
        $searchStmt = $pdo->prepare($sql);
        $password = md5($password);
        $searchStmt->execute(['name'=>$username, 'pass'=>$password]);
        $id = null;
    
        // Vérification du nombre de tentatives de connexion infructueuses
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3) {
            $now = time();
            $lastAttempt = $_SESSION['last_attempt'];
            $diff = $now - $lastAttempt;
    
            // Si la dernière tentative de connexion infructueuse a eu lieu il y a moins de 30 secondes,
            // on renvoie un message d'erreur indiquant le temps restant avant la prochaine tentative
            if ($diff < 30) {
                $remainingTime = 30 - $diff;
                return "Trop de tentatives de connexion infructueuses. Veuillez réessayer dans ".$remainingTime." secondes.";
            }
            // Sinon, on réinitialise le nombre de tentatives de connexion infructueuses
            $_SESSION['login_attempts'] = 0;
        }
    
        while ($row = $searchStmt->fetch()) {
            $id = $row["User_ID"];
        }
        if ($id == null) {
            // Incrémentation du nombre de tentatives de connexion infructueuses
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 1;
            } else {
                $_SESSION['login_attempts']++;
            }
            // Stockage de l'heure de la dernière tentative de connexion infructueuse
            $_SESSION['last_attempt'] = time();
            return "Login invalide, identifiant ou mot de passe incorrect !";
        }
        // Réinitialisation du nombre de tentatives de connexion infructueuses en cas de connexion réussie
        $_SESSION['login_attempts'] = 0;
        return $id;
    }
    
}