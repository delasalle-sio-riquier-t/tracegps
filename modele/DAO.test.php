<?php
// Projet TraceGPS
// fichier : modele/DAO.test.php
// Rôle : test de la classe DAO.class.php
// Dernière mise à jour : 15/8/2018 par JM CARTRON
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Test de la classe DAO</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web à la base MySQL
include_once ('DAO.class.php');
//include_once ('_DAO.mysql.class.php');
$dao = new DAO();

/*
// test de la méthode getNiveauConnexion ----------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de getNiveauConnexion : </h3>";
$niveau = $dao->getNiveauConnexion("admin", sha1("mdpadmin"));
echo "<p>Niveau de ('admin', 'mdpadmin') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("mdputilisateur"));
echo "<p>Niveau de ('europa', 'mdputilisateur') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("europa", sha1("123456"));
echo "<p>Niveau de ('europa', '123456') : " . $niveau . "</br>";

$niveau = $dao->getNiveauConnexion("toto", sha1("mdputilisateur"));
echo "<p>Niveau de ('toto', 'mdputilisateur') : " . $niveau . "</br>";
*/


/*
// test de la méthode existePseudoUtilisateur -----------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de existePseudoUtilisateur : </h3>";
if ($dao->existePseudoUtilisateur("admin")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin' : <b>" . $existe . "</b><br>";
if ($dao->existePseudoUtilisateur("europa")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'europa' : <b>" . $existe . "</b></br>";
if ($dao->existePseudoUtilisateur("toto")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'toto' : <b>" . $existe . "</b></p>";
*/


/*
// test de la méthode getUnUtilisateur -----------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de getUnUtilisateur : </h3>";
$unUtilisateur = $dao->getUnUtilisateur("admin");
if ($unUtilisateur) {
    echo "<p>L'utilisateur admin existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur admin n'existe pas !</p>";
}
$unUtilisateur = $dao->getUnUtilisateur("europa");
if ($unUtilisateur) {
    echo "<p>L'utilisateur europa existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur europa n'existe pas !</p>";
}
$unUtilisateur = $dao->getUnUtilisateur("admon");
if ($unUtilisateur) {
    echo "<p>L'utilisateur admon existe : <br>" . $unUtilisateur->toString() . "</p>";
}
else {
    echo "<p>L'utilisateur admon n'existe pas !</p>";
}
*/  


/*
// test de la méthode getTousLesUtilisateurs ------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de getTousLesUtilisateurs : </h3>";
$lesUtilisateurs = $dao->getTousLesUtilisateurs();
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{	echo ($unUtilisateur->toString());
    echo ('<br>');
}
*/


/*
// test de la méthode creerUnUtilisateur ----------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de creerUnUtilisateur : </h3>";
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "toto@gmail.com", "5566778899", 1, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
if ($ok)
{   echo "<p>Utilisateur bien enregistré !</p>";
    echo $unUtilisateur->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de l'utilisateur !</p>";
}
*/


/*
// test de la méthode modifierMdpUtilisateur ------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de modifierMdpUtilisateur : </h3>";
$unUtilisateur = $dao->getUnUtilisateur("toto");
if ($unUtilisateur) {
    echo "<p>Ancien mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
    $dao->modifierMdpUtilisateur("toto", "mdpadmin");
    $unUtilisateur = $dao->getUnUtilisateur("toto");
    echo "Nouveau mot de passe de l'utilisateur toto : <b>" . $unUtilisateur->getMdpSha1() . "</b><br>";
    
    $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdputilisateur'));
    echo "Niveau de connexion de ('toto', 'mdputilisateur') : <b>" . $niveauDeConnexion . "</b><br>";
    
    $niveauDeConnexion = $dao->getNiveauConnexion('toto', sha1('mdpadmin'));
    echo "Niveau de connexion de ('toto', 'mdpadmin') : <b>" . $niveauDeConnexion . "</b></p>";
}
else {
    echo "<p>L'utilisateur toto n'existe pas !</p>";
}
*/


