<?php
// Projet TraceGPS
// fichier : modele/DAO.class.php   (DAO : Data Access Object)
// R�le : fournit des m�thodes d'acc�s � la bdd tracegps (projet TraceGPS) au moyen de l'objet PDO
// modifi� par Jim le 12/8/2018

// liste des m�thodes d�j� d�velopp�es (dans l'ordre d'apparition dans le fichier) :

// __construct() : le constructeur cr�e la connexion $cnx � la base de donn�es
// __destruct() : le destructeur ferme la connexion $cnx � la base de donn�es
// getNiveauConnexion($login, $mdp) : fournit le niveau (0, 1 ou 2) d'un utilisateur identifi� par $login et $mdp
// function existePseudoUtilisateur($pseudo) : fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
// getUnUtilisateur($login) : fournit un objet Utilisateur � partir de $login (son pseudo ou son adresse mail)
// getTousLesUtilisateurs() : fournit la collection de tous les utilisateurs (de niveau 1)
// creerUnUtilisateur($unUtilisateur) : enregistre l'utilisateur $unUtilisateur dans la bdd
// modifierMdpUtilisateur($login, $nouveauMdp) : enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $login dapr�s l'avoir hash� en SHA1
// supprimerUnUtilisateur($login) : supprime l'utilisateur $login (son pseudo ou son adresse mail) dans la bdd, ainsi que ses traces et ses autorisations
// envoyerMdp($login, $nouveauMdp) : envoie un mail � l'utilisateur $login avec son nouveau mot de passe $nouveauMdp

// liste des m�thodes restant � d�velopper :

// existeAdrMailUtilisateur($adrmail) : fournit true si l'adresse mail $adrMail existe dans la table tracegps_utilisateurs, false sinon
// getLesUtilisateursAutorises($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autoris�s � suivre l'utilisateur $idUtilisateur
// getLesUtilisateursAutorisant($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisant l'utilisateur $idUtilisateur � voir leurs parcours
// autoriseAConsulter($idAutorisant, $idAutorise) : v�rifie que l'utilisateur $idAutorisant) autorise l'utilisateur $idAutorise � consulter ses traces
// creerUneAutorisation($idAutorisant, $idAutorise) : enregistre l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// supprimerUneAutorisation($idAutorisant, $idAutorise) : supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// getLesPointsDeTrace($idTrace) : fournit la collection des points de la trace $idTrace
// getUneTrace($idTrace) : fournit un objet Trace � partir de identifiant $idTrace
// getToutesLesTraces() : fournit la collection de toutes les traces
// getMesTraces($idUtilisateur) : fournit la collection des traces de l'utilisateur $idUtilisateur
// getLesTracesAutorisees($idUtilisateur) : fournit la collection des traces que l'utilisateur $idUtilisateur a le droit de consulter
// creerUneTrace(Trace $uneTrace) : enregistre la trace $uneTrace dans la bdd
// terminerUneTrace($idTrace) : enregistre la fin de la trace d'identifiant $idTrace dans la bdd ainsi que la date de fin
// supprimerUneTrace($idTrace) : supprime la trace d'identifiant $idTrace dans la bdd, ainsi que tous ses points
// creerUnPointDeTrace(PointDeTrace $unPointDeTrace) : enregistre le point $unPointDeTrace dans la bdd


// certaines m�thodes n�cessitent les classes suivantes :
include_once ('Utilisateur.class.php');
include_once ('Trace.class.php');
include_once ('PointDeTrace.class.php');
include_once ('Point.class.php');
include_once ('Outils.class.php');

// inclusion des param�tres de l'application
include_once ('parametres.php');

