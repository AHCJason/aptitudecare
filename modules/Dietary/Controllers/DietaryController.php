<?php


class DietaryController extends MainPageController {

	// protected $template = "dietary";
	public $module = "Dietary";
	protected $navigation = 'dietary';
	protected $searchBar = 'dietary';
	protected $helper = 'DietaryMenu';


	public function index() {
		smarty()->assign("title", "Dietary");
		// if user is not authorized to access this page, then re-direct
		if (!auth()->getRecord()) {
			$this->redirect();
		}

		// get the location
		$location = $this->getLocation();

		// check if the user has permission to access this module
		if ($location->location_type != 1) {
			$this->redirect();
		}

		// check if the location is has the admission dashboard enabled
		$modEnabled = ModuleEnabled::isAdmissionsEnabled($location->id);

		// if the facility is using the admission dashboard, then get a list of
		// the current patients from the admission app for the current location.

		// NOTE: if a location is using the admission dashboard they should
		// not have the ability to add or delete patients through the dietary
		// app interface.
		
		if ($modEnabled) {
			$rooms = $this->loadModel("Room")->fetchEmpty($location->id);
			// until the admission app is re-built and we move to a single database we need to fetch
			// the data from the admission db and save to the master db
			// IMPORTANT: Remove this after admission app is re-built in new framework!!!
			$scheduled = $this->loadModel('AdmissionDashboard')->syncCurrentPatients($location->id);
			$currentPatients = $this->loadModel("Room")->mergeRooms($rooms, $scheduled);
		} else {
			// if the locations is not using the admission dashboard then load the patients
			// from ac_patient and dietary_patient_info tables
			// fetch current patients
			//$scheduled = $this->loadModel("Patient")->fetchPatients($location->id); //this one didn't cross refrence the dietary_patient_info and thus the sync home health patients showed up.
			$currentPatients = $this->loadModel("Patient")->fetchDietaryPatients($location->id);
		}
		/*
		$file_out = "";
		foreach($currentPatients as $sc)
		{
			$file_out .= $sc->public_id . ", " . $sc->id . ", " . $sc->last_name . ", " . $sc->first_name . ", " . $sc->patient_admit_id . ", " . $sc->location_id . ", " . $sc->number . "\n";
		}
		
		file_put_contents("/tmp/lastCurrentPateints", $file_out, LOCK_EX);
		*/

		smarty()->assign('currentPatients', $currentPatients);
		smarty()->assign('modEnabled', $modEnabled);
	}
	
