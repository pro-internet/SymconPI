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
		echo $parent;
		echo $ident;
		if(@IPS_GetObjectIDByIdent($ident, $parent) === false)
		{
			$eid = IPS_CreateEvent(1 /*züklisch*/);
			IPS_SetName($eid, $name);
			IPS_SetParent($eid, $parent);
			echo "\n" . $eid . "\n";
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
		}
		
		//Soll-Wert Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Soll-Wert", "SollwertVar", "PWM.Celsius", $sid);
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
			IPS_SetEventScript($eid, "PWM_refresh(". $this->InstanceID .");");
			IPS_SetEventActive($eid, true);
		}
		
		//Soll-Wert Komfort Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertKomfortVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Komfort-Wert", "SollwertKomfortVar", "PWM.Celsius", $sid);
		}
		
		//Soll-Wert Reduziert Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertReduziertVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Reduziert-Wert", "SollwertReduziertVar", "PWM.Celsius", $sid);
		}
		
		//Soll-Wert Solar Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertSolarVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Solar-Wert", "SollwertSolarVar", "PWM.Celsius", $sid);
		}
		
		//Soll-Wert Urlaub Variable erstellen
		if(@IPS_GetObjectIDByIdent("SollwertUrlaubVar",$this->InstanceID) === false)
		{
			$vid = $this->CreateVariable(2,"Urlaub-Wert", "SollwertUrlaubVar", "PWM.Celsius", $sid);
		}
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		$this->refresh();
	}
	
	private function setValueHeating($value)
	{
		$vid = $this->ReadPropertyInteger("Stellmotor");
		$v = IPS_GetVariable($vid);
		$o = IPS_GetObject($vid);
		
		if($v["VariableCustomAction"] > 0)
			$actionID = $v["VariableCustomAction"];
		else
			$actionID = $v["VariableAction"];
		
		if($actionID < 10000)
		{
			SetValue($vid, $value);
		}
		else
		{
			if(IPS_InstanceExists($actionID)) 
			{
				IPS_RequestAction($actionID, $o["ObjectIdent"], $value);
			}
			else if(IPS_ScriptExists($actionID))
			{
				echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $vid, "VALUE" => $value, "SENDER" => "WebFront"));
			}
		}
	}
	
	////////////////////
	//public functions//
	////////////////////
	
	public function refresh()
	{
		$stellmotorID = $this->ReadPropertyInteger("Stellmotor");
		if($stellmotorID >= 10000)
		{	
			$var['istwert'] = $this->ReadPropertyInteger("IstWert");
			$var['sollwert'] = IPS_GetObjectIDByIdent("SollwertVar", $this->InstanceID);
			$var['trigger'] = IPS_GetObjectIDByIdent("TriggerVar", $this->InstanceID);
			$var['interval'] = IPS_GetObjectIDByIdent("IntervalVar", $this->InstanceID);
			$var['oeffnungszeit'] = IPS_GetObjectIDByIdent("OeffnungszeitVar", $this->InstanceID);
			foreach($var as $i => $v)
			{
				echo $v . "\n";
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
				echo "Heizung Stellmotor zu!";
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
				
				echo "Heizung Stellmotor auf für $oeffnungszeit Minuten";
			}
		}
	}
	
	public function heatingOff()
	{
		$this->setValueHeating(false); //stellmotor aus
		if(@IPS_GetObjectIDByIdent("TurnOffHeatingTimer", $this->InstanceID) !== false)
		{
			$eid = IPS_GetObjectIDByIdent("TurnOffHeatingTimer", $this->InstanceID);
			IPS_DeleteEvent($eid);
		}
	}
}
?>