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
 
        // Get the Weather Forcast for the Day!
        public function ownFirstClass() {
           
        }
    }
?>