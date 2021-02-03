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
vimport('~~/modules/WSAPP/synclib/connectors/HeadConnector.php');
vimport('~~/modules/WSAPP/SyncServer.php');
include_once 'includes/Webservices/Query.php';
include_once 'includes/Webservices/Create.php';
include_once 'includes/Webservices/Retrieve.php';

class Google_Head_Connector extends WSAPP_HeadConnector {

	/**
	 * function to push data to vtiger
	 * @param type $recordList
	 * @param type $syncStateModel
	 * @return type
	 */
	public function push($recordList, $syncStateModel) {
		return parent::push($recordList, $syncStateModel);
	}

	/**
	 * function to get data from vtiger
	 * @param type $syncStateModel
	 * @return type
	 */
	public function pull($syncStateModel) {
		$records = parent::pull($syncStateModel);
		return $records;
	}

	/**
	 * function that returns syncTrackerhandler name
	 * @return string
	 */
	public function getSyncTrackerHandlerName() {
		return 'Google_vtigerSyncHandler';
	}
	
}
