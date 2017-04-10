<?
class SchwellwertTimer extends IPSModule {
 
    public function Create() 
	{
		// Diese Zeile nicht löschen.
		parent::Create();
		
		$this->RegisterPropertyInteger("Unit", 4);
		$this->RegisterPropertyInteger("Sensor", 0);
		$this->RegisterPropertyString("valueOff", "0");
		$this->RegisterPropertyString("valueOn", "1");
		
		//Custom Unit Einstellungsgrößen
		$this->RegisterPropertyInteger("Type", 1);
		$this->RegisterPropertyString("prefix", "");
		$this->RegisterPropertyString("suffix", "");
		$this->RegisterPropertyString("min", "0");
		$this->RegisterPropertyString("max", "10");
		$this->RegisterPropertyString("steps", "1");

		//SetValueScript erstellen
		if(@IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID) === false)
		{
			$vid = IPS_CreateScript(0 /* PHP Script */);
			IPS_SetParent($vid, $this->InstanceID);
			IPS_SetName($vid, "SetValue");
			IPS_SetIdent($vid, "SetValueScript");
			IPS_SetHidden($vid, true);	
			IPS_SetScriptContent($vid, "<?

if (\$IPS_SENDER == \"WebFront\") 
{ 
    SetValue(\$_IPS['VARIABLE'], \$_IPS['VALUE']); 
} 

?>");
		}
		
		//Status Variable erstellen
		if(@IPS_GetObjectIDByIdent("StatusVariable", $this->InstanceID) === false)
		{
			$svid = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);
			$vid = IPS_CreateVariable(0 /* Boolean */);
			IPS_SetParent($vid, $this->InstanceID);
			IPS_SetName($vid, "Status");
			IPS_SetIdent($vid, "StatusVariable");
			if(IPS_VariableProfileExists("~Switch"))
			{
				IPS_SetVariableCustomProfile($vid,"~Switch");
			}
			else
			{
				IPS_CreateVariableProfile("~Switch",0);
				IPS_SetVariableProfileValues("~Switch",0,1,1);
				IPS_SetVariableProfileAssociation("~Switch",0,"Aus","",-1);
				IPS_SetVariableProfileAssociation("~Switch",1,"An","",0x00FF00);
				IPS_SetVariableProfileIcon("~Switch","Power");
				
				IPS_SetVariableCustomProfile($vid,"~Switch");
			}
			IPS_SetVariableCustomAction($vid,$svid);
		}
		
		//Nachlaufzeit Variable erstellen
		if(@IPS_GetObjectIDByIdent("Nachlaufzeit",$this->InstanceID) === false)
		{
			$svid = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);
			$vid = IPS_CreateVariable(1 /* Integer */);
			IPS_SetParent($vid, $this->InstanceID);
			IPS_SetName($vid, "Nachlaufzeit");
			IPS_SetIdent($vid, "Nachlaufzeit");
			if(IPS_VariableProfileExists("SZS.Minutes"))
			{
				IPS_SetVariableCustomProfile($vid,"SZS.Minutes");
			}
			else
			{
				IPS_CreateVariableProfile("SZS.Minutes", 1);
				IPS_SetVariableProfileValues("SZS.Minutes", 0, 120, 1);
				IPS_SetVariableProfileText("SZS.Minutes",""," Min.");
				//IPS_SetVariableProfileIcon("SZS.Minutes", "");
				
				IPS_SetVariableCustomProfile($vid,"SZS.Minutes");
			}
			IPS_SetVariableCustomAction($vid,$svid);
		}
		
