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
            $idAutorise = $unUtilisateur->getId();
            $idAutorisant = $uneTrace->getIdUtilisateur();
            if ($dao->autoriseAConsulter($idAutorisant, $idAutorise) == false)
            {	$msg = "Erreur : vous n'êtes pas autorisé par le propriétaire du parcours.";
            }
            else
            {  
                $msg = "Données de la trace demandée.";
            }
        }
    }
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg, $uneTrace);
}
else {
    creerFluxJSON($msg, $uneTrace);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

function creerFluxXML($msg, $uneTrace) {
    
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
    
    // traitement des utilisateurs
    if ($uneTrace != null) {
        // place l'élément 'donnees' dans l'élément 'data'
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);
        
        // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
        $elt_trace = $doc->createElement('trace');
        $elt_donnees->appendChild($elt_trace);
        
        $elt_idTrace = $doc->createElement('id', $uneTrace->getId());
        $elt_trace->appendChild($elt_idTrace);
        
        $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $uneTrace->getDateHeureDebut());
        $elt_trace->appendChild($elt_dateHeureDebut);
        
        $elt_terminee = $doc->createElement('terminee', $uneTrace->getTerminee());
        $elt_trace->appendChild($elt_terminee);
        
        $elt_dateHeureFin = $doc->createElement('dateHeureFin', $uneTrace->getDateHeureFin());
        $elt_trace->appendChild($elt_dateHeureFin);
        
        $elt_idUtilisateur = $doc->createElement('idUtilisateur', $uneTrace->getIdUtilisateur());
        $elt_trace->appendChild($elt_idUtilisateur);
        
        // crée un élément vide 'utilisateur'
        $elt_lesPoints = $doc->createElement('lesPoints');
        // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
        $elt_donnees->appendChild($elt_lesPoints);
        
        $lesPointsDuneTrace = $uneTrace->getLesPointsDeTrace();
        foreach ($lesPointsDuneTrace as $unPointsDuneTrace)
        {
            // crée un élément vide 'utilisateur'
            $elt_point = $doc->createElement('point');
            // place l'élément 'utilisateur' dans l'élément 'lesUtilisateurs'
            $elt_lesPoints->appendChild($elt_point);
            
            // crée les éléments enfants de l'élément 'utilisateur'
            $elt_id         = $doc->createElement('id', $unPointsDuneTrace->getId());
            $elt_point->appendChild($elt_id);
            
            $elt_latitude     = $doc->createElement('latitude', $unPointsDuneTrace->getLatitude());
            $elt_point->appendChild($elt_latitude);
            
            $elt_longitude    = $doc->createElement('longitude', $unPointsDuneTrace->getLongitude());
            $elt_point->appendChild($elt_longitude);
            
            $elt_altitude     = $doc->createElement('altitude', $unPointsDuneTrace->getAltitude());
            $elt_point->appendChild($elt_altitude);
            
            $elt_dateHeure     = $doc->createElement('dateHeure', $unPointsDuneTrace->getDateHeure());
            $elt_point->appendChild($elt_dateHeure);
            
            $elt_rythmeCardio = $doc->createElement('rythmeCardio', $unPointsDuneTrace->getRythmeCardio());
            $elt_point->appendChild($elt_rythmeCardio);
        }
    }
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

function creerFluxJSON($msg, $uneTrace)
{
    if ($uneTrace == null) {
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    else {
        $trace = array();
        $trace["id"] = $uneTrace->getId();
        $trace["dateHeureDebut"] = $uneTrace->getDateHeureDebut();
        $trace["terminee"] = $uneTrace->getTerminee();
        $trace["dateHeureFin"] = $uneTrace->getDateHeureFin();
        $trace["idUtilisateur"] = $uneTrace->getIdUtilisateur();
        
        $lesPointsDuneTrace = $uneTrace->getLesPointsDeTrace();
        
        // construction d'un tableau contenant les utilisateurs
        $lesObjetsTraces = array();
        foreach ($lesPointsDuneTrace as $unPointsDuneTrace)
        {	// crée une ligne dans le tableau
            $unObjetTrace = array();
            $unObjetTrace["id"] = $unPointsDuneTrace->getId();
            $unObjetTrace["latitude"] = $unPointsDuneTrace->getLatitude();
            $unObjetTrace["longitude"] = $unPointsDuneTrace->getLongitude();
            $unObjetTrace["altitude"] = $unPointsDuneTrace->getAltitude();
            $unObjetTrace["dateHeure"] = $unPointsDuneTrace->getDateHeure();
            $unObjetTrace["rythmeCardio"] = $unPointsDuneTrace->getRythmeCardio();

            $lesObjetsTraces[] = $unObjetTrace;
        }
        $elt_points = ["lesPoints" => $lesObjetsTraces];
        
        // construction de l'élément "trace"
        $elt_trace = ["trace" => $trace];
        
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg, "donnees" => $elt_trace + $elt_points];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}