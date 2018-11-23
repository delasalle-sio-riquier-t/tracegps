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
if ( empty ($_REQUEST ["pseudoConsulte"]) == true)  $pseudoConsulte = "";  else   $pseudoConsulte = $_REQUEST ["pseudoConsulte"];
if ( empty ($_REQUEST ["lang"]) == true) $lang = "";  else $lang = strtolower($_REQUEST ["lang"]);
// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

$lesTraces = null;

// Contrôle de la présence des paramètres
if ( $pseudo == "" || $mdpSha1 == "" || $pseudoConsulte == "")
{	$msg = "Erreur : données incomplètes.";
}
else
{	// il faut être un utilisateur pour consulter un parcours
    if ( $dao->getNiveauConnexion($pseudo, $mdpSha1) == 0 )
    {   $msg = "Erreur : authentification incorrecte.";
    }
    else
    {
        $pseudoAConsulte = $dao->getUnUtilisateur($pseudoConsulte);
        if ($pseudoAConsulte == null)
        {	$msg = "Erreur : pseudo consulté inexistant.";
        }
        else
        {   $unUtilisateur = $dao->getUnUtilisateur($pseudo);
            $idAutorise = $unUtilisateur->getId();
            $idAutorisant = $pseudoAConsulte->getId();
            
            if ($dao->autoriseAConsulter($idAutorisant, $idAutorise) == false)
            {	$msg = "Erreur : vous n'êtes pas autorisé par cet utilisateur.";
            }
            else
            {
                $lesTraces = $dao->getLesTraces($idAutorisant);
                if (sizeof($lesTraces) == 0)
                {   $msg = "Aucune trace pour l'utilisateur oxygen.";
                }
                else
                {   $msg = sizeof($lesTraces) . " trace(s) pour l'utilisateur " . $pseudoConsulte . ".";
                }
            }
        }
    }
}
// ferme la connexion à MySQL
unset($dao);

// création du flux en sortie
if ($lang == "xml") {
    creerFluxXML($msg, $lesTraces);
}
else {
    creerFluxJSON($msg, $lesTraces);
}

// fin du programme (pour ne pas enchainer sur la fonction qui suit)
exit;

function creerFluxXML($msg, $lesTraces) {
    
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
    if ($lesTraces != null) {
        foreach ($lesTraces as $uneTrace)
        {
            // place l'élément 'donnees' dans l'élément 'data'
            $elt_donnees = $doc->createElement('donnees');
            $elt_data->appendChild($elt_donnees);
            
            // place l'élément 'lesUtilisateurs' dans l'élément 'donnees'
            $elt_lesTraces = $doc->createElement('lesTraces');
            $elt_donnees->appendChild($elt_lesTraces);
            
            $elt_trace = $doc->createElement('trace');
            $elt_lesTraces->appendChild($elt_trace);
            
            $elt_idTrace = $doc->createElement('id', $uneTrace->getId());
            $elt_trace->appendChild($elt_idTrace);
            
            $elt_dateHeureDebut = $doc->createElement('dateHeureDebut', $uneTrace->getDateHeureDebut());
            $elt_trace->appendChild($elt_dateHeureDebut);
            
            $elt_terminee = $doc->createElement('terminee', $uneTrace->getTerminee());
            $elt_trace->appendChild($elt_terminee);
            
            if ($uneTrace->getTerminee() == 1) {
                $elt_dateHeureFin = $doc->createElement('dateHeureFin', $uneTrace->getDateHeureFin());
                $elt_trace->appendChild($elt_dateHeureFin);
            }
            
            $elt_terminee = $doc->createElement('distance', number_format($uneTrace->getDistanceTotale(), 1));
            $elt_trace->appendChild($elt_terminee);
            
            $elt_idUtilisateur = $doc->createElement('idUtilisateur', $uneTrace->getIdUtilisateur());
            $elt_trace->appendChild($elt_idUtilisateur);
        }
    }
    
    // Mise en forme finale
    $doc->formatOutput = true;
    
    // renvoie le contenu XML
    echo $doc->saveXML();
    return;
}

function creerFluxJSON($msg, $lesTraces)
{
    if ($lesTraces == null) {
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg];
    }
    else {
        $lesObjetsTraces = array();
        foreach ($lesTraces as $uneTrace)
        {
            $unObjetTrace = array();
        
            $unObjetTrace["id"] = $uneTrace->getId();
            $unObjetTrace["dateHeureDebut"] = $uneTrace->getDateHeureDebut();
            $unObjetTrace["terminee"] = $uneTrace->getTerminee();
            if ($uneTrace->getTerminee() == 1)
                $unObjetTrace["dateHeureFin"] = $uneTrace->getDateHeureFin();
            $unObjetTrace["distance"] = number_format($uneTrace->getDistanceTotale(), 1);
            $unObjetTrace["idUtilisateur"] = $uneTrace->getIdUtilisateur();
            
            $lesObjetsTraces[] = $unObjetTrace;
        }
                       
        // construction de l'élément "trace"
        $elt_lesTraces = ["lesTraces" => $lesObjetsTraces];
        
        // construction de l'élément "data"
        $elt_data = ["reponse" => $msg, "donnees" => $elt_lesTraces];
    }
    
    // construction de la racine
    $elt_racine = ["data" => $elt_data];
    
    // retourne le contenu JSON (l'option JSON_PRETTY_PRINT gère les sauts de ligne et l'indentation)
    echo json_encode($elt_racine, JSON_PRETTY_PRINT);
    return;
}