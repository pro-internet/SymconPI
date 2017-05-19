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


            $rWert     = CreateVariable("rWert",      3 /*String*/,  $categoryId_DataValues,  140, '~String', $scriptId_Refresh, '');

          
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