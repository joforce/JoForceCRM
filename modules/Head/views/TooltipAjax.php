<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 ************************************************************************************/

class Head_TooltipAjax_View extends Head_PopupAjax_View {

	function preProcess(Head_Request $request) {
		return true;
	}

	function postProcess(Head_Request $request) {
		return true;
	}

	function process (Head_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		
		$this->initializeListViewContents($request, $viewer);
		echo $viewer->view('TooltipContents.tpl', $moduleName, true);
	}
	
	public function initializeListViewContents(Head_Request $request, Head_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		$recordId = $request->get('record');

		$tooltipViewModel = Head_TooltipView_Model::getInstance($moduleName, $recordId);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_MODEL', $tooltipViewModel->getRecord()->getModule());
		
		$viewer->assign('TOOLTIP_FIELDS', $tooltipViewModel->getFields());
		$viewer->assign('RECORD', $tooltipViewModel->getRecord());
		$viewer->assign('RECORD_STRUCTURE', $tooltipViewModel->getStructure());
		$viewer->assign('RECORD_ID', $recordId);

		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}

}