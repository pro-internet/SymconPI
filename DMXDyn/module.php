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
			

            // Create Instance Vars (RGBW & FadeWert)
            $vid = $this->CreateVariable(1,"R Standart Wert","VarID_RWert", $parent, 0, 0);
            $vid = $this->CreateVariable(1,"G Standart Wert","VarID_GWert", $parent, 1, 0);
            $vid = $this->CreateVariable(1,"B Standart Wert","VarID_BWert", $parent, 2, 0);
            $vid = $this->CreateVariable(1,"W Standart Wert","VarID_WWert", $parent, 3, 0);
            $vid = $this->CreateVariable(1, "Fade Standart Wert","VarID_FadeWert", $parent, 4, 0);

        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Don't delete this Row!
            parent::ApplyChanges();

            $parent = $this->InstanceID;

            // On Apply read Device List
            $deviceList = json_decode($this->ReadPropertyString("Lichter"));
            print_r($deviceList);
        }

       protected function CreateVariable($type, $name, $ident, $parent, $position, $initVal){
            $vid = IPS_CreateVariable($type);
            IPS_SetName($vid,$name);
            IPS_SetParent($vid,$parent);
            IPS_SetIdent($vid,$ident);
            IPS_SetPosition($vid,$position);
            SetValue($vid,$initVal);
            
            return $vid;
        }
 


        // Own Function
        public function ownFirstFunction() {
           
        }
    }
?>