<?php
//methode envoyant les JSON
require_once("json.php");
//methodes qui communique avec la base de données
require_once("donnees.php");
//methodes permettant de s'authentifié
require_once("authentification.php");

$request_method = $_SERVER["REQUEST_METHOD"];  // GET / POST
switch ($request_method) {
    case 'GET' :
        if (!empty($_GET['demande'])) {
            //decomposition de l'url
            $url = explode("/", (String) $_GET['demande'],FILTER_SANITIZE_URL);
            switch ($url[0]) {
                case "login" :
                    $login = isset($url[1]) ? $url[1] : "";
                    $pwd = isset($url[2]) ? $url[2] : "";
                    verifLoginPassword($login,$pwd); // retourne une api key
                    break;
                case "getUserByNameAndPwd" :
                    authentification();
                    if (isset($url[1]) && isset($url[2])) {
                        getUserByNameAndPwd($url[1], $url[2]);
                    } else {
                        $infos["statut"] = "KO";
                        $infos["message"] = "l'email et le pwd doivent être paramétrés";
                        sendJSON($infos, 400);
                    }
                case "getMoodsByIdAccount" :
                    authentification();
                    if (isset($url[1])) {
                        if (isset($url[2])) {
                            if (!is_numeric($url[1]) || !is_numeric($url[2])) {
                                $infos["statut"] = "KO";
                                $infos["message"] = "L'id d'un compte et le nombre d'humeurs souhaité doivent être numérique";
                                sendJSON($infos, 400);
                            }
                            getLastMoods(intval($url[1]), intval($url[2]));
                        } else {
                            if (!is_numeric($url[1])) {
                                $infos["statut"] = "KO";
                                $infos["message"] = "L'id d'un compte doit être numérique";
                                sendJSON($infos, 400);
                            }
                            getAllMoodsByIdAccount(intval($url[1]));
                        }
                    } else {
                        $infos["statut"] = "KO";
                        $infos["message"] = "Veuillez indiqué l'id d'un utilisateur";
                        sendJSON($infos, 400);
                    }
                case "getAllMoods" :
                    authentification();
                    getAllMoods();
                default:
                    $infos['statut'] = "KO";
                    $infos['message'] = $url[0]." inexistant";
                    sendJSON($infos, 404) ;
            }
        }
        break;
    case 'POST' :
        if (!empty($_GET['demande'])) {
            //decomposition de l'url
            $url = explode("/", (String) filter_var($_GET['demande'], FILTER_SANITIZE_URL));
            switch ($url[0]) {
                case "addMood" :
                    $donnees = json_decode((String) file_get_contents("php://input"),true);
                    addMood((array) $donnees);
                    break;
                default :
                    $infos['statut'] = "KO";
                    $infos['message'] = $url[0]." inexistant";
                    sendJSON($infos, 404) ;
            }
        }
        break;
    default :
        $infos['statut']="KO";
        $infos['message']="URL non valide";
        sendJSON($infos, 404) ;
}