/*
// test de la méthode supprimerUnUtilisateur ------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de supprimerUnUtilisateur : </h3>";
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimé !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimé !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/


/*
// test de la méthode envoyerMdp ------------------------------------------------------------------
// modifié par Jim le 12/8/2018
echo "<h3>Test de envoyerMdp : </h3>";
// pour ce test, une adresse mail que vous pouvez consulter
$unUtilisateur = new Utilisateur(0, "toto", "mdputilisateur", "jean.michel.cartron@gmail.com", "5566778899", 2, date('Y-m-d H:i:s', time()), 0, null);
$ok = $dao->creerUnUtilisateur($unUtilisateur);
$dao->modifierMdpUtilisateur("toto", "mdpadmin");
$ok = $dao->envoyerMdp("toto", "mdpadmin");
if ($ok) {
    echo "<p>Mail bien envoyé !</p>";
}
else {
    echo "<p>Echec lors de l'envoi du mail !</p>";
}
// supprimer le compte créé
$ok = $dao->supprimerUnUtilisateur("toto");
if ($ok) {
    echo "<p>Utilisateur toto bien supprimé !</p>";
}
else {
    echo "<p>Echec lors de la suppression de l'utilisateur toto !</p>";
}
*/





// Le code restant à développer va être réparti entre les membres de l'équipe de développement.
// Afin de limiter les conflits avec GitHub, il est décidé d'attribuer une zone de ce fichier à chaque développeur.
// Développeur 1 : lignes 200 à 299
// Développeur 2 : lignes 300 à 399
// Développeur 3 : lignes 400 à 500

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
// la dernière version du fichier.
// Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.





// --------------------------------------------------------------------------------------
// début de la zone attribuée au développeur 1 (Ogu) : lignes 200 à 299
// --------------------------------------------------------------------------------------

// test de la m�thode existeAdrMailUtilisateur ----------------------------------------------------
// modifi� par Jim le 12/8/2018
echo "<h3>Test de existeAdrMailUtilisateur : </h3>";
if ($dao->existeAdrMailUtilisateur("admin@gmail.com")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin@gmail.com' : <b>" . $existe . "</b><br>";
if ($dao->existeAdrMailUtilisateur("delasalle.sio.eleves@gmail.com")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'delasalle.sio.eleves@gmail.com' : <b>" . $existe . "</b></br>";


// test de la m�thode autoriseAConsulter ----------------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de autoriseAConsulter : </h3>";
if ($dao->autoriseAConsulter(2, 3)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 2 autorise l'utilisateur 3 : <b>" . $autorise . "</b><br>";

if ($dao->autoriseAConsulter(3, 2)) $autorise = "oui"; else $autorise = "non";
echo "<p>L'utilisateur 3 autorise l'utilisateur 2 : <b>" . $autorise . "</b><br>";

// test de la m�thode creerUneAutorisation ---------------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de creerUneAutorisation : </h3>";
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La cr�ation de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a r�ussi : <b>" . $ok . "</b><br>";
// la m�me autorisation ne peut pas �tre enregistr�e 2 fois
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La cr�ation de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a r�ussi : <b>" . $ok . "</b><br>";

// test de la m�thode getLesUtilisateursAutorisant ------------------------------------------------
// modifi� par Jim le 13/8/2018
// test de la m�thode getLesUtilisateursAutorisant ------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de getLesUtilisateursAutorisant(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorisant(4);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisant l'utilisateur 4 � voir leurs parcours : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{   echo ($unUtilisateur->toString());
echo ('<br>');
}

// test de la m�thode getLesUtilisateursAutorises -------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de getLesUtilisateursAutorises(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorises(2);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autoris�s par l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{	echo ($unUtilisateur->toString());
echo ('<br>');
}




























































































// --------------------------------------------------------------------------------------
// début de la zone attribuée au développeur 2 (vincent) : lignes 300 à 399
// --------------------------------------------------------------------------------------
// test de la m�thode getLesUtilisateursAutorisant ------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de getLesUtilisateursAutorisant(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorisant(4);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisant l'utilisateur 4 � voir leurs parcours : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{   echo ($unUtilisateur->toString());
echo ('<br>');
}

// test de la m�thode getLesUtilisateursAutorises -------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de getLesUtilisateursAutorises(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorises(2);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autoris�s par l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{	echo ($unUtilisateur->toString());
echo ('<br>');
}

// test de la m�thode supprimerUneAutorisation ----------------------------------------------------
// modifi� par Jim le 13/8/2018
echo "<h3>Test de supprimerUneAutorisation : </h3>";
// on cr�e une autorisation
if ($dao->creerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La cr�ation de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a r�ussi : <b>" . $ok . "</b><br>";
// puis on la supprime
if ($dao->supprimerUneAutorisation(2, 1)) $ok = "oui"; else $ok = "non";
echo "<p>La suppression de l'autorisation de l'utilisateur 2 vers l'utilisateur 1 a r�ussi : <b>" . $ok . "</b><br>";


































































































// --------------------------------------------------------------------------------------
// début de la zone attribuée au développeur 3 (xxxxxxxxxxxxxxxxxxxx) : lignes 400 à 499
// --------------------------------------------------------------------------------------


































































































// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>