	public function triplecheck()
	{
		if (!auth()->getRecord()) {
			$this->redirect();
		}
		
		$start = microtime(true);
		
		$location = $this->getLocation();
		
		//list of patients on Index
		$currentPatients = $this->loadModel("Patient")->fetchDietaryPatients($location->id);
		
		//tray card info with snacks
		@$tray_card_info = $this->loadModel('PatientInfo')->fetchTrayCardInfo("all", $location->id, true);
		
		//Get Patient Census Report
		$diet_census = $this->loadModel('PatientDietOrder')->fetchPatientCensus($location->id, "public_id");
		
		//get allergies
		$allergies = $this->loadModel('PatientInfo')->fetchByLocation_allergy($location);
		$dislikes = $this->loadModel('PatientInfo')->fetchByLocation_dislikes($location);
		
		// get beverages
		// not useful in current form, need to create second query to be patientID, pt pub_id, beverage_id
		//$beverages = $this->loadModel("Beverage")->fetchBeverageReport($location, $date);

		// get diet census report

		
		
		
		echo "<pre>";
		
		echo "\nDB took: " . (microtime(true) - $start) . "\n";
		$start = microtime(true);
		

		$currentPatientsbyPub = array();
		$tray_card_info_byPub = array();
		$diet_census_byPub = array();
		
		//Loop through index list and verify every patient is traycard.
		//Report on Empty Rooms.
		//Save PTs to new array with pubid as key.
		foreach($currentPatients as $id => &$pt)
		{
			if($pt->patient_admit_id == null)
			{
				echo "{$pt->number} is empty on index list.\n";
				unset($currentPatients[$id]);
			} else {
				if(isset($currentPatientsbyPub[$pt->public_id]))
				{
					echo "ERROR! Duplicate Patient! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
				} else {
					$currentPatientsbyPub[$pt->public_id] = &$pt;
				}
				/*
				echo $pt->public_id;
				echo ":";
				echo $pt->last_name;
				echo "\n";*/
			}
		}
	//TRAY CARDS
		//loop over tray cards and get if they have one or erronously have one.
		foreach($tray_card_info as $tid => &$traycard)
		{
			//We are already here, build traycard report for checking
			if(isset($tray_card_info_byPub[$traycard->public_id]))
			{
				echo "ERROR! Duplicate tray card for patient! {$traycard->public_id} {$traycard->last_name} {$traycard->first_name}\n";
			} else {
				$tray_card_info_byPub[$traycard->public_id] = &$traycard;
			}
			if(isset($currentPatientsbyPub[$traycard->public_id]))
			{
				$currentPatientsbyPub[$traycard->public_id]->hasTrayCard = true;
				//echo "{$traycard->public_id} {$traycard->last_name} {$traycard->first_name} has traycard \n";
			} else {
				echo "ERROR! {$traycard->public_id} {$traycard->last_name} {$traycard->first_name} has traycard, but not on list \n";
			}
		}
		
		//check if we don't have a traycard for a patient
		foreach($currentPatientsbyPub as $id => &$pt)
		{
			if(!isset($pt->hasTrayCard))
			{
				echo "ERROR! PT Doesn't have traycard! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
			}
		}
		
	//DIET CENSUS Report
		foreach($diet_census as $id => &$pt)
		{
			//We are already here, build dietary_census report for checking with pubID
			if(isset($diet_census_byPub[$pt->public_id]))
			{
				echo "ERROR! Duplicate tray card for patient! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
			} else {
				$diet_census_byPub[$pt->public_id] = &$pt;
			}
			
			if(isset($currentPatientsbyPub[$pt->public_id]))
			{
				$currentPatientsbyPub[$pt->public_id]->hasCensusReport = true;
			} else {
				echo "ERROR! {$pt->public_id} {$pt->last_name} {$pt->first_name} has census entry, but not on list \n";
			}
		}
		
		//check if we don't have a traycard for a patient
		foreach($currentPatientsbyPub as $id => &$pt)
		{
			if(!isset($pt->hasCensusReport))
			{
				echo "ERROR! PT isn't on the census report! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
			}
		}
		
	//Allergies Report
		foreach($allergies as $id => &$pt)
		{
			if(isset($currentPatientsbyPub[$pt->public_id]))
			{
				$currentPatientsbyPub[$pt->public_id]->hasAllergy = true;
			} else {
				echo "ERROR! On Allergy Report, but in index!\n";
			}
			
			if(isset($tray_card_info_byPub[$pt->public_id]))
			{
				$tray_card_info_byPub[$pt->public_id]->hasAllergy = true;
				if($tray_card_info_byPub[$pt->public_id]->allergies != $pt->allergy_name || $diet_census_byPub[$pt->public_id]->allergies != $pt->allergy_name)
				{
					$tc_allergy = explode(",", trim($tray_card_info_byPub[$pt->public_id]->allergies));
					$rp_allergy = explode(",", trim($pt->allergy_name));
					$dc_allergy = explode(",", trim($diet_census_byPub[$pt->public_id]->allergies));
					
					sort($tc_allergy);
					sort($rp_allergy);
					sort($dc_allergy);

					array_walk($tc_allergy, function (&$value) { $value = trim($value); });
					array_walk($rp_allergy, function (&$value) { $value = trim($value); });
					array_walk($dc_allergy, function (&$value) { $value = trim($value); });
					
					$count_tcVrp = count(array_diff($tc_allergy, $rp_allergy));
					$count_rpVtc = count(array_diff($rp_allergy, $tc_allergy));
					$count_tcVdc = count(array_diff($tc_allergy, $dc_allergy));
					
					$count_3     = count(array_diff($rp_allergy, $tc_allergy, $dc_allergy));
					
					echo "Diff the 3 $count_3\n";
					

					
					if($count_3 === 0)
					{
						echo "WARN! allergies aren't in the same order report vs traycard vs census! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
					} else {
						echo "ERROR! allergies aren't the SAME! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
					}
					echo "::Tray Card     \t{$tray_card_info_byPub[$pt->public_id]->allergies}\n::Allergy Report\t{$pt->allergy_name}\n::DietaryCensus \t{$diet_census_byPub[$pt->public_id]->allergies}\n";
				}
				
			} else {
				echo "ERROR! {$pt->public_id} {$pt->last_name} {$pt->first_name} has allergy entry, but not on traycard \n";
			}
		}
		
		//check if patient has traycard allergy and report allergy
		foreach($tray_card_info_byPub as $id => &$pt)
		{
			if(!isset($pt->hasAllergy) && $pt->allergies != "")
			{
				echo "ERROR! PT isn't on the allergy report {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
			}
		}
		
//Duplicate of Allergeis, just swapped the for each on dislikes
	//Dislikes Report
		foreach($dislikes as $id => &$pt)
		{
			if(isset($currentPatientsbyPub[$pt->public_id]))
			{
				$currentPatientsbyPub[$pt->public_id]->hasDislike = true;
			} else {
				echo "ERROR! On Allergy Report, but in index!\n";
			}
			
			if(isset($tray_card_info_byPub[$pt->public_id]))
			{
				$tray_card_info_byPub[$pt->public_id]->hasDislike = true;
				if($tray_card_info_byPub[$pt->public_id]->dislikes != $pt->dislike_name)// || $diet_census_byPub[$pt->public_id]->dislikes != $pt->dislike_name)
				{
					$tc_allergy = explode(",", trim($tray_card_info_byPub[$pt->public_id]->dislikes));
					$rp_allergy = explode(",", trim($pt->dislike_name));
					//$dc_allergy = explode(",", trim($diet_census_byPub[$pt->public_id]->dislikes));
					
					sort($tc_allergy);
					sort($rp_allergy);
					//sort($dc_allergy);

					array_walk($tc_allergy, function (&$value) { $value = trim($value); });
					array_walk($rp_allergy, function (&$value) { $value = trim($value); });
					//array_walk($dc_allergy, function (&$value) { $value = trim($value); });
					
					$count_tcVrp = count(array_diff($tc_allergy, $rp_allergy));
					$count_rpVtc = count(array_diff($rp_allergy, $tc_allergy));
					//$count_tcVdc = count(array_diff($tc_allergy, $dc_allergy));
					
					$count_3     = count(array_diff($rp_allergy, $tc_allergy));
					
					echo "Diff the 3 $count_3\n";
					

					
					if($count_3 === 0)
					{
						echo "WARN! dislikes aren't in the same order report vs traycard! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
					} else {
						echo "ERROR! dislikes aren't the SAME! {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
					}
					echo "::Tray Card     \t{$tray_card_info_byPub[$pt->public_id]->dislikes}\n::Allergy Report\t{$pt->dislike_name}\n";
				}
				
			} else {
				echo "ERROR! {$pt->public_id} {$pt->last_name} {$pt->first_name} has dislike entry, but not on traycard \n";
			}
		}
		
		//check if patient has traycard allergy and report allergy
		foreach($tray_card_info_byPub as $id => &$pt)
		{
			if(!isset($pt->hasDislike) && $pt->allergies != "")
			{
				echo "ERROR! PT isn't on the dislike report {$pt->public_id} {$pt->last_name} {$pt->first_name}\n";
			}
		}		
		
		echo "\n\nFor took: ". (microtime(true) - $start);

		
		echo "</pre>";
		
		$data = "";
		$data .= "Traycards with Snacks:\n" . print_r($tray_card_info_byPub, true);
		$data .= "Census:\n" . print_r($diet_census_byPub, true);
		$data .= "Allergies:\n" . print_r($allergies, true);
		$data .= "Dislikes:\n" . print_r($dislikes, true);
		//$data .= "Diet Census:\n" . print_r($diet_census_byPub, true);
		
		smarty()->assign("output", $data);
		//$this->template = "ajax";

	}


