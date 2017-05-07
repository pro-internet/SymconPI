<?
class PWM extends IPSModule {
	
	protected function CreateProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "", $suffix = "", $icon = "")
	{
		IPS_CreateVariableProfile($profile, $type);
		IPS_SetVariableProfileValues($profile, $min, $max, $steps);
		IPS_SetVariableProfileText($profile, $prefix, $suffix);
		IPS_SetVariableProfileDigits($profile, $digits);
		IPS_SetVariableProfileIcon($profile, $icon);
	}

	protected function CreateVariable($type, $name, $ident, $profile, $actionID, $parent = "thisInstance", $position = 0, $initVal = 0)
	{
		if($parent == "thisInstance")
			$parent = $this->InstanceID;
		$vid = IPS_CreateVariable($type);
		IPS_SetName($vid,$name);
		IPS_SetParent($vid,$parent);
		IPS_SetIdent($vid,$ident);
		IPS_SetPosition($vid,$position);
		IPS_SetVariableCustomProfile($vid,$profile);
		IPS_SetVariableCustomAction($vid,$actionID);
		SetValue($vid,$initVal);
		
		return $vid;
	}
	
	protected function CreateTimer($name, $ident, $script, $parent = "thisInstance")
	{
		if($parent == "thisInstance")
			$parent = $this->InstanceID;
		if(@IPS_GetObjectIDByIdent($ident, $parent) === false)
		{
			$eid = IPS_CreateEvent(1 /*züklisch*/);
			IPS_SetName($eid, $name);
			IPS_SetParent($eid, $parent);
			IPS_SetIdent($eid, $ident);
			IPS_SetEventScript($eid, $script);
		}
		else
		{
			$eid = IPS_GetObjectIDByIdent($ident, $parent);
		}
		return $eid;
	}

	public function Create() {
		//Never delete this line!
		parent::Create();

		if(@$this->RegisterPropertyInteger("Stellmotor") !== false)
		{
			$this->RegisterPropertyInteger("Stellmotor",0);
			$this->RegisterPropertyInteger("IstWert",0);
		}
		
		//Ist-Wert onChange Event
		if(@IPS_GetObjectIDByIdent("IstwertOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Istwert onChange");
			IPS_SetIdent($eid, "IstwertOnChange");
			IPS_SetEventTrigger($eid, 1, $this->ReadPropertyInteger("IstWert"));
			IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//SetValueScript erstellen
		if(@IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID) === false)
		{
			$sid = IPS_CreateScript(0 /* PHP Script */);
			IPS_SetParent($sid, $this->InstanceID);
			IPS_SetName($sid, "SetValue");
			IPS_SetIdent($sid, "SetValueScript");
			IPS_SetHidden($sid, true);	
			IPS_SetScriptContent($sid, "<?

if (\$IPS_SENDER == \"WebFront\") 
{ 
    SetValue(\$_IPS['VARIABLE'], \$_IPS['VALUE']); 
} 

?>");
		}
		
		//Targets Kategorie erstellen
		if(@IPS_GetObjectIDByIdent("TargetsCat", $this->InstanceID) === false)
		{
			$cid = IPS_CreateCategory();
			IPS_SetParent($cid, $this->InstanceID);
			IPS_SetName($cid, "Targets");
			IPS_SetIdent($cid, "TargetsCat");
		}
		
		//°C Profil erstellen
		if(!IPS_VariableProfileExists("PWM.Celsius"))
		{
			$this->CreateProfile("PWM.Celsius", 2, 0, 40, 0.1, 1, "", "°C");
		}
		
		//Min. Profil erstellen
		if(!IPS_VariableProfileExists("PWM.Minutes"))
		{
			$this->CreateProfile("PWM.Minutes", 2, 0, 40, 0.1, 1, "", " Min.");
		}
		
		//Selector Profil erstellen
		if(!IPS_VariableProfileExists("PWM.Selector"))
		{
			$this->CreateProfile("PWM.Selector", 1, 0, 4, 1, 0);
			IPS_SetVariableProfileAssociation("PWM.Selector", 0, "Standard", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 1, "Komfort", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 2, "Reduziert", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 3, "Solar/PV", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 4, "Urlaub", "", -1);
		}
		
		//Trigger Variable erstellen
		if(@IPS_GetObjectIDByIdent("TriggerVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Trigger","TriggerVar","PWM.Celsius",$sid);
		}
		else
		{
			$vid = IPS_GetObjectIDByIdent("TriggerVar",$this->InstanceID);
		}

		//Trigger Variable onChange Event
		if(@IPS_GetObjectIDByIdent("TriggerOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Trigger onChange");
			IPS_SetIdent($eid, "TriggerOnChange");
			IPS_SetEventTrigger($eid, 1, $vid);
			IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//Interval Variable erstellen
		if(@IPS_GetObjectIDByIdent("IntervalVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Interval","IntervalVar","PWM.Minutes",$sid);
			SetValue($vid,10);
		}
		else
		{
			$vid = IPS_GetObjectIDByIdent("IntervalVar",$this->InstanceID);
		}
		
		//Interval onChange Event
		if(@IPS_GetObjectIDByIdent("IntervalOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Interval onChange");
			IPS_SetIdent($eid, "IntervalOnChange");
			IPS_SetEventTrigger($eid, 1, $vid);
			IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//Minimale Öffnungszeit Variable erstellen
		if(@IPS_GetObjectIDByIdent("OeffnungszeitVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Minimale Öffnungszeit", "OeffnungszeitVar", "PWM.Minutes", $sid);
			SetValue($vid,1);
		}
		
		//Soll-Wert Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Standard", "SollwertVar", "PWM.Celsius", $sid);
		}
		else
		{
			$vid = IPS_GetObjectIDByIdent("SollwertVar",$this->InstanceID);
		}
		
		//Soll-Wert onChange Event
		if(@IPS_GetObjectIDByIdent("SollwertOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Sollwert onChange");
			IPS_SetIdent($eid, "SollwertOnChange");
			IPS_SetEventTrigger($eid, 1, $vid);
			IPS_SetEventScript($eid, "PWM_sollwertRefresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//Selector für die Soll-Werte erstellen
		if(@IPS_GetObjectIDByIdent("SelectorVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(1,"Sollwert", "SelectorVar", "PWM.Selector", $sid);
		}
		
		//Selector onChange
		if(@IPS_GetObjectIDByIdent("SelectorOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Selector onChange");
			IPS_SetIdent($eid, "SelectorOnChange");
			IPS_SetEventTrigger($eid, 1, $vid);
			IPS_SetEventScript($eid, "PWM_selectSollwert(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//Sollwerte Data Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertData", $this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(3,"Sollwerte.Data", "SollwertData", "", $sid);
			IPS_SetHidden($vid,true);
			
			$data[0] = array("value" => 0, "name" => "Standard");
			$data[1] = array("value" => 0, "name" => "Komfort");
			$data[2] = array("value" => 0, "name" => "Reduziert");
			$data[3] = array("value" => 0, "name" => "Solar/PV");
			$data[4] = array("value" => 0, "name" => "Urlaub");
			$d = json_encode($data);
			SetValue($vid, $d);
		}
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
		//Ist-Wert onChange Event
		if(@IPS_GetObjectIDByIdent("IstwertOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Istwert onChange");
			IPS_SetIdent($eid, "IstwertOnChange");
			IPS_SetEventTrigger($eid, 1, $this->ReadPropertyInteger("IstWert"));
			IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		else
		{
			$eid = IPS_GetObjectIDByIdent("IstwertOnChange", $this->InstanceID);
			IPS_SetEventTrigger($eid, 1, $this->ReadPropertyInteger("IstWert"));
		}
		
		$this->refresh();
	}
	
	private function setValueHeating($value)
	{
		$targets = IPS_GetObjectIDByIdent("TargetsCat", $this->InstanceID);
		foreach(IPS_GetChildrenIDs($targets) as $target) 
		{
			/*only allow links*/
			if(IPS_LinkExists($target)) 
			{
				$linkVariableID = IPS_GetLink($target)['TargetID'];
				if(IPS_VariableExists($linkVariableID)) 
				{
					$type = IPS_GetVariable($linkVariableID)['VariableType'];
					$id = $linkVariableID;
					
					$o = IPS_GetObject($id);
					$v = IPS_GetVariable($id);
					
					if($v['VariableType'] == 0)
					{
						$value = (bool) $value;
					}
					
					if($v["VariableCustomAction"] > 0)
						$actionID = $v["VariableCustomAction"];
					else
						$actionID = $v["VariableAction"];
					
					/*Skip this device if we do not have a proper id*/
						if($actionID < 10000)
						{
							SetValue($id,$value);
							continue;
						}
					if(IPS_InstanceExists($actionID)) 
					{
						IPS_RequestAction($actionID, $o["ObjectIdent"], $value);
					}
					else if(IPS_ScriptExists($actionID))
					{
						echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $id, "VALUE" => $value, "SENDER" => "WebFront"));
					}
				}
			}
		}
	}
	
	////////////////////
	//public functions//
	////////////////////
	public function sollwertRefresh()
	{
		$selectID = IPS_GetObjectIDByIdent("SelectorVar",$this->InstanceID);
		$sollID = IPS_GetObjectIDByIdent("SollwertVar",$this->InstanceID);
		$dataID = IPS_GetObjectIDByIdent("SollwertData", $this->InstanceID);
		$selectValue = GetValue($selectID);
		$o = IPS_GetObject($sollID);
		$data = (array) json_decode(GetValue($dataID));
		$data[$selectValue] = array("value" => GetValue($sollID), "name" => $o['ObjectName']);
		$data = json_encode($data);
		SetValue($dataID, $data);
		$this->refresh();
	}
	
	public function refresh()
	{
		$var['istwert'] = $this->ReadPropertyInteger("IstWert");
		if($var['istwert'] >= 10000)
		{	
			$var['istwert'] = $this->ReadPropertyInteger("IstWert");
			$var['sollwert'] = IPS_GetObjectIDByIdent("SollwertVar", $this->InstanceID);
			$var['trigger'] = IPS_GetObjectIDByIdent("TriggerVar", $this->InstanceID);
			$var['interval'] = IPS_GetObjectIDByIdent("IntervalVar", $this->InstanceID);
			$var['oeffnungszeit'] = IPS_GetObjectIDByIdent("OeffnungszeitVar", $this->InstanceID);
			foreach($var as $i => $v)
			{
				$var[$i] = GetValue($v);
			}
			if($var['trigger'] == 0)
				$var['trigger'] = 0.1;
			
			$eName = "Nächste Aktuallisierung";
			$eIdent = "refreshTimer";
			$eScript = "PWM_refresh(". $this->InstanceID .");";
			$eid = $this->CreateTimer($eName, $eIdent, $eScript);
			IPS_SetEventCyclicTimeFrom($eid, (int)date("H"), (int)date("i"), (int)date("s"));
			IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 0, 1 /* Sekündlich */, $var['interval'] * 60);
			IPS_SetEventActive($eid, true);
			IPS_SetHidden($eid, false);
			
			$temperaturDifferenz = $var['sollwert'] - $var['istwert'];
			$oeffnungszeit_prozent = (100 / $var['trigger']) * $temperaturDifferenz;
			$oeffnungszeit = ($oeffnungszeit_prozent * $var['interval']) * 0.01; //Öffnungszeit in Minuten
			
			if($oeffnungszeit <= $var['oeffnungszeit'])
			{
				$this->setValueHeating(false);
				//"Heizung Stellmotor zu!";
			}
			else
			{
				$this->setValueHeating(true);
				
				$eName = "Heizung aus";
				$eIdent = "heatingOffTimer";
				$eScript = "PWM_heatingOff(". $this->InstanceID .");";
				$eid = $this->CreateTimer($eName, $eIdent, $eScript);
				IPS_SetEventCyclicTimeFrom($eid, (int)date("H"), (int)date("i"), (int)date("s"));
				IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 0, 1 /* Sekündlich */, $oeffnungszeit * 60);
				IPS_SetEventActive($eid, true);
				IPS_SetHidden($eid, false);
				
				//"Heizung Stellmotor auf für $oeffnungszeit Minuten";
			}
		}
	}
	
	public function selectSollwert()
	{
		$selectID = IPS_GetObjectIDByIdent("SelectorVar",$this->InstanceID);
		$sollID = IPS_GetObjectIDByIdent("SollwertVar",$this->InstanceID);
		$dataID = IPS_GetObjectIDByIdent("SollwertData", $this->InstanceID);
		$selectValue = GetValue($selectID);
		$selectProfile = IPS_GetVariableProfile(IPS_GetVariable($selectID)['VariableCustomProfile']);
		$data = (array) json_decode(GetValue($dataID));
		SetValue($sollID, $data[$selectValue]->value);
		IPS_SetName($sollID, $data[$selectValue]->name);
	}
	
	public function heatingOff()
	{
		$this->setValueHeating(false); //stellmotor aus
		if(@IPS_GetObjectIDByIdent("heatingOffTimer", $this->InstanceID) !== false)
		{
			$eid = IPS_GetObjectIDByIdent("heatingOffTimer", $this->InstanceID);
			IPS_DeleteEvent($eid);
		}
	}
}
?>