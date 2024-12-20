<?php

class InfoController extends DietaryController {

	public $module = "Dietary";
	protected $navigation = 'dietary';
	protected $searchBar = 'dietary';
	protected $helper = 'DietaryMenu';





	public function current() {

		smarty()->assign('title', "Current Menu");

		//passthrough permission to hide edit button.
		smarty()->assign('permission', auth()->hasPermission('manage_menu'));

		// Check url for week in the past or future
		if (isset (input()->weekSeed)) {
			$weekSeed = input()->weekSeed;
		// If no date is set in the url then default to this week
		} else {
			$weekSeed = date('Y-m-d');
		}

		$week = Calendar::getWeek($weekSeed);

		$nextWeekSeed = date("Y-m-d", strtotime("+7 days", strtotime($week[0])));


		smarty()->assign(array(
			'weekSeed' => $weekSeed,
			'weekStart' => date('Y-m-d', strtotime($weekSeed)),
			'week' => $week,
			'advanceWeekSeed' => $nextWeekSeed,
			'retreatWeekSeed' => date("Y-m-d", strtotime("-7 days", strtotime($weekSeed))),
		));


		$_dateStart = date('Y-m-d', strtotime($week[0]));
		$_dateEnd = date('Y-m-d', strtotime($week[6]));

		//changed to be if today not in week shown, show today button.
		//if (strtotime($_dateStart) > strtotime('now')) {
		if (!in_array(date('Y-m-d', strtotime('now')), $week, true)) {
			$today = date('Y-m-d', strtotime('now'));
		} else {
			$today = false;
		}

		smarty()->assign('today', $today);

		smarty()->assign('startDate', $_dateStart);


		$urlDate = date('m/d/Y', strtotime($_dateStart));
		$printDate = date("l, F j, Y", strtotime($_dateStart));
		smarty()->assign('urlDate', $urlDate);


		// Get the selected facility. If no facility has been selected return the users' default location
		$location = $this->getLocation();

		// Get the menu id the facility is currently using
		$menu = $this->loadModel('Menu')->fetchMenu($location->id, $_dateStart);
		smarty()->assign('menu', $menu);

		// Get the menu day for today
		$numDays = $this->loadModel('MenuItem')->fetchMenuDay($menu->menu_id);
		$startDay = round($this->dateDiff($menu->date_start, $_dateStart) % $numDays->count + 1);
		$endDay = $startDay + 6;


		// Get the menu items for the week
		$menuItems = $this->loadModel('MenuItem')->fetchMenuItems($location->id, $_dateStart, $_dateEnd, $startDay, $endDay, $menu->menu_id);
		$this->normalizeMenuItems($menuItems);
	}

