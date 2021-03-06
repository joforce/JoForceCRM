<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 * *********************************************************************************** */

class PriceBooks_SaveAjax_Action extends Head_SaveAjax_Action {

	public function saveRecord($request) {
		$recordModel = $this->getRecordModelFromRequest($request);
        vglobal('JOFORCE_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode',false));
		$recordModel->save();
        vglobal('JOFORCE_TIMESTAMP_NO_CHANGE_MODE', false);
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Head_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Head_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);

			//To store the relationship between Products/Services and PriceBooks
			if ($parentRecordId && ($parentModuleName === 'Products' || $parentModuleName === 'Services')) {
				$parentRecordModel = Head_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
				$sellingPricesList = $parentModuleModel->getPricesForProducts($recordModel->get('currency_id'), array($parentRecordId));
				$recordModel->updateListPrice($parentRecordId, $sellingPricesList[$parentRecordId]);
			}
		}
		return $recordModel;
	}
}
