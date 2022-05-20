<?php

class Module extends AppData {

	protected $table = 'module';

	public function fetchUserModules($user) {
		//if we are in Vouch Proxy mode, they we use the listed permissions to lookup accessible modules in reverse!
		if(isset($_COOKIE['VouchCookie']))
		{
			$vc = auth()->VouchCookie();
			if($vc->status == 200){
				$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} WHERE id IN (SELECT module_id from ac_group_module where ac_group_module.group_id IN (SELECT id from ac_group WHERE ac_group.name IN (";
				foreach(auth()->VouchCookie()->Useraccess as $key => $value) {
					$sql .= ":access$key, ";
					$params[":access$key"] = $value;
				}
				//fix hanging fencewire.
				$sql = trim($sql, ", ");
				$sql .= ")));";

				#die($sql.print_r($params, true));
				return db()->FetchRows($sql, $params, $this);
			} else {
				$sql = "SELECT {$this->tableName()}.* FROM {$this->tableName()} INNER JOIN `ac_user_module` ON `ac_user_module`.`module_id`={$this->tableName()}.`id` INNER JOIN `ac_user` ON `ac_user`.`id`=`ac_user_module`.`user_id` WHERE `ac_user`.`public_id`=:userid order by `ac_user`.`default_module`, `ac_module`.`name` ASC";
				$params[':userid'] = $user;
				return $this->fetchAll($sql, $params);
			}
		}
	}

	public function fetchDefaultModule() {
		$params[":default_module"] = auth()->getRecord()->default_module;
		$sql = "SELECT m.* FROM {$this->tableName()} m WHERE m.id = :default_module";
		return $this->fetchOne($sql, $params);
	}

}