	public function create(){
		// if the user does not have permission then throw error and redirect
		if (!auth()->hasPermission('create_menu')) {
			session()->setFlash("You do not have permission to access that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
	}

	public function save_create() {
		// if the user does not have permission then throw error and redirect
		if (!auth()->hasPermission('create_menu')) {
			session()->setFlash("You do not have permission to access that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
		
		if (input()->menu_name != "") {
			$new_menu = $this->loadModel('Menu');
			$new_menu->name = input()->menu_name;
			$new_menu->public_id = getRandomString();
		} else {
			$error_messages[] = "Enter a name for the new menu";

		}

		if (input()->num_weeks != "") {
			$num_days = input()->num_weeks * 7;
		} else {
			$error_messages[] = "Enter the number of weeks in the menu";
		}

		// break point
		if (!empty ($error_messages)) {
			session()->setFlash($error_messages, 'error');
			$this->redirect(input()->current_url);
		}

		if ($new_menu->save()) {
			$success = false;
			for ($day = 1; $day <= $num_days; $day++) {
				$meal_id = 1;
				while ($meal_id <= 3) {
					$menu_item = $this->loadModel('MenuItem');
					$menu_item->menu_id = $new_menu->id;
					$menu_item->meal_id = $meal_id;
					$menu_item->day = $day;
					$menu_item->content = "No menu content has been entered.";
					#try manual pubID gen
					$menu_item->public_id = getRandomString();
					#manual set datetime_created
					$menu_item->datetime_created = mysql_datetime();
					$menu_item->datetime_modified = mysql_datetime();
					$menu_item->save();
					$meal_id++;
				}
				$success = true;
			}

			if ($success) {
				session()->setFlash("The new menu was created", 'success');
				$this->redirect(array('module' => "Dietary", 'page' => "info", 'action' => "corporate_menus", 'menu' => $new_menu->public_id));
			} else {
				session()->setFlash("Could not create the new menu. Please try again.", 'error');
				$this->redirect(input()->current_url);
			}
		}

	}


	/*
	 * -------------------------------------------------------------------------
	 *  Manage the corporate menus
	 * -------------------------------------------------------------------------
	 *
	 * Access to this page is restricted to corporate admins
	 *
	 */
	public function manage() {
		// if the user does not have permission then throw error and redirect
		if (!auth()->hasPermission('create_menu')) {
			session()->setFlash("You do not have permission to access that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}

		// fetch all the menus
		$menus = $this->loadModel('Menu')->fetchAll();
		#die(print_r($menus, true));
		smarty()->assign('menus', $menus);

	}


	/*
	 * -------------------------------------------------------------------------
	 *  DELETE USERS
	 * -------------------------------------------------------------------------
	 */
	public function delete_menu() {
		//	If the id var is filled then delete the item with that id
		if (input()->menu != '') {
			$menu = $this->loadModel('Menu', input()->menu);


			// delete all entries in menu_item
			if ($this->loadModel('MenuItem')->deleteMenuItems($menu->id)) {
				if ($menu->delete()) {
					return true;
				}

				return false;
			}

			return false;
		}

		return false;
	}


	/*
	 * -------------------------------------------------------------------------
	 *  EDIT MENU
	 * -------------------------------------------------------------------------
	 *
	 * Functionality to change the menu name only right now. May need to add
	 * addtional functionality in the future...
	 *
	 */
	public function edit_menu() {
		$menu = $this->loadModel('Menu', input()->menu);
		smarty()->assign('menu', $menu);

		// if this is a post then we are trying to save
		if (input()->is('post')) {
			$menu->name = input()->name;

			if ($menu->save()) {
				session()->setFlash("The name of the menu has been changed.", 'success');
				$this->redirect(array('module' => "Dietary", 'page' => "info", 'action' => "manage"));
			} else {
				session()->setFlash("Could not change the name of the menu. Please try again.", 'error');
				$this->redirect(input()->current_url);
			}
		}
	}



	public function corporate_menus() {
		// if the user does not have permission then throw error and redirect
		if (!auth()->hasPermission('create_menu')) {
			session()->setFlash("You do not have permission to access that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
		
		smarty()->assign('title', "Corporate Menus");
		session()->setReferringPage();
		// Get all available menus
		$menus = $this->loadModel('Menu')->fetchAll();
		smarty()->assign('menus', $menus);

		// Set the selected menu info
		if (isset (input()->menu)) {
			$selectedMenu = $this->loadModel('Menu', input()->menu);
		} else {
			// if no menu has been selected we will just assign the first menu in the array
			$selectedMenu = $menus[0];
		}
		smarty()->assign('selectedMenu', $selectedMenu);

		if (isset (input()->page)) {
			$page = input()->page;
		} else {
			$page = 1;
		}

		// Fetch content for selected menu
		$menuItems = $this->loadModel('MenuItem')->paginateMenuItems($selectedMenu->id, null, $page);
		$this->normalizeMenuItems($menuItems);

	}


	public function facility_menus() {
		// if the user does not have permission then throw error and redirect
		if (!auth()->hasPermission('create_menu')) {
			session()->setFlash("You do not have permission to access that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
		
		smarty()->assign('title', "Facility Menu");
		$location = $this->getLocation();

		$date = date('Y-m-d', strtotime("now"));
		$currentMenu = $this->loadModel('LocationMenu')->fetchMenu($location->id, $date);

		if (isset (input()->menu_id)) {
			$selectedMenu = $this->loadModel('Menu', input()->menu_id);
		} else {
			$selectedMenu = $currentMenu;
		}

		smarty()->assign('location', $location);
		smarty()->assign('currentMenu', $currentMenu);

		// get all available menus for this location
		$availableMenus = $this->loadModel('LocationMenu')->fetchAvailable($location->id);
		smarty()->assign('availableMenus', $availableMenus);
		smarty()->assign('selectedMenu', $selectedMenu);

		if (isset (input()->page)) {
			$page = input()->page;
		} else {
			$page = false;
		}

		// paginate menu info
		$results = $this->loadModel('MenuItem')->paginateMenuItems($currentMenu->menu_id, $location->id, $page);
		$this->normalizeMenuItems($results);

		smarty()->assign('menu', $selectedMenu);

	}


	public function public_page_items() {
		$authStatus = auth()->hasPermission('dietary_public_page_items');
		
		if (!auth()->hasPermission('dietary_public_page_items')) {
			session()->setFlash("You do not have permission to save this page.", 'success');
		}
		
		smarty()->assign("title", "Public Page Items");
		smarty()->assign("authStatus", $authStatus);
		$location = $this->getLocation();

		#die($this->loadModel("LocationDetail")->tableName());
		// menu greeting
		$menuGreeting = $this->loadModel("LocationDetail")->fetchOne(null, array("location_id" => $location->id));
	
		//if this is false then one has not been created before.
		if($menuGreeting === false) {
			$menuGreeting = $this->loadModel("LocationDetail");
			$menuGreeting->location_id =  $location->id;
			$menuGreeting->menu_greeting =  " ";
			$menuGreeting->datetime_created =  mysql_datetime();;
			$menuGreeting->datetime_modified =  mysql_datetime();;
			$menuGreeting->id =  NULL;
			$menuGreeting->save();
		}
		smarty()->assign("menuGreeting", $menuGreeting);

		// meal time info
		$meals = $this->loadModel("Meal")->fetchAll(null, array("location_id" => $location->id));
		if(count($meals) != 3)
		{
			//base config of type => name, start, end.
			$meals2fix = array(
				1=> array(
					"name" => "Breakfast", 
					"start" => "8:00",
					"end" => "9:30"
				),
				2=> array(
					"name" => "Lunch", 
					"start" => "11:00",
					"end" => "12:30"
				),
				3=> array(
					"name" => "Dinner",
					"start" => "17:00",
					"end" => "18:15"
				)
			);

			//if the meal already exists from DB, don't create new ones.
			foreach($meals as $k => $v) {
				unset($meals2fix[$meals[$k]->type]);
			}

			//create new meal objects and save to DB.
			foreach($meals2fix as $k => $v) {
				$tempMeal = $this->loadModel("Meal");
				$tempMeal->id = NULL;
				$tempMeal->location_id = $location->id;
				$tempMeal->type = $k;
				$tempMeal->start = $v['start'];
				$tempMeal->end = $v['end'];
				$tempMeal->datetime_created =  mysql_datetime();
				$tempMeal->datetime_modified =  mysql_datetime();
				$tempMeal->save();
				$meals[] = $tempMeal;
			}

			//sort array to be the right order if we have been messing with a missing item.
			usort($meals, function($a, $b) {return strcmp($a->type, $b->type);});

		} 
		//var_dump($meals);
		smarty()->assign("meals", $meals);

		// alternate menu items
		$alternates = $this->loadModel("Alternate")->fetchOne(null, array("location_id" => $location->id));
		if($alternates === false)
		{
			$user = auth()->getRecord();

			$alternates = $this->loadModel("Alternate");
			$alternates->location_id = $location->id;
			$alternates->user_id = $user->id;
			$alternates->content = "Alternate1; Alternate2";
			$alternates->datetime_created =  mysql_datetime();
			$alternates->datetime_modified =  mysql_datetime();
			$alternates->save();
		}

		smarty()->assignByRef("alternates", $alternates);
	}



	public function submitWelcomeInfo() {
		if (!auth()->hasPermission('dietary_public_page_items')) {
			session()->setFlash("You do not have permission to save that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
		$greeting = $this->loadModel("LocationDetail", input()->location_detail_id);
		$location = $this->loadModel("Location", input()->location);
		$greeting->menu_greeting = input()->menu_greeting;
		if ($greeting->save()) {
			session()->setFlash("The menu greeting info was changed for {$location->name}", "success");
		} else {
			session()->setFlash("Could not save the greeting info. Please try again.", "error");
		}

		$this->redirect(input()->path);
	}


	public function submitMealTimes() {
		if (!auth()->hasPermission('dietary_public_page_items')) {
			session()->setFlash("You do not have permission to save that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}
		$message = array();

		var_dump(input());
		$end_time = get_object_vars(input()->end);

		foreach (input()->start as $key => $start_time) {
			$meal = $this->loadModel("Meal", $key);
			if ($start_time != "") {
				$meal->start = date("H:i:s", strtotime($start_time));
			} else {
				session()->setFlash("Set a meal time and try again.", "error");
				$this->redirect(input()->path);
			}
			if ($end_time  != "") {
				$meal->end = date("H:i:s", strtotime($end_time[$key]));
			} else {
				session()->setFlash("Set a meal time and try again.", "error");
				$this->redirect(input()->path);
			}

			if ($meal->save()) {
				$message[] = "The meal time was successfully saved.";
			} else {
				$message[] = "Could not save the meal time.";
			}
		}

		session()->setFlash($message, "success");
		$this->redirect(input()->path);

	}


	public function submitAltItems() {
		if (!auth()->hasPermission('dietary_public_page_items')) {
			session()->setFlash("You do not have permission to save that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}

		$location = $this->loadModel('Location', input()->location);
		$alternate = $this->loadModel('Alternate', input()->alt_menu_id);

		if (input()->alt_menu == "") {
			session()->setFlash("Please enter items for the alternate menu", 'error');
			$this->redirect(input()->path());
		} else {
			$alternate->content = input()->alt_menu;
		}

		if ($alternate->location_id == "") {
			$alternate->location_id = $location->id;
		}

		$alternate->user_id = auth()->getRecord()->id;

		if ($alternate->save()) {
			session()->setFlash("The alternate menu was changed for {$location->name}", 'success');
		} else {
			session()->setFlash("Could not save the alternate menu changes. Please try again.", 'error');
		}
		$this->redirect(input()->path);
	}



/*
 * FACILITY BEVERAGES
 * Loads a list of the beverages available at the selected facility
 *
 */
	public function beverages() {
		$location = $this->getLocation();
		$beverages = $this->loadModel('LocationBeverage')->fetchBeverages($location->id);
		smarty()->assign('beverages', $beverages);
	}



/*
 * SAVE THE FACILITY BEVERAGES
 * Save the beverages entered by the user.
 */
	public function save_beverages() {
		$location = $this->loadModel('Location', input()->location);
		$beverage_list = $this->loadModel('BeverageList')->fetchAll();
		$location_beverage_list = $this->loadModel('LocationBeverage')->fetchAll(null, array('location_id' => $location->id));

		// create an error save array
		$save_errors = array();

		// create an array holding the beverage id's
		$bev_id_array = array();
		foreach ($beverage_list as $b) {
			array_push($bev_id_array, $b->id);
		}
		// create an array for the location specific beverages
		$loc_id_array = array();
		foreach ($location_beverage_list as $b) {
			array_push($loc_id_array, $b->beverage_id);
		}

		// loop through input items and check if they already exist in the beverage list
		foreach (input()->beverage as $ii) {
			// if the id is not in the array then the item doesn't exist, save it...
			if (!in_array($ii['id'], $bev_id_array)) {
				$new_beverage = $this->loadModel('BeverageList');
				$new_beverage->name = $ii['name'];
				if ($new_beverage->save()) {
					array_push($save_errors, true);
				} else {
					array_push($save_errors, false);
				}

				// if the id did not exist in the beverages list then it has never been
				// saved for the location.
				// add the new beverage to the beverage location list as well
				$location_beverage = $this->loadModel('LocationBeverage');
				$location_beverage->location_id = $location->id;
				$location_beverage->beverage_id = $new_beverage->id;
				if ($location_beverage->save()) {
					array_push($save_errors, true);
				} else {
					array_push($save_errors, false);
				}

			}

			// if the id is not in the location beverage list then it needs to be added
			if (!in_array($ii['id'], $loc_id_array)) {
				$location_beverage = $this->loadModel('LocationBeverage');
				$location_beverage->location_id = $location->id;
				$location_beverage->beverage_id = $ii['id'];
				if ($location_beverage->save()) {
					array_push($save_errors, true);
				} else {
					array_push($save_errors, false);
				}
			}
		}

		// error checking. set flash message and redirect to appropriate url
		if (in_array(false, $save_errors)) {
			session()->setFlash("There was a problem saving the beverages, please try again.", 'error');
			$this->redirect(input()->current_url);
		} else {
			session()->setFlash("The beverages were saved", 'success');
			$this->redirect(array('module' => 'Dietary', 'page' => 'dietary', 'action' => 'index', 'location' => $location->public_id));
		}

	}



/*
 * This was just a snippet to run to enter all the beverages in each location into
 * the database.
 *
 */

// public function insert_beverages() {
// 	$locations = $this->loadModel('Location')->fetchFacilities();
//
// 	foreach ($locations as $l) {
// 		$i = 1;
// 		while ($i <= 12) {
// 			$location_beverage = $this->loadModel('LocationBeverage');
// 			$location_beverage->location_id = $l->id;
// 			$location_beverage->beverage_id = $i;
// 			$location_beverage->save();
// 			$i++;
// 		}
// 	}
//
// 	pr ($location_beverage); exit;
//
// }



/*
 * AJAX request to delete a beverage items
 *
 */
	public function delete_beverage() {
		//	If the id var is filled then delete the item with that id
		if (input()->bev_id != '') {
			$beverage = $this->loadModel('Location', input()->menu);


			// delete all entries in menu_item
			if ($this->loadModel('MenuItem')->deleteMenuItems($menu->id)) {
				if ($menu->delete()) {
					return true;
				}

				return false;
			}

			return false;
		}

		return false;

	}


/*
 * SET MENU START DATE
 * This page is used to set the start date when changing the menu the facility will
 * be using. This change usually only occurs twice per years, but depends on how
 * many menus the facility will utilyze throughout the year.
 *
 */
	public function menu_start_date() {
		$authStatus = auth()->hasPermission('dietary_menu_start_date');
		
		if (!auth()->hasPermission('dietary_menu_start_date')) {
			session()->setFlash("You do not have permission to save this page.", 'success');
		}
		
		smarty()->assign("authStatus", $authStatus);

		smarty()->assign("title", "Menu Start Date");
		$location = $this->getLocation();

		$date = date("Y-m-d", strtotime("now"));
		smarty()->assign("date", $date);
		$availableMenus = $this->loadModel("LocationMenu")->fetchAvailable($location->id);
		$currentMenu = $this->loadModel("LocationMenu")->fetchCurrent($location->id, $date);
		
		$allMenus = $this->loadModel("LocationMenu")->fetchAvailableWithUnasigned($location->id);

		smarty()->assignByRef("availableMenus", $allMenus);
		smarty()->assignByRef("currentMenu", $currentMenu);
	}



	/*
	 * SUBMIT THE START DATE
	 * Submits, checks for errors, and saves the start date submitted from the set
	 * set menu start date page.
	 *
	 */
	public function submitStartDate() {
		if (!auth()->hasPermission('dietary_menu_start_date')) {
			if(isset(input()->isAjax) && input()->isAjax == true)
			{
				http_response_code(403); //Forbidden
				json_return("You do not have permission to save that page.");
			}
			session()->setFlash("You do not have permission to save that page.", 'error');
			$this->redirect(array('module' => $this->getModule()));
		}

		$location = $this->loadModel("Location", input()->location);
		if (input()->menu != "") {
			$menu = $this->loadModel("Menu", input()->menu);
			$locationMenu = $this->loadModel("LocationMenu")->checkExisting($menu->id, $location->id);
		} else {
			session()->setFlash("Please select a new menu to start", "error");
			$this->redirect(input()->path);
		}

		if (input()->date_start != "") {
			$locationMenu->date_start = date("Y-m-d", strtotime(input()->date_start));
			$date = input()->date_start;
		} else {
			session()->setFlash("Select the date to start the menu.", "error");
			$this->redirect(input()->path);
		}

		//this means this menu has not been set before.
		if($locationMenu->id === NULL)
		{
			//$locationMenu is now considered new so populate
			
			$locationMenu->public_id = getRandomString();
			$locationMenu->location_id = $location->id;
			$locationMenu->menu_id = $menu->id;
			$locationMenu->datetime_modified = mysql_datetime();
			#die(var_dump($locationMenu));
		}

		if ($locationMenu->save()) {
			if(isset(input()->isAjax) && input()->isAjax == true)
			{
				json_return("Good!");
			} else {
				session()->setFlash("The new menu will start on {$date} for {$location->name}", "success");
				$this->redirect(array("module" => $this->module));
			}
		} else {
			if(isset(input()->isAjax) && input()->isAjax == true)
			{
				http_response_code(500); //it failed.
				json_return("BAD!!");
			}
		}


	}

}
