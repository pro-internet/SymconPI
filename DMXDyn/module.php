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
 
        // Erstellt eine Instanz Variable
        protected function CreateVariable($type, $name, $ident, $profile, $actionID, $parent = "thisInstance", $position = 0, $initVal = 0){
            if($parent == "thisInstance")
                $parent = $this->InstanceID;
            $vid = IPS_CreateVariable($type);
            IPS_SetName($vid,$name);
            IPS_SetParent($vid,$parent);
            IPS_SetIdent($vid,$ident);
            IPS_SetPosition($vid,$position);
            IPS_SetVariableCustomProfile($vid,$profile);
            if($actionID > 9999)
                IPS_SetVariableCustomAction($vid,$actionID);
            SetValue($vid,$initVal);
            
            return $vid;
        }

        // Own Function
        public function ownFirstFunction() {
           
        }
    }
?>