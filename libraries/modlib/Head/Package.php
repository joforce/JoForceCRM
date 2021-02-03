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
require_once('libraries/modlib/Head/PackageUpdate.php');

/**
 * Package Manager class for vtiger Modules.
 * @package modlib
 */
class Head_Package extends Head_PackageUpdate {

	/**
	 * Constructor
	 */
	function Head_Package() {
		parent::__construct();
	}
}
?>