// d�but de la classe DAO (Data Access Object)
class DAO
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres priv�s de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $cnx;				// la connexion � la base de donn�es
    
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Constructeur et destructeur ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct() {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
        try
        {	$this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
            $PARAM_USER,
            $PARAM_PWD);
        return true;
        }
        catch (Exception $ex)
        {	echo ("Echec de la connexion a la base de donnees <br>");
        echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
        echo ("PARAM_HOTE = " . $PARAM_HOTE);
        return false;
        }
    }
    
    public function __destruct() {
        // ferme la connexion � MySQL :
        unset($this->cnx);
    }
    
    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- M�thodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    // fournit le niveau (0, 1 ou 2) d'un utilisateur identifi� par $pseudo et $mdpSha1
    // cette fonction renvoie un entier :
    //     0 : authentification incorrecte
    //     1 : authentification correcte d'un utilisateur (pratiquant ou personne autoris�e)
    //     2 : authentification correcte d'un administrateur
    // modifi� par Jim le 11/1/2018
    public function getNiveauConnexion($pseudo, $mdpSha1) {
        // pr�paration de la requ�te de recherche
        $txt_req = "Select niveau from tracegps_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $txt_req .= " and mdpSha1 = :mdpSha1";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requ�te et de ses param�tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        $req->bindValue("mdpSha1", $mdpSha1, PDO::PARAM_STR);
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // traitement de la r�ponse
        $reponse = 0;
        if ($uneLigne) {
        	$reponse = $uneLigne->niveau;
        }
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        // fourniture de la r�ponse
        return $reponse;
    }
    
    
    // fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
    // modifi� par Jim le 27/12/2017
    public function existePseudoUtilisateur($pseudo) {
        // pr�paration de la requ�te de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requ�te et de ses param�tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // ex�cution de la requ�te
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        
        // fourniture de la r�ponse
        if ($nbReponses == 0) {
            return false;
        }
        else {
            return true;
        }
    }
    
    
    // fournit un objet Utilisateur � partir de son pseudo $pseudo
    // fournit la valeur null si le pseudo n'existe pas
    // modifi� par Jim le 9/1/2018
    public function getUnUtilisateur($pseudo) {
        // pr�paration de la requ�te de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requ�te et de ses param�tres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        
        // traitement de la r�ponse
        if ( ! $uneLigne) {
            return null;
        }
        else {
            // cr�ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }
    
    
    // fournit la collection  de tous les utilisateurs (de niveau 1)
    // le r�sultat est fourni sous forme d'une collection d'objets Utilisateur
    // modifi� par Jim le 27/12/2017
    public function getTousLesUtilisateurs() {
        // pr�paration de la requ�te de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where niveau = 1";
        $txt_req .= " order by pseudo";
        
        $req = $this->cnx->prepare($txt_req);
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouv�e :
        while ($uneLigne) {
            // cr�ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur � la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }

    
    // enregistre l'utilisateur $unUtilisateur dans la bdd
    // fournit true si l'enregistrement s'est bien effectu�, false sinon
    // met � jour l'objet $unUtilisateur avec l'id (auto_increment) attribu� par le SGBD
    // modifi� par Jim le 9/1/2018
    public function creerUnUtilisateur($unUtilisateur) {
        // on teste si l'utilisateur existe d�j�
        if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;
        
        // pr�paration de la requ�te
        $txt_req1 = "insert into tracegps_utilisateurs (pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation)";
        $txt_req1 .= " values (:pseudo, :mdpSha1, :adrMail, :numTel, :niveau, :dateCreation)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requ�te et de ses param�tres
        $req1->bindValue("pseudo", utf8_decode($unUtilisateur->getPseudo()), PDO::PARAM_STR);
        $req1->bindValue("mdpSha1", utf8_decode(sha1($unUtilisateur->getMdpsha1())), PDO::PARAM_STR);
        $req1->bindValue("adrMail", utf8_decode($unUtilisateur->getAdrmail()), PDO::PARAM_STR);
        $req1->bindValue("numTel", utf8_decode($unUtilisateur->getNumTel()), PDO::PARAM_STR);
        $req1->bindValue("niveau", utf8_decode($unUtilisateur->getNiveau()), PDO::PARAM_INT);
        $req1->bindValue("dateCreation", utf8_decode($unUtilisateur->getDateCreation()), PDO::PARAM_STR);
        // ex�cution de la requ�te
        $ok = $req1->execute();
        // sortir en cas d'�chec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a �t� attribu� � la trace
        $txt_req2 = "Select max(id) as idMax from tracegps_utilisateurs";
        $req2 = $this->cnx->prepare($txt_req2);
        // extraction des donn�es
        $req2->execute();
        $uneLigne = $req2->fetch(PDO::FETCH_OBJ);
        $unId = $uneLigne->idMax;
        $unUtilisateur->setId($unId);
        return true;
    }
    
    
    // enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $pseudo dapr�s l'avoir hash� en SHA1
    // fournit true si la modification s'est bien effectu�e, false sinon
    // modifi� par Jim le 9/1/2018
    public function modifierMdpUtilisateur($pseudo, $nouveauMdp) {
        // pr�paration de la requ�te
        $txt_req = "update tracegps_utilisateurs set mdpSha1 = :nouveauMdp";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requ�te et de ses param�tres
        $req->bindValue("nouveauMdp", sha1($nouveauMdp), PDO::PARAM_STR);
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // ex�cution de la requ�te
        $ok = $req->execute();
        return $ok;
    }
    
    
    // supprime l'utilisateur $pseudo dans la bdd, ainsi que ses traces et ses autorisations
    // fournit true si l'effacement s'est bien effectu�, false sinon
    // modifi� par Jim le 9/1/2018
    public function supprimerUnUtilisateur($pseudo) {
        $unUtilisateur = $this->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            return false;
        }
        else {
            $idUtilisateur = $unUtilisateur->getId();
            
            // suppression des traces de l'utilisateur (et des points correspondants)
            $lesTraces = $this->getLesTraces($idUtilisateur);
            foreach ($lesTraces as $uneTrace) {
                $this->supprimerUneTrace($uneTrace->getId());
            }
            
            // pr�paration de la requ�te de suppression des autorisations
            $txt_req1 = "delete from tracegps_autorisations" ;
            $txt_req1 .= " where idAutorisant = :idUtilisateur or idAutorise = :idUtilisateur";
            $req1 = $this->cnx->prepare($txt_req1);
            // liaison de la requ�te et de ses param�tres
            $req1->bindValue("idUtilisateur", utf8_decode($idUtilisateur), PDO::PARAM_INT);
            // ex�cution de la requ�te
            $ok = $req1->execute();
            
            // pr�paration de la requ�te de suppression de l'utilisateur
            $txt_req2 = "delete from tracegps_utilisateurs" ;
            $txt_req2 .= " where pseudo = :pseudo";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requ�te et de ses param�tres
            $req2->bindValue("pseudo", utf8_decode($pseudo), PDO::PARAM_STR);
            // ex�cution de la requ�te
            $ok = $req2->execute();
            return $ok;
        }
    }
    
    
    // envoie un mail � l'utilisateur $pseudo avec son nouveau mot de passe $nouveauMdp
    // retourne true si envoi correct, false en cas de probl�me d'envoi
    // modifi� par Jim le 9/1/2018
    public function envoyerMdp($pseudo, $nouveauMdp) {
        global $ADR_MAIL_EMETTEUR;
        // si le pseudo n'est pas dans la table tracegps_utilisateurs :
        if ( $this->existePseudoUtilisateur($pseudo) == false ) return false;
        
        // recherche de l'adresse mail
        $adrMail = $this->getUnUtilisateur($pseudo)->getAdrMail();
        
        // envoie un mail � l'utilisateur avec son nouveau mot de passe
        $sujet = "Modification de votre mot de passe d'acc�s au service TraceGPS";
        $message = "Cher(ch�re) " . $pseudo . "\n\n";
        $message .= "Votre mot de passe d'acc�s au service service TraceGPS a �t� modifi�.\n\n";
        $message .= "Votre nouveau mot de passe est : " . $nouveauMdp ;
        $ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
        return $ok;
    }
    
    
    // Le code restant � d�velopper va �tre r�parti entre les membres de l'�quipe de d�veloppement.
    // Afin de limiter les conflits avec GitHub, il est d�cid� d'attribuer une zone de ce fichier � chaque d�veloppeur.
    // D�veloppeur 1 : lignes 350 � 549
    // D�veloppeur 2 : lignes 550 � 749
    // D�veloppeur 3 : lignes 750 � 950
    
    // Quelques conseils pour le travail collaboratif :
    // avant d'attaquer un cycle de d�veloppement (d�but de s�ance, nouvelle m�thode, ...), faites un Pull pour r�cup�rer 
    // la derni�re version du fichier.
    // Apr�s avoir test� et valid� une m�thode, faites un commit et un push pour transmettre cette version aux autres d�veloppeurs.
    
    
    
    
    
    // --------------------------------------------------------------------------------------
    // d�but de la zone attribu�e au d�veloppeur 1 (Ogu) : lignes 350 � 549
    // --------------------------------------------------------------------------------------
    
    public function existeAdrMailUtilisateur($adrMail)
    {
        $txt_req1 = "select * from tracegps_utilisateurs where adrMail = :adrMail" ;
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requ�te et de ses param�tres
        $req1->bindValue("adrMail", utf8_decode($adrMail), PDO::PARAM_STR);
        // ex�cution de la requ�te
        $req1->execute();
        
        $nbReponses = $req1->fetchColumn(0);
        // lib�re les ressources du jeu de donn�es
        $req1->closeCursor();
        
        if($nbReponses == 0)
        {   return false;}
        else 
        {   return true;}
        
    }
    
    public function autoriseAConsulter($idAutorisant, $idAutorise)
    {
        $txt_req1 = "select * from tracegps_autorisations where idAutorisant = :idAutorisant AND idAutorise = :idAutorise";
        
        $req1 = $this->cnx->prepare($txt_req1);
        $req1->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req1->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);
        
        $req1->execute();
        
        $nbReponses = $req1->fetchColumn(0);
        // lib�re les ressources du jeu de donn�es
        $req1->closeCursor();
        
        if($nbReponses == 0)
        {   return false;}
        else
        {   return true;}
    }
    
    public function creerUneAutorisation($idAutorisant, $idAutorise)
    {
        $txt_req1 = "INSERT INTO tracegps_autorisations VALUES(:idAutorisant, :idAutorise)";
        $req1 = $this->cnx->prepare($txt_req1);
        $req1->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req1->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);
        
        $ok = $req1->execute();
        
        if ($ok) return true;
        else return false;
        
    }
 
    public function getToutesLesTraces()
    {
        $txt_req = "Select * from tracegps_vue_traces";
        
        
        $req = $this->cnx->prepare($txt_req);
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesTraces = array();
        // tant qu'une ligne est trouv�e :
        while ($uneLigne) {
            // cr�ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $uneDateHeureDebut = utf8_encode($uneLigne->dateDebut);
            $uneDateHeureFin = utf8_encode($uneLigne->dateFin);
            $terminee = utf8_encode($uneLigne->terminee);
            $unIdUtilisateur = utf8_encode($uneLigne->idUtilisateur);
            
            $uneTrace = new Trace($unId, $uneDateHeureDebut, $uneDateHeureFin,$terminee, $unIdUtilisateur);
            $uneTrace->setLesPointsDeTrace($this->getLesPointsDeTrace($unId));
            
            // ajout de l'utilisateur � la collection
            $lesTraces[] = $uneTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        // fourniture de la collection
        return $lesTraces;
    }
    
    // --------------------------------------------------------------------------------------
    // d�but de la zone attribu�e au d�veloppeur 2 (Vincent) : lignes 550 � 749
    // --------------------------------------------------------------------------------------

    public function getLesUtilisateursAutorisant($idUtilisateur)
    {
        $txt_req = "select * from tracegps_autorisations, tracegps_vue_utilisateurs where id = idAutorisant and idAutorise = :id";
        
        $req = $this->cnx->prepare($txt_req);
        $req->bindValue("id", $idUtilisateur, PDO::PARAM_INT);
        
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouv�e :
        while ($uneLigne) {
            // cr�ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur � la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }
    
    public function getLesUtilisateursAutorises($idUtilisateur)
    {
        $txt_req = "select * from tracegps_autorisations, tracegps_vue_utilisateurs  where tracegps_autorisations.idAutorise = tracegps_vue_utilisateurs.id  and idAutorisant = 2";
        
        $req = $this->cnx->prepare($txt_req);
        $req->bindValue("id", $idUtilisateur, PDO::PARAM_INT);
        
        // extraction des donn�es
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouv�e :
        while ($uneLigne) {
            // cr�ation d'un objet Utilisateur
            $unId = utf8_encode($uneLigne->id);
            $unPseudo = utf8_encode($uneLigne->pseudo);
            $unMdpSha1 = utf8_encode($uneLigne->mdpSha1);
            $uneAdrMail = utf8_encode($uneLigne->adrMail);
            $unNumTel = utf8_encode($uneLigne->numTel);
            $unNiveau = utf8_encode($uneLigne->niveau);
            $uneDateCreation = utf8_encode($uneLigne->dateCreation);
            $unNbTraces = utf8_encode($uneLigne->nbTraces);
            $uneDateDerniereTrace = utf8_encode($uneLigne->dateDerniereTrace);
            
            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur � la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // lib�re les ressources du jeu de donn�es
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }

    public function supprimerUneAutorisation($idAutorisant, $idAutorise) 
    {
        $txt_req1 = "DELETE FROM tracegps_autorisations WHERE idAutorisant= :idAutorisant AND  idAutorise = :idAutorise";
        $req1 = $this->cnx->prepare($txt_req1);
        $req1->bindValue("idAutorisant", $idAutorisant, PDO::PARAM_INT);
        $req1->bindValue("idAutorise", $idAutorise, PDO::PARAM_INT);
        
        $ok = $req1->execute();
        
        if ($ok) return true;
        else return false;
    }
       

    
    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 3 (Riquier) : lignes 750 à 949
    // --------------------------------------------------------------------------------------
    
    public function getLesPointsDeTrace($idTrace) {
        // préparation de la requête de recherche
        $txt_req = "Select id, latitude, longitude, altitude, dateHeure, rythmeCardio";
        $txt_req .= " from tracegps_Points";
        $txt_req .= " where idTrace = $idTrace";
        $txt_req .= " order by id";
        
        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Utilisateur
        $lesPointsDeTrace = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            
            $unId = utf8_encode($uneLigne->id);
            $uneLatitude = utf8_encode($uneLigne->latitude);
            $uneLongitude = utf8_encode($uneLigne->longitude);
            $uneAltitude = utf8_encode($uneLigne->altitude);
            $uneDateHeure = utf8_encode($uneLigne->dateHeure);
            $unRythmeCardio = utf8_encode($uneLigne->rythmeCardio);
            
            $unTempsCumule = 0 ;	// temps cumulé depuis le départ (en secondes)
            $uneDistanceCumulee = 0;	// distance cumulée depuis le départ (en Km)
            $uneVitesse = 0;
            
            $unPointDeTrace = new PointDeTrace($idTrace, $unId, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);
            // ajout de l'utilisateur à la collection
            $lesPointsDeTrace[] = $unPointDeTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $lesPointsDeTrace;
    }
    
    public function creerUnPointDeTrace($unPointDeTrace) {
        // préparation de la requête
        $txt_req1 = "insert into tracegps_points (idTrace, id, latitude, longitude, altitude, dateHeure, rythmeCardio)";
        $txt_req1 .= " values (:idTrace, :id, :latitude, :longitude, :altitude, :dateHeure, :rythmeCardio)";
        $req1 = $this->cnx->prepare($txt_req1);
        
        // liaison de la requête et de ses paramètres
        $req1->bindValue("idTrace", $unPointDeTrace->getIdTrace(), PDO::PARAM_INT);
        $req1->bindValue("id", $unPointDeTrace->getId(), PDO::PARAM_INT);
        $req1->bindValue("latitude", $unPointDeTrace->getLatitude(), PDO::PARAM_STR);
        $req1->bindValue("longitude", $unPointDeTrace->getLongitude(), PDO::PARAM_STR);
        $req1->bindValue("altitude", $unPointDeTrace->getAltitude(), PDO::PARAM_STR);
        $req1->bindValue("dateHeure", $unPointDeTrace->getDateHeure(), PDO::PARAM_STR);
        $req1->bindValue("rythmeCardio", $unPointDeTrace->getRythmeCardio(), PDO::PARAM_INT);
        
        // exécution de la requête
        $ok = $req1->execute();
        
        // sortir en cas d'échec
        if ( ! $ok) { return false; }
        
        if ($unPointDeTrace->getId() == 1)
        {
            $txt_req2 = "update tracegps_traces set dateDebut = :dateDebut where id = :id";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requête et de ses paramètres
            $req2->bindValue("id", $unPointDeTrace->getIdTrace(), PDO::PARAM_INT);
            $req2->bindValue("dateDebut", $unPointDeTrace->getDateHeure(), PDO::PARAM_STR);
            // extraction des données
            $req2->execute();
        }
        return true;
    }
    
    

   


    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
        
    
    
    
    
    
    
    
    
    
    
    
    
   

    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 4 (EPS Erwan) : lignes 950 à 1150
    // --------------------------------------------------------------------------------------
    
    // fournit un objet Trace à partir de son pseudo $idUtilisateur
    // fournit la valeur null si le pseudo n'existe pas
    // modifié par Erwan le 16/10/2018
    public function getLesTraces($idUtilisateur) {
        // préparation de la requête de recherche
        $txt_req = "Select id, dateDebut, dateFin, terminee, idUtilisateur";
        $txt_req .= " from tracegps_traces";
        $txt_req .= " where idUtilisateur = :idUtilisateur";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Trace
        $lesTraces = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Trace
            $unId = utf8_encode($uneLigne->id);
            $uneDateDebut = utf8_encode($uneLigne->dateDebut);
            $uneDateFin = utf8_encode($uneLigne->dateFin);
            $terminee = utf8_encode($uneLigne->terminee);
            $unIdUtilisateur = utf8_encode($uneLigne->idUtilisateur);
            
            $uneTrace = new Trace($unId, $uneDateDebut, $uneDateFin, $terminee, $unIdUtilisateur);
            
            $lesPointsDeTrace = getLesPointsDeTrace($unId);
            
            $uneTrace->ajouterPoint($lesPointsDeTrace);
            
            // ajout de l'utilisateur à la collection
            $lesTraces[] = $uneTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        return $lesTraces;
    }
    
    public function getLesTracesAutorisees($idUtilisateur) {
        // préparation de la requête de recherche
        $txt_req = "Select id, dateDebut, dateFin, terminee, idUtilisateur";
        $txt_req .= " from tracegps_traces";
        $txt_req .= " where idUtilisateur = :idUtilisateur";
        $txt_req .= " and idUtilisateur in (select idAutorise";
        $txt_req .= " from tracegps_autorisations where idAutorise = :idUtilisateur)";
        
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        
        // construction d'une collection d'objets Trace
        $lesTraces = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Trace
            $unId = utf8_encode($uneLigne->id);
            $uneDateDebut = utf8_encode($uneLigne->dateDebut);
            $uneDateFin = utf8_encode($uneLigne->dateFin);
            $terminee = utf8_encode($uneLigne->terminee);
            $unIdUtilisateur = utf8_encode($uneLigne->idUtilisateur);
            
            $uneTrace = new Trace($unId, $uneDateDebut, $uneDateFin, $terminee, $unIdUtilisateur);
            
            $lesPointsDeTrace = getLesPointsDeTrace($unId);
            
            $uneTrace->ajouterPoint($lesPointsDeTrace);
            
            // ajout de l'utilisateur à la collection
            $lesTraces[] = $uneTrace;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        
        return $lesTraces;
    }
    
    public function creerUneTrace($uneTrace) {
        // préparation de la requête
        if ($uneTrace->getTerminee() == false) {
            $txt_req1 = "insert into tracegps_traces (dateDebut, dateFin, terminee, idUtilisateur)";
            $txt_req1 .= " values (:dateDebut, null, :terminee, :idUtilisateur)";
        } else {
            $txt_req1 = "insert into tracegps_traces (dateDebut, dateFin, terminee, idUtilisateur)";
            $txt_req1 .= " values (:dateDebut, :dateFin, :terminee, :idUtilisateur)";
        }
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("dateDebut", $uneTrace->getDateHeureDebut(), PDO::PARAM_STR);
        
        if ($uneTrace->getTerminee() == true) {
            $req1->bindValue("dateFin", $uneTrace->getDateHeureFin(), PDO::PARAM_STR);
        }
        
        $req1->bindValue("terminee", $uneTrace->getTerminee(), PDO::PARAM_INT);
        $req1->bindValue("idUtilisateur", $uneTrace->getIdUtilisateur(), PDO::PARAM_INT);
        // exécution de la requête
        $ok = $req1->execute();
        // sortir en cas d'échec
        if ( ! $ok) { return false; }
        
        // recherche de l'identifiant (auto_increment) qui a été attribué à la trace
        $txt_req2 = "Select max(id) as idMax from tracegps_traces";
        $req2 = $this->cnx->prepare($txt_req2);
        // extraction des données
        $req2->execute();
        $uneLigne = $req2->fetch(PDO::FETCH_OBJ);
        $unId = $uneLigne->idMax;
        $uneTrace->setId($unId);
        return true;
    }
    
    public function supprimerUneTrace($uneTrace) {
        // préparation de la requête de suppression des autorisations
        $txt_req1 = "delete from tracegps_traces" ;
        $txt_req1 .= " where id = :idTrace";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("idTrace", $uneTrace, PDO::PARAM_INT);
        // exécution de la requête
        $ok = $req1->execute();
        
        // préparation de la requête de suppression de l'utilisateur
        $txt_req2 = "delete from tracegps_points" ;
        $txt_req2 .= " where idTrace = :idTrace";
        $req2 = $this->cnx->prepare($txt_req2);
        // liaison de la requête et de ses paramètres
        $req2->bindValue("idTrace", $uneTrace, PDO::PARAM_INT);
        // exécution de la requête
        $ok = $req2->execute();
        return $ok;
    }
    
    public function terminerUneTrace($uneTrace) {
        $lesPointsDeTraces = getLesPointsDeTrace($uneTrace);
        
        for ($i = 0; $i < sizeof($lesPointsDeTraces); $i++) {
            if ($lesPointsDeTraces[$i] < sizeof($lesPointsDeTraces))
                $dateFin = $lesPointsDeTraces[$i]->getDateHeure();
        }
        
        // préparation de la requête de la modifcation des autorisations
        $txt_req1 = "update tracegps_traces";
        $txt_req1 .= " set terminee = :terminee and dateFin = :dateFin" ;
        $txt_req1 .= " where id = :idTrace";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("idTrace", $uneTrace, PDO::PARAM_INT);
        $req1->bindValue("terminee", 1, PDO::PARAM_INT);
        $req1->bindValue("dateFin", $dateFin, PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req1->execute();
        
        return $ok;
    } 
    
} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces apr�s la balise de fin de script !!!!!!!!!!!!