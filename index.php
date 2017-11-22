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

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution
include_once 'config/config.php';
include_once 'includes/Webservices/Relation.php';

include_once 'vtlib/Head/Module.php';
include_once 'includes/main/WebUI.php';

$webUI = new Head_WebUI();
include_once 'includes/main/Cannonical.php';
$webUI->process(new Head_Request($_REQUEST, $_REQUEST));
