<?
class PWM extends IPSModule {

	public function Create() {
		//Never delete this line!
		parent::Create();

		if(@$this->RegisterPropertyInteger("Stellmotor") !== false)
		{
			$this-RegisterPropertyInteger("Stellmotor",0);
		}
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
	}
	
	////////////////////
	//public functions//
	////////////////////
}
?>