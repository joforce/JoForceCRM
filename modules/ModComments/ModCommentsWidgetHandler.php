<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 ************************************************************************************/
global $currentModule;

$widgetName = modlib_purify($_REQUEST['widget']);
$criteria = modlib_purify($_REQUEST['criteria']);

$widgetController = CRMEntity::getInstance($currentModule);
$widgetInstance = $widgetController->getWidget($widgetName);
$widgetInstance->setCriteria($criteria);

echo $widgetInstance->process( array('ID' => modlib_purify($_REQUEST['parentid'])) );
