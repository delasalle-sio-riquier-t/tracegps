<?php
// connexion du serveur web à la base MySQL
include_once ('../modele/DAO.class.php');
$dao = new DAO();

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
// la fonction $_POST récupère une donnée envoyées par la méthode POST
// la fonction $_REQUEST récupère par défaut le contenu des variables $_GET, $_POST, $_COOKIE
if ( empty ($_REQUEST ["pseudo"]) == true)  $pseudo = "";  else   $pseudo = $_REQUEST ["pseudo"];
if ( empty ($_REQUEST ["mdpSha1"]) == true)  $mdpSha1 = "";  else   $mdpSha1 = $_REQUEST ["mdpSha1"];
if ( empty ($_REQUEST ["idTrace"]) == true)  $idTrace = "";  else   $idTrace = $_REQUEST ["idTrace"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

$uneTrace = null;

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $idTrace == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// il faut être un utilisateur pour consulter un parcours
    if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte.";
    }
    else
    {
        $uneTrace = $dao->getUneTrace($idTrace);
        if ($uneTrace == null)
        {	$msg = "Erreur : parcours inexistant.";
        }
        else
        {   $unUtilisateur = $dao->getUnUtilisateur($pseudo);
            $idUtilisateur = $unUtilisateur->getId();
            $idUserTrace = $uneTrace->getId();
            
            if ($idUserTrace != $idUtilisateur)
            {	$msg = "Erreur : vous n'êtes pas le propriétaire de ce parcours.";
            }
            else
            {
                $ok = $dao->supprimerUneTrace($uneTrace);
                if ($ok == false)
                {    $msg = "Erreur : problème lors de la suppression du parcours.";
                }
                else
                {   $msg = "Parcours supprimé.";
                }
            }
        }
    }
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg);
}
else {
    creerFluxJSON($msg);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

function creerFluxXML($msg) {
    
    // crée une instance de DOMdocument (DOM : Document Object Model)
    $doc = new DOMDocument();
    
    // specifie la version et le type d'encodage
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    
    // crée un commentaire et l'encode en UTF-8
    $elt_commentaire = $doc->createComment('Service web GetUnParcoursEtSesPoints - BTS SIO - Lycée De La Salle - Rennes');
    // place ce commentaire à la racine du document XML
    $doc->appendChild($elt_commentaire);
    
    // crée l'élément 'data' à la racine du document XML
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    
    // place l'élément 'reponse' dans l'élément 'data'
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);       
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

function creerFluxJSON($msg)
{
    // construction de l'élément "data"
    $elt_data = ["reponse" => $msg];
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}