		//Targets Kategorie erstellen
		$this->CreateCategoryByIdent($this->InstanceID, "Targets", "Targets");
    }
	
	//Schwellwert Variable erstellen
	private function CreateLimitVariable($type) 
	{
		if(@IPS_GetObjectIDByIdent("limit", $this->InstanceID) === false)
		{
			$svid = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);
			$vid = IPS_CreateVariable($type);
			IPS_SetParent($vid, $this->InstanceID);
			IPS_SetName($vid, "Schwellwert");
			IPS_SetIdent($vid, "limit");
			IPS_SetVariableCustomAction($vid, $svid);
			return $vid;
		}
		else
		{
			$vid = IPS_GetObjectIDByIdent("limit", $this->InstanceID);
			if(IPS_GetVariable($vid)['VariableType'] != $type)
			{
				IPS_DeleteVariable($vid);
				$vid = $this->CreateLimitVariable($type);
				return $vid;
			}
			return $vid;
		}
	}
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
		{
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
			
			///////////////////
			// Profilbereich //
			///////////////////
			switch($this->ReadPropertyInteger("Unit"))
			{
				case(1 /*°C*/):
					$vid = $this->CreateLimitVariable(1 /* integer */);
				
					if(!IPS_VariableProfileExists("SWT.DegreeCelsius"))
					{
					IPS_CreateVariableProfile("SWT.DegreeCelsius", 1);
					IPS_SetVariableProfileValues("SWT.DegreeCelsius", 0, 40, 1);
					IPS_SetVariableProfileText("SWT.DegreeCelsius", "", "°C");
					//IPS_SetVariableProfileIcon("SWT.DegreeCelsius", "");
					}
					IPS_SetVariableCustomProfile($vid, "SWT.DegreeCelsius");
					break;
				case(2 /*°F*/):
					$vid = $this->CreateLimitVariable(1 /* integer */);
				
					if(!IPS_VariableProfileExists("SWT.DegreeFahrenheit"))
					{
					IPS_CreateVariableProfile("SWT.DegreeFahrenheit", 1);
					IPS_SetVariableProfileValues("SWT.DegreeFahrenheit", 0, 105, 1);
					IPS_SetVariableProfileText("SWT.DegreeFahrenheit", "", "°F");
					//IPS_SetVariableProfileIcon("SWT.DegreeFahrenheit", "");
					}
					IPS_SetVariableCustomProfile($vid, "SWT.DegreeFahrenheit");
					break;
				case(3 /*Lux*/):
					$vid = $this->CreateLimitVariable(1 /* integer */);
				
					if(!IPS_VariableProfileExists("SWT.Lux"))
					{
					IPS_CreateVariableProfile("SWT.Lux", 1);
					IPS_SetVariableProfileValues("SWT.Lux", 0, 80000, 1000);
					IPS_SetVariableProfileText("SWT.Lux", "", "lx");
					//IPS_SetVariableProfileIcon("SWT.Lux", "");
					}
					IPS_SetVariableCustomProfile($vid, "SWT.Lux");
					break;
				case(4):
					$sensorID = $this->ReadPropertyInteger("Sensor");
					// Uberprüft die validität der Variable
					if($sensorID >= 10000)
					{
						$systemProfile = IPS_GetVariable($sensorID)['VariableProfile'];
						$customProfile = IPS_GetVariable($sensorID)['VariableCustomProfile'];
						if($customProfile != "")
						{
							$type = IPS_GetVariable($sensorID)['VariableType'];
							$vid = $this->CreateLimitVariable($type);
							IPS_SetVariableCustomProfile($vid, $customProfile);
						}
						else if($systemProfile != "")
						{
							$type = IPS_GetVariable($sensorID)['VariableType'];
							$vid = $this->CreateLimitVariable($type);
							IPS_SetVariableCustomProfile($vid, $systemProfile);
						}
						else
						{
							try
							{
								$error = "\nInvalid Variable Profile\n";
								if(gettype($customProfile) != "string")
								{
									$error .= 'Types detected: ' . gettype($customProfile);
									$error .= ', ' . gettype($systemProfile) . '\n';
									$error .= 'Type expected: "string"';
								}
								else
								{
									$error .= "→ Profile is empty";
								}
								throw new Exception($error);
							}
							catch (Exception $e) 
							{
								echo 'Caught exception: ',  $e->getMessage(), "\n";
							}
						}
					}
					break;
				case(0 /*Custom*/):
					$type = $this->ReadPropertyInteger("Type");
					$vid = $this->CreateLimitVariable($type);
					
					if(!IPS_VariableProfileExists("SWT.Custom"))
					{
						IPS_CreateVariableProfile("SWT.Custom", $type);
					}
					else if(IPS_GetVariableProfile("SWT.Custom")['ProfileType'] != $type)
					{
						IPS_DeleteVariableProfile("SWT.Custom");
						IPS_CreateVariableProfile("SWT.Custom", $type);
					}
					
					$p = $this->ReadPropertyString("prefix");
					$s = $this->ReadPropertyString("suffix");
					$min = $this->ReadPropertyString("min");
					$max = $this->ReadPropertyString("max");
					$steps = $this->ReadPropertyString("steps");
					IPS_SetVariableProfileValues("SWT.Custom", $min, $max, $steps);
					IPS_SetVariableProfileText("SWT.Custom", $p, $s);
					//IPS_SetVariableProfileIcon("SWT.Custom", "");
					
					IPS_SetVariableCustomProfile($vid, "SWT.Custom");
					break;
				default:
					$unit = $this->ReadPropertyInteger("Unit");
					try
					{
						
						$error = "\nInvalid Unit Index: $unit\n";
						$error .= "0: Custom\n";
						$error .= "1: Degree (°C)\n";
						$error .= "2: Degree (°F)\n";
						$error .= "3: Lux (lx)\n";
						$error .= "4: Same as Sensor";
						throw new Exception($error);
						
					}
					catch (Exception $e) 
					{
						echo 'Caught exception: ',  $e->getMessage(), "\n";
					}
			}
			//////////////////
			// Logikbereich //
			//////////////////
			
			$tid = $this->RegisterTimer("Update", 300000 /*alle 5 Minuten*/, "SWT_refreshStatus(0);");
        }
 
		/**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
 
        public function refreshStatus() 
		{
            $sid = $this->ReadPropertyInteger("Sensor");
			$lid = IPS_GetObjectIDByIdent("limit", $this->InstanceID);
			$statusID = IPS_GetObjectIDByIdent("Status", $this->InstanceID);
			$ntID = IPS_GetObjectIDByIdent("Nachlaufzeit", $this->InstanceID);
			
			$sensor = GetValue($sid);	$limit = GetValue($lid);	$nachlaufzeit = GetValue($ntID);
			if($limit < $sensor) //Above limit
			{
				SetValue($statusID,1);	
				$tid = $this->RegisterTimer("Nachlaufzeit", $nachlaufzeit*60000 /*Minuten zu Millisekunden*/, "SWT_nachlaufzeitAbgelaufen(0)");
				$this->RegisterPropertyInteger("nachlauftimer", $tid);
			}
			else //Below limit
			{
				SetValue($statusID,0);
			}
        }
		
		public function nachlaufzeitAbgelaufen()
		{
			$tid = $this->ReadPropertyInteger("nachlauftimer");
			IPS_SetEventActive($tid,false);
		}
		
		public function statusOnChange($status)
		{
			$targets = IPS_GetObjectIDByIdent("Targets");
			if($status == "On")
			{
				$value = $this->ReadPropertyInteger("valueOn");
			}
			else
			{
				$value = $this->ReadPropertyInteger("valueOff");
			}
			
			foreach(IPS_GetChildrenIDs($targets) as $target) 
			{
				//only allow links
				if(IPS_LinkExists($TargetID)) 
				{
					$linkVariableID = IPS_GetLink($TargetID)['TargetID'];
					if(IPS_VariableExists($linkVariableID)) 
					{
						$type = IPS_GetVariable($linkVariableID)['VariableType'];
						$id = $linkVariableID;
						
						$o = IPS_GetObject($id);
						$v = IPS_GetVariable($id);
						
						if($v["VariableCustomAction"] > 0)
							$actionID = $v["VariableCustomAction"];
						else
							$actionID = $v["VariableAction"];
						
						//Skip this device if we do not have a proper id
							if($actionID < 10000)
								continue;
							
						if(IPS_InstanceExists($actionID)) 
						{
							IPS_RequestAction($actionID, $o["ObjectIdent"], $value);
						}
						else if(IPS_ScriptExists($actionID))
						{
							echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $id, "VALUE" => $value));
						}
					}
				}
			}
		}
		
		private function CreateCategoryByIdent($id, $ident, $name) 
		{
			$cid = @IPS_GetObjectIDByIdent($ident, $id);
			if($cid === false) {
				$cid = IPS_CreateCategory();
				IPS_SetParent($cid, $id);
				IPS_SetName($cid, $name);
				IPS_SetIdent($cid, $ident);
			}
			return $cid;
		}
    }
?>