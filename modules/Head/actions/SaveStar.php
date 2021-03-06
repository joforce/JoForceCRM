<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 * ***********************************************************************************/

class Head_SaveStar_Action extends Head_Mass_Action {

	function checkPermission(Head_Request $request) {
		//Return true as WebUI.php is already checking for module permission
		return true;
	}

	public function process(Head_Request $request) {
		$module = $request->get('module');
		if ($request->has('selected_ids')) {
			$recordIds = $this->getRecordsListFromRequest($request);
		} else {
			$recordIds = array($request->get('record'));
		}

		$moduleUserSpecificTableName = Head_Functions::getUserSpecificTableName();
		//TODO : Currently we are not doing retrieve_entity_info before doing save since we have only one user specific field(starred)
		// if we add more user specific field then we need to peform retrieve_entity_info 
		foreach ($recordIds as $recordId) {
			$focus = CRMEntity::getInstance($module);
			$focus->mode = "edit";
			$focus->id = $recordId;
			$focus->column_fields->startTracking();
			$focus->column_fields['starred'] = $request->get('value');
			$focus->insertIntoEntityTable($moduleUserSpecificTableName, $module);
		}

		$response = new Head_Response();
		$response->setResult(true);
		$response->emit();
	}

}
