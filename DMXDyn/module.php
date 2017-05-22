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

            // Create Instance Vars (RGBW & FadeWert)
            $vid = $this->CreateVariable(1,"R Standart Wert","VarID_RWert", $parent, 0, 0);
            $vid = $this->CreateVariable(1,"G Standart Wert","VarID_GWert", $parent, 1, 0);
            $vid = $this->CreateVariable(1,"B Standart Wert","VarID_BWert", $parent, 2, 0);
            $vid = $this->CreateVariable(1,"W Standart Wert","VarID_WWert", $parent, 3, 0);
            $vid = $this->CreateVariable(1, "Fade Standart Wert","VarID_FadeWert", $parent, 4, 0);

            IPS_SetHidden($parent, true); //Objekt verstecken
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

                    $vid = $this->CreateVariable(1,"R", "R", $insID, 0, 1);
                    $vid = $this->CreateVariable(1,"G", "G", $insID, 1, 2);
                    $vid = $this->CreateVariable(1,"B", "B", $insID, 2, 3);
                    $vid = $this->CreateVariable(1,"W", "W", $insID, 3, 4);
                    $vid = $this->CreateVariable(1,"Fade", "Fade", $insID, 4, 20);
                }
            }

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