	public function syncAdmissions() {
		$location = $this->loadModel('Location', input()->location);
		$this->loadModel('AdmissionDashboard')->syncDBs($location->id);
	}



	public function normalizeMenuItems($menuItems) {
		$menuWeek = false;
		foreach ($menuItems as $key => $item) {

			if (isset ($item->date) && $item->date != "") {
				$menuItems[$key]->type = "MenuMod";
			} elseif (isset ($item->menu_item_id) && $item->menu_item_id != "") {
				$menuItems[$key]->type = "MenuChange";
			} else {
				$menuItems[$key]->type = "MenuItem";
			}

			// Get the current week
			$menuWeek = floor($item->day / 7);

			$menuItems[$key]->content = nl2br($item->content);

			// explode the tags
			if (strstr($item->content, "<p>")) {
				$menuItems[$key]->content = explode("<p>", $item->content);
				$menuItems[$key]->content = str_replace("</p>", "", $item->content);
			} else {
				$menuItems[$key]->content = explode("<br />", $item->content);
			}

			if (isset ($item->mod_content)) {
				// explode the tags
				if (strstr($item->mod_content, "<p>")) {
					$menuItems[$key]->mod_content = explode("<p>", $item->mod_content);
					$menuItems[$key]->mod_content = str_replace("</p>", "", $item->mod_content);
				} else {
					$menuItems[$key]->mod_content = explode("<br />", $item->mod_content);
				}
			}


		}

		smarty()->assign('count', 0);
		smarty()->assign('menuWeek', $menuWeek);
		smarty()->assignByRef('menuItems', $menuItems);
	}

}
