<?php

class Location extends AppData {
	
	protected $table = 'location';

	protected $hasMany = array(
		'LocationLinkState' => array(
			'table' => 'ac_location_link_state',
			'join_type' => 'LEFT',
			'inner_key' => 'id',
			'foreign_key' => 'location_id'
		)
	);

	/* //don't believe in use
	public function fetchDefaultLocation() {
		if (auth()->valid()) {
			$user = auth()->getRecord();
		} else {
			return false;
			exit;
		}	

		$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN ac_user on ac_user.default_location = {$this->tableName()}.id WHERE ac_user.id = :user_id";
		$params[":user_id"] = $user->id;

		return $this->fetchOne($sql, $params);

	}*/

	/* //don't believe in use
	public function fetchOtherLocations($module = null) {
		if (auth()->valid()) {
			$user = auth()->getRecord();
		} else {
			return false;
			exit;
		}	
		
		$user_location = $this->loadTable('UserLocation');

		$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN ac_user_location ON {$user_location->tableName()}.location_id = {$this->tableName()}.id";

		if ($module != '') {
			$sql .= " INNER JOIN ac_modules_enabled ON ac_modules_enabled.location_id = ac_location.id INNER JOIN ac_module ON ac_module.id = ac_modules_enabled.module_id";
			$params[":module_name"] = $module;
		}

		$sql .= " WHERE ac_user_location.user_id = :user_id";

		if ($module != '') {
			$sql .= " AND ac_module.name = :module_name";
		}

		$sql .= "  GROUP BY ac_location.id";

		
		$params[":user_id"] = $user->id;
		return $this->fetchAll($sql, $params);
	}
	*/

	public function fetchLinkedFacilities($location_id = false) {
		if (auth()->valid()) {
			$user = auth()->getRecord();
		} else {
			return false;
			exit;
		}		

		$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN home_health_facility_link ON home_health_facility_link.facility_id = {$this->tableName()}.id WHERE home_health_facility_link.home_health_id IN (SELECT {$this->tableName()}.id FROM {$this->tableName()} INNER JOIN ac_user_location ON ac_user_location.location_id = {$this->tableName()}.id WHERE ac_user_location.user_id = :user_id)";

		$sql .= " AND home_health_facility_link.home_health_id = :location_id";
		if ($location_id) {
			$params[":location_id"] = $location_id;
		} else {
			$params["location_id"] = $this->id;
		}

			
		$params[':user_id'] = $user->id;

		return $this->fetchAll($sql, $params);
	}


	public function fetchFacilitiesByHomeHealthId($id) {
		$sql = "select {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN home_health_facility_link ON home_health_facility_link.facility_id = {$this->tableName()}.id WHERE home_health_facility_link.home_health_id = :id";
		$params[":id"] = $id;
		return $this->fetchAll($sql, $params);
	}



	public function fetchHomeHealthLocation() {
		$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.id = (SELECT home_health_id FROM home_health_facility_link WHERE home_health_facility_link.facility_id = :facility_id)";
		$params[":facility_id"] = $this->id;
		
		return $this->fetchOne($sql, $params);
	}


	public function fetchHomeHealthLocationByLocation($location_id = false) {
		$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.id = (SELECT home_health_id FROM home_health_facility_link WHERE home_health_facility_link.facility_id = :facility_id)";
		if ($location_id) {
			$params[":facility_id"] = $location_id;
		} else {
			$params[":facility_id"] = $this->id;
		}
		
		return $this->fetchAll($sql, $params);
	}

	public function fetchLinkedFacility($location_id = false) {
		if (auth()->valid()) {
			$user = auth()->getRecord();
		} else {
			return false;
			exit;
		}		

		$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN home_health_facility_link ON home_health_facility_link.facility_id = {$this->tableName()}.id WHERE home_health_facility_link.home_health_id IN (SELECT {$this->tableName()}.id FROM {$this->tableName()} INNER JOIN ac_user_location ON ac_user_location.location_id = {$this->tableName()}.id WHERE ac_user_location.user_id = :user_id)";

		if ($location_id) {
			$sql .= " AND home_health_facility_link.home_health_id = :location_id";
			$params[":location_id"] = $location_id;
		}

			
		$params[':user_id'] = $user->id;
		return $this->fetchOne($sql, $params);
	}
	
	//Fetches single location.
	public function fetchLocation($id) {
		$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.";
		
		if (is_numeric($id)) {
			$sql .= "`id`=:id";
		} else {
			$sql .="`public_id`=:id";
		}
		
		$params[':id'] = $id;
		return $this->fetchOne($sql, $params);
	}

	public function fetchLocationStates() {
		$sql = "(SELECT `{$this->tableName()}`.`state` FROM `{$this->tableName()}` WHERE `{$this->tableName()}`.`id` = :id) UNION (SELECT  `ac_location_link_state`.`state` FROM `{$this->tableName()}` INNER JOIN `ac_location_link_state` ON `ac_location_link_state`.`location_id` = `{$this->tableName()}`.`id` WHERE `{$this->tableName()}`.`id` = :id)";
		$params[':id'] = $this->id;
		return $this->fetchAll($sql, $params);
	}


