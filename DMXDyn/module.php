<?
    // Klassendefinition
    class DMXDyn extends IPSModule {
        
        // Constructor
        public function __construct($InstanceID) {
            // Don't delete this Row!
            parent::__construct($InstanceID);
         }
        // Create Instance
        public function Create() {
            // Don't delete this Row!
            parent::Create();

            if($parent == "thisInstance")
			    $parent = $this->InstanceID;

            // Create Instance Vars
            $VarID_RWert = IPS_CreateVariable(1);
            IPS_SetName($VarID_RWert, "R Standart Wert");
            IPS_SetParent($VarID_Raumtemperatur, $parent);
          
          /*
            $this->RegisterPropertyString("woeid", "701780");
            $this->RegisterPropertyString("Degree", "C");
          */
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Don't delete this Row!
            parent::ApplyChanges();
        }
 


        // Own Function
        public function ownFirstFunction() {
           
        }
    }
?>