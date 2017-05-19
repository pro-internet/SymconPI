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


			$parent = $this->InstanceID;

            // Create Instance Vars (RGBW)
            $VarID_RWert = IPS_CreateVariable(1);
            IPS_SetName($VarID_RWert, "R Standart Wert");
            IPS_SetParent($VarID_RWert, $parent);

            $VarID_GWert = IPS_CreateVariable(1);
            IPS_SetName($VarID_GWert, "G Standart Wert");
            IPS_SetParent($VarID_GWert, $parent);

            $VarID_BWert = IPS_CreateVariable(1);
            IPS_SetName($VarID_BWert, "B Standart Wert");
            IPS_SetParent($VarID_BWert, $parent);

            $VarID_WWert = IPS_CreateVariable(1);
            IPS_SetName($VarID_WWert, "W Standart Wert");
            IPS_SetParent($VarID_WWert, $parent);
          
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