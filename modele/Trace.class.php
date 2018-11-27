<?php
// Projet TraceGPS
// fichier : modele/Trace.class.php
// Rôle : la classe Trace représente une trace ou un parcours
// Dernière mise à jour : 02/10/2018 par EPS Erwan

include_once ('PointDeTrace.class.php');

class Trace
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Attributs privés de la classe -------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    private $id;				// identifiant de la trace
    private $dateHeureDebut;		// date et heure de début
    private $dateHeureFin;		// date et heure de fin
    private $terminee;			// true si la trace est terminée, false sinon
    private $idUtilisateur;		// identifiant de l'utilisateur ayant créé la trace
    private $lesPointsDeTrace;		// la collection (array) des objets PointDeTrace formant la trace
    
    // ------------------------------------------------------------------------------------------------------
    // ----------------------------------------- Constructeur -----------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function Trace($unId, $uneDateHeureDebut, $uneDateHeureFin, $terminee, $unIdUtilisateur) {
        $this->lesPointsDeTrace = array();
        
        $this->id = $unId;				
        $this->dateHeureDebut = $uneDateHeureDebut;	
        $this->dateHeureFin = $uneDateHeureFin;		
        $this->terminee = $terminee;		
        $this->idUtilisateur = $unIdUtilisateur;}
        
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------------- Getters et Setters ------------------------------------------
    // ------------------------------------------------------------------------------------------------------
    
    public function getId() {return $this->id;}
    public function setId($unId) {$this->id = $unId;}
    
    public function getDateHeureDebut() {return $this->dateHeureDebut;}
    public function setDateHeureDebut($uneDateHeureDebut) {$this->dateHeureDebut = $uneDateHeureDebut;}
    
    public function getDateHeureFin() {return $this->dateHeureFin;}
    public function setDateHeureFin($uneDateHeureFin) {$this->dateHeureFin= $uneDateHeureFin;}
    
    public function getTerminee() {return $this->terminee;}
    public function setTerminee($terminee) {$this->terminee = $terminee;}
    
    public function getIdUtilisateur() {return $this->idUtilisateur;}
    public function setIdUtilisateur($unIdUtilisateur) {$this->idUtilisateur = $unIdUtilisateur;}
    
    public function getLesPointsDeTrace() {return $this->lesPointsDeTrace;}
    public function setLesPointsDeTrace($lesPointsDeTrace) {$this->lesPointsDeTrace = $lesPointsDeTrace;}
    
    // Fournit une chaine contenant toutes les données de l'objet
    public function toString() {
        $msg = "Id : " . $this->getId() . "<br>";
        $msg .= "Utilisateur : " . $this->getIdUtilisateur() . "<br>";
        if ($this->getDateHeureDebut() != null) {
            $msg .= "Heure de début : " . $this->getDateHeureDebut() . "<br>";
        }
        if ($this->getTerminee()) {
            $msg .= "Terminée : Oui  <br>";
        }
        else {
            $msg .= "Terminée : Non  <br>";
        }
        $msg .= "Nombre de points : " . $this->getNombrePoints() . "<br>";
        if ($this->getNombrePoints() > 0) {
            if ($this->getDateHeureFin() != null) {
                $msg .= "Heure de fin : " . $this->getDateHeureFin() . "<br>";
            }
            $msg .= "Durée en secondes : " . $this->getDureeEnSecondes() . "<br>";
            $msg .= "Durée totale : " . $this->getDureeTotale() . "<br>";
            $msg .= "Distance totale en Km : " . $this->getDistanceTotale() . "<br>";
            $msg .= "Dénivelé en m : " . $this->getDenivele() . "<br>";
            $msg .= "Dénivelé positif en m : " . $this->getDenivelePositif() . "<br>";
            $msg .= "Dénivelé négatif en m : " . $this->getDeniveleNegatif() . "<br>";
            $msg .= "Vitesse moyenne en Km/h : " . $this->getVitesseMoyenne() . "<br>";
            $msg .= "Centre du parcours : " . "<br>";
            $msg .= "   - Latitude : " . $this->getCentre()->getLatitude() . "<br>";
            $msg .= "   - Longitude : "  . $this->getCentre()->getLongitude() . "<br>";
            $msg .= "   - Altitude : " . $this->getCentre()->getAltitude() . "<br>";
        }
        return $msg;
    }
    
    public function getNombrePoints() {
        return sizeof($this->lesPointsDeTrace);
    }
    
    public function getCentre()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return null;
            else
            {
                $premierPoint = $this->lesPointsDeTrace[0];
                $centre = new Point(0,0,0);
                $latitudeMin = $premierPoint->getLatitude();
                $latitudeMax = $premierPoint->getLatitude();
                $longitudeMin = $premierPoint->getLongitude();
                $longitudeMax = $premierPoint->getLongitude();
                
                for ($i=1; $i < sizeof($this->lesPointsDeTrace); $i++)
                {
                    if ($this->lesPointsDeTrace[$i]->getLatitude() <= $latitudeMin)
                        $latitudeMin = $this->lesPointsDeTrace[$i]->getLatitude();
                        
                    if ($this->lesPointsDeTrace[$i]->getLatitude() >= $latitudeMax)
                        $latitudeMax = $this->lesPointsDeTrace[$i]->getLatitude();
                        
                    if ($this->lesPointsDeTrace[$i]->getLongitude() <= $longitudeMin)
                        $longitudeMin = $this->lesPointsDeTrace[$i]->getLongitude();
                        
                    if ($this->lesPointsDeTrace[$i]->getLongitude() >= $longitudeMax)
                        $longitudeMax = $this->lesPointsDeTrace[$i]->getLongitude();
                }
                
                $latitudeMoy = ($latitudeMin + $latitudeMax) / 2;
                $longitudeMoy = ($longitudeMin + $longitudeMax) / 2;
                
                $centre->setLatitude($latitudeMoy);
                $centre->setLongitude($longitudeMoy);
                
                return $centre;
            }
    }
    
    public function getDenivele() {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else
            {
                $premierPoint =$this->lesPointsDeTrace[0];
                $altitudeMin = $premierPoint->getAltitude();
                $altitudeMax = $premierPoint->getAltitude();
                
                foreach ($this->lesPointsDeTrace as $point)
                {
                    if ($point->getAltitude() <= $altitudeMin)
                        $altitudeMin = $point->getAltitude();
                        
                    if ($point->getAltitude() >= $altitudeMax)
                        $altitudeMax = $point->getAltitude();
                }
                
                return $altitudeMax - $altitudeMin;
            }
    }
    
    public function getDureeEnSecondes()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else
            {
                $premierPoint = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];
                return $premierPoint->getTempsCumule();
            }
    }
    
    public function getDureeTotale()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return "00:00:00";
            else
            {     
                $heures = Trace::getDureeEnSecondes() / 3600;
                $minutes = (Trace::getDureeEnSecondes() % 3600) / 60;
                $secondes = (Trace::getDureeEnSecondes() % 3600) % 60;
                
                return sprintf("%02d",$heures) . ":" . sprintf("%02d",$minutes)  . ":" . sprintf("%02d",$secondes) ;
            }
    }
    
    public function getDistanceTotale()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else
            {
                $premierPoint =$this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];
                return $premierPoint->getDistanceCumulee();
            }
    }

    public function getDenivelePositif()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else
            {
                $denivelePos = 0;
                for ($i = 0; $i < (sizeof($this->lesPointsDeTrace) - 1); $i++)
                {
                    $point1 = $this->lesPointsDeTrace[$i];
                    $point2 = $this->lesPointsDeTrace[$i + 1];
                    
                    if ($point1->getAltitude() < $point2->getAltitude())
                        $denivelePos += $point2->getAltitude() - $point1->getAltitude();
                }
                
                return $denivelePos;
            }
    }
    
    public function getDeniveleNegatif()
    {
        if (sizeof($this->lesPointsDeTrace) == 0)
            return 0;
            else
            {
                $deniveleNeg = 0;
                for ($i = 0; $i < (sizeof($this->lesPointsDeTrace) - 1); $i++)
                {
                    $point1 = $this->lesPointsDeTrace[$i];
                    $point2 = $this->lesPointsDeTrace[$i + 1];
                    
                    if ($point1->getAltitude() > $point2->getAltitude())
                        $deniveleNeg += $point1->getAltitude() - $point2->getAltitude();
                }
                
                return $deniveleNeg;
            }
    }

    public function getVitesseMoyenne()
    {
        if ($this->getDureeEnSecondes() == 0)
            return 0;
        else
        {
            $distance = $this->getDistanceTotale();
            $secondes = $this->getDureeEnSecondes();
            return $distance / ($secondes / 3600);
        }
    }
    
    public function ajouterPoint($unPoint)
    {
        if ($this->getNombrePoints() == 0)
        {
            $unPoint->setDistanceCumulee(0);
            $unPoint->setTempsCumule(0);
            $unPoint->setVitesse(0);            
        }
        else
        {
            $pointPrecedent = $this->lesPointsDeTrace[sizeof($this->lesPointsDeTrace) - 1];
                        
            $unPoint->setDistanceCumulee($pointPrecedent->getDistanceCumulee() + Point::getDistance($unPoint, $pointPrecedent));
            
            $temps = strtotime($unPoint->getDateHeure()) - strtotime($pointPrecedent->getDateHeure());
            $unPoint->setTempsCumule($pointPrecedent->getTempsCumule() + $temps);
            if($temps == 0) 
                $vitesse = 0;
            else 
                $vitesse = Point::getDistance($unPoint, $pointPrecedent) / ($temps / 3600);
            
            $unPoint->setVitesse($vitesse);
        }
        $this->lesPointsDeTrace[] = $unPoint;
    }
    
    public function viderListePoints()
    {
        $this->lesPointsDeTrace = array();
    }
} // fin de la classe Trace

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
