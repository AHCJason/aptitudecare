<?php

class SchedulesController extends AdmissionController {


	public function dischargePatient() {
		if (input()->id != "") {
			$patient = $this->loadModel('Patient', input()->id);
			$schedule = $this->loadModel('Schedule');
			if ($schedule->discharge($patient->id)) {
				return true;
			}
			
		}

		return false;
	}
	
	//AJAX END POINT FOR ROOM CHANGE!
	public function movePatientRooms() {
		
		echo $this->getLocation()->id;
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		
		if (input()->id != "") {
			$patient = $this->loadModel('Patient', input()->id);
			$schedule = $this->loadModel('Schedule');
			if ($schedule->move($this->getLocation()->id, $patient->id, input()->oldroom, input()->newroom)) {
				return true;
			}
		}
		return false;
		//$data = "HELLO WORLD!"; //print_r($currentPatients, true);
		
		//$this->template  = 'pdf2';
		//smarty()->assign('data', $data);
	}
}

?>