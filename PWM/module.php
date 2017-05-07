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
		if($actionID > 9999)
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

		if(@$this->RegisterPropertyString("Raeume") !== false)
		{
			$this->RegisterPropertyString("Raeume","");
		}
		
		IPS_SetIdent($this->InstanceID, "PWMMainInstance");
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
			$this->CreateProfile("PWM.Selector", 1, 0, 3, 1, 0);
			IPS_SetVariableProfileAssociation("PWM.Selector", 0, "Komfort", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 1, "Reduziert", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 2, "Solar/PV", "", -1);
			IPS_SetVariableProfileAssociation("PWM.Selector", 3, "Urlaub", "", -1);
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
		
		//Selector für die Soll-Werte erstellen
		if(@IPS_GetObjectIDByIdent("SelectorVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(1,"Selector", "SelectorVar", "PWM.Selector", $sid);
		}
		
		//Selector onChange
		if(@IPS_GetObjectIDByIdent("SelectorOnChange", $this->InstanceID) === false)
		{
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetName($eid, "Selector onChange");
			IPS_SetIdent($eid, "SelectorOnChange");
			IPS_SetEventTrigger($eid, 1, $vid);
			IPS_SetEventScript($eid, "PWM_selectorOnChange(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();

		$moduleList = IPS_GetModuleList();
		$dummyGUID = ""; //init
		foreach($moduleList as $l)
		{
			if(IPS_GetModule($l)['ModuleName'] == "Dummy Module")
			{
				$dummyGUID = $l;
				break;
			}
		}

		$sid = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);
		$data = json_decode($this->ReadPropertyString("Raeume"));
		if(@$data[0]->Stellmotor > 9999 && @$data[0]->Istwert > 9999)
		{
			//Räume (Dummy Module) erstellen
			foreach($data as $i => $list)
			{	
			echo "Raum$i";
				if(@IPS_GetObjectIDByIdent("Raum$i", IPS_GetParent($this->InstanceID)) === false)
				{
					$insID = IPS_CreateInstance($dummyGUID);
					IPS_SetName($insID, $list->Raumname);
					IPS_SetParent($insID, IPS_GetParent($this->InstanceID));
					IPS_SetPosition($insID, $i + 1);
					IPS_SetIdent($insID, "Raum$i");
				}
				else
				{
					$insID = IPS_GetObjectIDByIdent("Raum$i", IPS_GetParent($this->InstanceID));
					IPS_SetName($insID, $list->Raumname);
				}
				
				//Soll-Wert Variable erstellen
				if(@IPS_GetObjectIDByIdent("SollwertVar",$insID) === false)
				{
					$vid = $this->CreateVariable(2,"Soll", "SollwertVar", "PWM.Celsius", 0, $insID);
					IPS_SetPosition($vid, 1);
				}
				else
				{
					$vid = IPS_GetObjectIDByIdent("SollwertVar", $insID);
				}
				
				//Soll-Wert onChange Event
				if(@IPS_GetObjectIDByIdent("SollwertOnChange", $insID) === false)
				{
					$eid = IPS_CreateEvent(0);
					IPS_SetParent($eid, $insID);
					IPS_SetPosition($eid, 99);
					IPS_SetName($eid, "Sollwert onChange");
					IPS_SetIdent($eid, "SollwertOnChange");
					IPS_SetEventTrigger($eid, 1, $vid);
					IPS_SetEventScript($eid, "PWM_selectorOnChange(". $this->InstanceID .");");
					IPS_SetEventActive($eid, true);
				}
				
				//Ist-Wert Link erstellen
				if(@IPS_GetObjectIDByIdent("IstwertLink",$insID) === false)
				{
					$lid = IPS_CreateLink();
					IPS_SetLinkTargetID($lid, $list->Istwert);
					IPS_SetName($lid, IPS_GetName($list->Istwert));
					IPS_SetParent($lid, $insID);
					IPS_SetPosition($lid, 0);
					IPS_SetIdent($lid, "IstwertLink");
				}
				
				//Ist-Wert onChange Event
				if(@IPS_GetObjectIDByIdent("IstwertOnChange", $insID) === false)
				{
					$eid = IPS_CreateEvent(0);
					IPS_SetParent($eid, $insID);
					IPS_SetPosition($eid, 99);
					IPS_SetName($eid, "Istwert onChange");
					IPS_SetIdent($eid, "IstwertOnChange");
					IPS_SetEventTrigger($eid, 1, $list->Istwert);
					IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
					IPS_SetEventActive($eid, true);
				}
				
				//Stellmotor Link erstellen
				if(@IPS_GetObjectIDByIdent("StellmotorLink",$insID) === false)
				{
					$lid = IPS_CreateLink();
					IPS_SetLinkTargetID($lid, $list->Stellmotor);
					IPS_SetName($lid, "Stellmotor");
					IPS_SetParent($lid, $insID);
					IPS_SetPosition($lid, 98);
					IPS_SetIdent($lid, "StellmotorLink");
				}
				
				//Soll-Wert Komfort Variable erstellen
				if(@IPS_GetObjectIDByIdent("KomfortVar",$insID) === false)
				{
					$vid = $this->CreateVariable(2,"Komfort", "KomfortVar", "PWM.Celsius", $sid, $insID);
					IPS_SetPosition($vid, 2);
					SetValue($vid, 21);
				}
				
				//Soll-Wert Reduziert Variable erstellen
				if(@IPS_GetObjectIDByIdent("ReduziertVar",$insID) === false)
				{
					$vid = $this->CreateVariable(2,"Reduziert", "ReduziertVar", "PWM.Celsius", $sid, $insID);
					IPS_SetPosition($vid, 3);
					SetValue($vid, 21);
				}
				
				//Soll-Wert Urlaub Variable erstellen
				if(@IPS_GetObjectIDByIdent("UrlaubVar",$insID) === false)
				{
					$vid = $this->CreateVariable(2,"Urlaub", "UrlaubVar", "PWM.Celsius", $sid, $insID);
					IPS_SetPosition($vid, 4);
					SetValue($vid, 21);
				}
				
				//Soll-Wert Solar Variable erstellen
				if(@IPS_GetObjectIDByIdent("SolarVar",$insID) === false)
				{
					$vid = $this->CreateVariable(2,"Solar", "SolarVar", "PWM.Celsius", $sid, $insID);
					IPS_SetPosition($vid, 5);
					SetValue($vid, 21);
				}
			}
		}
	}
	
	private function setValueHeating($value, $target)
	{
		if(IPS_VariableExists($target)) 
		{
			$type = IPS_GetVariable($target)['VariableType'];
			$id = $target;
			
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
	
	////////////////////
	//public functions//
	////////////////////
	public function selectorOnChange()
	{
		$selectorID = IPS_GetObjectIDByIdent("SelectorVar", $this->InstanceID);
		switch(GetValue($selectorID))
		{
			case(0):
				$soll = "KomfortVar";
				break;
			case(1):
				$soll = "ReduziertVar";
				break;
			case(2):
				$soll = "SolarVar";
				break;
			case(3):
				$soll = "UrlaubVar";
				break;			
		}
		$dataCount = count(json_decode($this->ReadPropertyString("Raeume")));
		for($i = 0; $i < $dataCount; $i++)
		{
			$insID = IPS_GetObjectIDByIdent("Raum$i", IPS_GetParent($this->InstanceID));
			$sollID = IPS_GetObjectIDByIdent("SollwertVar", $insID);
			$sollSzene = IPS_GetObjectIDByIdent($soll, $insID);
			$newSollwert = GetValue($sollSzene);
			SetValue($sollID, $newSollwert);
			
			$eid = IPS_GetObjectIDByIdent("SollwertOnChange", $insID);
			IPS_SetEventTrigger($eid, 1, $sollSzene);
		}
		
		$this->refresh();
	}
	
	public function refresh()
	{
		$data = json_decode($this->ReadPropertyString("Raeume"));
		$var = array();
		$var['trigger'] = GetValue(IPS_GetObjectIDByIdent("TriggerVar", $this->InstanceID));
		$var['interval'] = GetValue(IPS_GetObjectIDByIdent("IntervalVar", $this->InstanceID));
		$var['oeffnungszeit'] = GetValue(IPS_GetObjectIDByIdent("OeffnungszeitVar", $this->InstanceID));
		if($var['trigger'] == 0)
				$var['trigger'] = 0.1;
		for($i = 0; $i < count($data); $i++)
		{
			$insID = IPS_GetObjectIDByIdent("Raum$i", IPS_GetParent($this->InstanceID));
			$var['istwert'] = GetValue($data[$i]->Istwert);
			$var['sollwert'] = GetValue(IPS_GetObjectIDByIdent("SollwertVar", $insID));
			
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
				$this->setValueHeating(false, $data[$i]->Stellmotor);
				"Heizung Stellmotor zu!";
			}
			else
			{
				$this->setValueHeating(true, $data[$i]->Stellmotor);
				
				$eName = "Heizung aus";
				$eIdent = "heatingOffTimer";
				$eScript = "PWM_heatingOff(". $this->InstanceID . "," . $data[$i]->Stellmotor .");";
				$eid = $this->CreateTimer($eName, $eIdent, $eScript, $insID);
				IPS_SetEventCyclicTimeFrom($eid, (int)date("H"), (int)date("i"), (int)date("s"));
				IPS_SetEventCyclic($eid, 0 /* Keine Datumsüberprüfung */, 0, 0, 0, 1 /* Sekündlich */, $oeffnungszeit * 60);
				IPS_SetEventActive($eid, true);
				IPS_SetHidden($eid, false);
				
				"Heizung Stellmotor auf für $oeffnungszeit Minuten";
			}
		}
	}
	
	public function heatingOff($target)
	{
		$this->setValueHeating(false, $target); //stellmotor aus
		if(@IPS_GetObjectIDByIdent("heatingOffTimer", $this->InstanceID) !== false)
		{
			$eid = IPS_GetObjectIDByIdent("heatingOffTimer", $this->InstanceID);
			IPS_DeleteEvent($eid);
		}
	}
}
?>