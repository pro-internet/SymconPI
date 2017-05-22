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

            if(@$this->RegisterPropertyString("Lichter") !== false){
                $this->RegisterPropertyString("Lichter","");
            }
            
            $parent = $this->InstanceID;

            // Create Instance Profies
            if(!IPS_VariableProfileExists("DMX.Dim")){
			    $this->CreateProfile("DMX.Dim", 1, 0, 100, 1, 1, "", "%");
		    }
            if(!IPS_VariableProfileExists("DMX.Fade")){
                $this->CreateProfile("DMX.Fade", 1, 0, 10, 1, 1, "", "s");
            }
            if(!IPS_VariableProfileExists("~Switch")){
                $this->CreateProfile("~Switch", 0, 0, 1, 1, 1, "", "");
            }

            // Create Instance Vars (RGBW & FadeWert)
            // CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action)
            $vid = $this->CreateVariable(1,"Global R","VarID_RWert", $parent, 0, 0, "DMX.Dim", "16562", false);
            $vid = $this->CreateVariable(1,"Global G","VarID_GWert", $parent, 1, 0, "DMX.Dim", "16562", false);
            $vid = $this->CreateVariable(1,"Global B","VarID_BWert", $parent, 2, 0, "DMX.Dim", "16562", false);
            $vid = $this->CreateVariable(1,"Global W","VarID_WWert", $parent, 3, 0, "DMX.Dim", "16562", false);
            $vid = $this->CreateVariable(1, "Global Fade","VarID_FadeWert", $parent, 4, 0, "DMX.Fade", "16562", false);

            
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Don't delete this Row!
            parent::ApplyChanges();

            $parent = $this->InstanceID;


            $moduleList = IPS_GetModuleList();
            $dummyGUID = ""; //init
            foreach($moduleList as $l){
                if(IPS_GetModule($l)['ModuleName'] == "Dummy Module"){
                    $dummyGUID = $l;
                    break;
                }
            }

            // On Apply read Device List
            $deviceList = json_decode($this->ReadPropertyString("Lichter"));
            
            if (is_array($deviceList) || is_object($deviceList)){
                foreach($deviceList as $i => $list){

                    if(@IPS_GetObjectIDByIdent("device$i", IPS_GetParent($this->InstanceID)) === false){
                        $insID = IPS_CreateInstance($dummyGUID);	
                        IPS_SetParent($insID, IPS_GetParent($this->InstanceID));					
                    }
                    else{
                        $insID = IPS_GetObjectIDByIdent("device$i", IPS_GetParent($this->InstanceID));
                    }

                    IPS_SetName($insID, $list->Name);
                    IPS_SetPosition($insID, $i + 1);
                    IPS_SetIdent($insID, "device$i");

                    // Generate Values
                    $vid = $this->CreateVariable(1,"R", "R", $insID, 1, 1, "DMX.Dim", "16562", TRUE);
                    $vid = $this->CreateVariable(1,"G", "G", $insID, 2, 2, "DMX.Dim", "16562", TRUE);
                    $vid = $this->CreateVariable(1,"B", "B", $insID, 3, 3, "DMX.Dim", "16562", TRUE);
                    $vid = $this->CreateVariable(1,"W", "W", $insID, 4, 4, "DMX.Dim", "16562", TRUE);
                    $vid = $this->CreateVariable(1,"Fade", "Fade", $insID, 5, 5, "DMX.Fade", "16562", TRUE);

                    // Generate Switch
                    $vid = $this->CreateVariable(0, "Switch", "Swtich", $insID, 0, 0, "~Switch", "16562", FALSE);
                }
            }

        }

       protected function CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action, $hide){
            $vid = IPS_CreateVariable($type);
            IPS_SetName($vid,$name);
            IPS_SetParent($vid,$parent);
            IPS_SetIdent($vid,$ident);
            IPS_SetPosition($vid,$position);
            SetValue($vid,$initVal);
            IPS_SetHidden($vid, $hide); //Objekt verstecken
            if(!empty($profile)){
                IPS_SetVariableCustomProfile($vid,$profile);
            }
            if(!empty($action)){
                IPS_SetVariableCustomAction($vid,$action);
            }
            return $vid;
       }
        
       protected function CreateProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "DMX", $suffix = "", $icon = ""){
            IPS_CreateVariableProfile($profile, $type);
            IPS_SetVariableProfileValues($profile, $min, $max, $steps);
            IPS_SetVariableProfileText($profile, $prefix, $suffix);
            IPS_SetVariableProfileDigits($profile, $digits);
            IPS_SetVariableProfileIcon($profile, $icon);
       }

        // Own Function
        public function ownFirstFunction() {
           
        }
    }
?>