<?php
/* +**********************************************************************************
 * The contents of this file are subject to the JoForce Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Developer of the Original Code is JoForce.
 * All Rights Reserved.
 * ********************************************************************************** */

                        $getUserId = $adb->pquery('select * from rc_server_details where user_id = ?', array($current_user->id));
                        $userId = $adb->query_result($getUserId, 0, 'user_id');
                        $server_name = $adb->query_result($getUserId, 0, 'name');
                        $account_type = $adb->query_result($getUserId, 0, 'account_type');
                        $port = $adb->query_result($getUserId, 0, 'port');
                        $email = $adb->query_result($getUserId, 0, 'email');
                        $get_password = $adb->query_result($getUserId, 0, 'password');
                        $password = base64_decode($get_password);
                        $enabletype = $adb->query_result($getUserId, 0, 'enabletype');
                        $moduleName = $request->getModule();
                        if(!empty($moduleName)) {
                                $moduleModel = Head_Module_Model::getInstance($moduleName);
                                $currentUser = Users_Record_Model::getCurrentUserModel();
                                $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
                                $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
                                $viewer->assign('MODULE', $moduleName);

                                if(!$permission) {
                                        $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                                        $viewer->view('OperationNotPermitted.tpl', $moduleName);
                                        exit;
                                }

                                $linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
                                $linkModels = $moduleModel->getSideBarLinks($linkParams);

                                $viewer->assign('QUICK_LINKS', $linkModels);
                        }
                        $viewer->assign('ENABLETYPE', $enabletype);
                        $viewer->assign('SERVER', $server_name);
                        $viewer->assign('TYPE', $account_type);
                        $viewer->assign('PORT', $port);
                        $viewer->assign('email', $email);
                        $viewer->assign('password', $password);
			$viewer->assign('MODULE', $moduleName);
                        $viewer->view('ServerSettings.tpl', $moduleName);
?>