	private function fetchLocationofType($type = null)
	{
		if(isset($_COOKIE['VouchCookie']))
		{
			$vc = auth()->VouchCookie();
			if($vc->status == 200){
				//get id from pubID in ac_user, lookup group_ids in ac_user_group, use group_id to get list of permission_id from ac_group_permission, user permission_id to get names of permissions from ac_permission.

				$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.location_type = 1 AND public_id IN (";
				foreach($vc->Available_Loc as $key => $value) {
					$sql .= ":Available_Loc$key, ";
					$params[":Available_Loc$key"] = $value;
				}
				//fix hanging fencewire.
				$sql = trim($sql, ", ");
				$sql .= ")";
				return db()->FetchRows($sql, $params, $this);
			} else {
				die("failed validation");
			}
		} else {
			$params[":user_id"] = auth()->getRecord()->id;

			$sql = "SELECT * FROM {$this->tableName()} INNER JOIN ac_user_location ON {$this->tableName()}.id = ac_user_location.location_id WHERE ac_user_location.user_id = :user_id";
			if($type != null)
			{
				$sql .= " AND {$this->tableName()}.location_type = :type";
				$params[":type"] = $type;
			}
			return $this->fetchAll($sql, $params);
		}
	}

	public function fetchAllLocations() {
		return $this->fetchLocationofType();
	}

	public function fetchHomeHealthLocations() {
		return $this->fetchLocationofType(2);
	}

	public function fetchFacilities() {
		return $this->fetchLocationofType(1);
	}

	/* comment out the old functions and merge to one shared function with type filters.
	// fetch all the locations to which the user has permission to access. 
	// this function is used on the mainpagecontroller
	public function fetchAllLocations() {
		$user = auth()->getRecord();
		$userLocation = $this->loadTable('UserLocation');

		$sql = "SELECT * FROM {$this->tableName()} INNER JOIN {$userLocation->tableName()} ON {$userLocation->tableName()}.location_id = {$this->tableName()}.id WHERE {$userLocation->tableName()}.user_id = :user_id";
		$params[":user_id"] = $user->id;
		return $this->fetchAll($sql, $params);
	}


	public function fetchHomeHealthLocations() {
		if(isset($_COOKIE['VouchCookie']))
		{
			$vc = auth()->VouchCookie();
			if($vc->status == 200){
				//get id from pubID in ac_user, lookup group_ids in ac_user_group, use group_id to get list of permission_id from ac_group_permission, user permission_id to get names of permissions from ac_permission.

				$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.location_type = 2 AND public_id IN (";
				foreach($vc->Available_Loc as $key => $value) {
					$sql .= ":Available_Loc$key, ";
					$params[":Available_Loc$key"] = $value;
				}
				//fix hanging fencewire.
				$sql = trim($sql, ", ");
				$sql .= ")";
				return db()->FetchRows($sql, $params, $this);
			} else {
				die("failed validation");
			}
		} else {
			$sql = "SELECT * FROM {$this->tableName()} LEFT JOIN ac_user_location ON ac_user_location.location_id = {$this->tableName()}.id WHERE ac_user_location.user_id = :id AND {$this->tableName()}.location_type = 2";
			$params[":id"] = auth()->getRecord()->id;
			return $this->fetchAll($sql, $params);
		}
	}


	public function fetchFacilities() {
		if(isset($_COOKIE['VouchCookie']))
		{
			$vc = auth()->VouchCookie();
			if($vc->status == 200){
				//get id from pubID in ac_user, lookup group_ids in ac_user_group, use group_id to get list of permission_id from ac_group_permission, user permission_id to get names of permissions from ac_permission.

				$sql = "SELECT * FROM {$this->tableName()} WHERE {$this->tableName()}.location_type = 1 AND public_id IN (";
				foreach($vc->Available_Loc as $key => $value) {
					$sql .= ":Available_Loc$key, ";
					$params[":Available_Loc$key"] = $value;
				}
				//fix hanging fencewire.
				$sql = trim($sql, ", ");
				$sql .= ")";
				return db()->FetchRows($sql, $params, $this);
			} else {
				die("failed validation");
			}
		} else {
			$params[":user_id"] = auth()->getRecord()->id;

			$sql = "SELECT * FROM {$this->tableName()} INNER JOIN ac_user_location ON {$this->tableName()}.id = ac_user_location.location_id WHERE {$this->tableName()}.location_type = 1 AND ac_user_location.user_id = :user_id";
			return $this->fetchAll($sql, $params);
		}
	}
	*/

	public function fetchHealthcareFacilityId() {
		$healthcareFacility = $this->loadTable("HealthcareFacility");
		$sql = "SELECT hf.id FROM {$healthcareFacility->tableName()} hf WHERE hf.name = :name";
		$params[":name"] = $this->name;
		return $this->fetchOne($sql, $params);
	}

}