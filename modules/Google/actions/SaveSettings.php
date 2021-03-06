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

class Google_SaveSettings_Action extends Head_BasicAjax_Action {

    public function process(Head_Request $request) {
        $sourceModule = $request->get('sourcemodule');
        $fieldMapping = $request->get('fieldmapping');
        Google_Utils_Helper::saveSettings($request);
        if($fieldMapping) {
            Google_Utils_Helper::saveFieldMappings($sourceModule, $fieldMapping);
        }
        $response = new Head_Response;
        $result = array('settingssaved' => true);
        $response->setResult($result);
        $response->emit();
    }
    
}

?>