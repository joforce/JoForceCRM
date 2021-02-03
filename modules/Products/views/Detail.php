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

class Products_Detail_View extends Head_Detail_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('showBundleTotalCostView');
	}
	
	function preProcess(Head_Request $request, $display = true) {
		global $adb;
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Head_Record_Model::getInstanceById($recordId, $moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();
		
		$viewer = $this->getViewer($request);

		$inventoryModules = array('Invoice' => array('id' => 'invoiceid', 'table' => 'jo_invoice'),
                        'Quotes' => array('id' => 'quoteid', 'table' => 'jo_quotes'),
			'SalesOrder' => array('id' => 'salesorderid', 'table' => 'jo_salesorder'),
			'PurchaseOrder' => array('id' => 'purchaseorderid', 'table' => 'jo_purchaseorder'),
                );
                $totalValue = array();
                foreach($inventoryModules as $key => $singleModule){
                        $total = 0;
                        $query = $adb->pquery("select total from jo_crmentityrel join {$singleModule['table']} on {$singleModule['id']} = relcrmid where crmid = ? and relmodule = ?", array($recordId, $key));
                        if($adb->num_rows($query) > 0){
                                while($result = $adb->fetch_array($query)){
                                        $total += $result['total'];
                                }
                                $totalValue[$key] = $total;
                        }
                        else{
                                $totalValue[$key] = 0;
                        }
                }
                $getRelatedDeals = getRelatedRecordSumValue($recordId, $request->getModule(), 'Potentials', 'amount');
		$getTicketCount = getRelatedRecordSumValue($recordId, $request->getModule(), 'HelpDesk');

                $totalValue['Potentials'] = $getRelatedDeals? $getRelatedDeals : 0;
                $totalValue['HelpDesk'] = $getTicketCount? $getTicketCount : 0;
                $viewer->assign('TOTAL', $totalValue);

		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		
		parent::preProcess($request, $display);
	}

	public function showModuleDetailView(Head_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Head_Record_Model::getInstanceById($recordId, $moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		$viewer->assign('TAXCLASS_DETAILS', $recordModel->getTaxClassDetails());
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}

	public function showModuleBasicView(Head_Request $request) {
		return $this->showModuleDetailView($request);
	}
	
	public function getOverlayHeaderScripts(Head_Request $request){
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.'.$moduleName.'.resources.Detail';
		$jsFileNames = array(
			'~libraries/jquery/boxslider/jquery.bxslider.min.js',
			'modules.PriceBooks.resources.Detail',
		);
		$jsFileNames[] = $moduleDetailFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;	
	}

	public function getHeaderScripts(Head_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.'.$moduleName.'.resources.Detail';
		$moduleRelatedListFile = 'modules.'.$moduleName.'.resources.RelatedList';
		unset($headerScriptInstances[$moduleDetailFile]);
		unset($headerScriptInstances[$moduleRelatedListFile]);

		$jsFileNames = array(
			'~libraries/jquery/jquery.cycle.min.js',
			'~libraries/jquery/boxslider/jquery.bxslider.min.js', 
			'modules.PriceBooks.resources.Detail',
			'modules.PriceBooks.resources.RelatedList',
		);
		
		$jsFileNames[] = $moduleDetailFile;
		$jsFileNames[] = $moduleRelatedListFile;

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function showBundleTotalCostView(Head_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentRecordId = $request->get('record');
		$tabLabel = $request->get('tabLabel');

		if ($moduleName === $relatedModuleName && $tabLabel === 'Product Bundles') {//Products && Child Products
			$parentRecordModel = Head_Record_Model::getInstanceById($parentRecordId, $moduleName);
			$parentModuleModel = $parentRecordModel->getModule();
			$parentRecordModel->set('currency_id', getProductBaseCurrency($parentRecordId, $parentModuleModel->getName()));

			$subProductsCostsInfo = $parentRecordModel->getSubProductsCostsAndTotalCostInUserCurrency();
			$subProductsTotalCost = $subProductsCostsInfo['subProductsTotalCost'];
			$subProductsCostsInfo = $subProductsCostsInfo['subProductsCosts'];

			$viewer = $this->getViewer($request);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('TAB_LABEL', $tabLabel);
			$viewer->assign('PARENT_RECORD', $parentRecordModel);
			$viewer->assign('SUB_PRODUCTS_TOTAL_COST', $subProductsTotalCost);
			$viewer->assign('SUB_PRODUCTS_COSTS_INFO', $subProductsCostsInfo);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

			return $viewer->view('BundleCostView.tpl', $moduleName, 'true');
		}
	}
}
