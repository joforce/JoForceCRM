<?php
class PDFMaker_Save_Action extends Head_Save_Action {

        public function checkPermission(Head_Request $request) {
                return true;
        }

        public function process(Head_Request $request) {

                $moduleName = $request->getModule();
                $record = $request->get('record');
                $recordModel = new PDFMaker_Record_Model();
                $recordModel->setModule($moduleName);

                if(!empty($record)) {
                        $recordModel->setId($record);
                }
		$status = $request->get('status');
		$pdf_settings = array();
		$pdf_settings['file_name'] = $request->get('filename');
                $pdf_settings['page_format'] = $request->get('page_format');
                $pdf_settings['page_orientation'] = $request->get('page_orientation');
                $pdf_settings['margin_top'] = $request->get('margin_top');
                $pdf_settings['margin_bottom'] = $request->get('margin_bottom');
                $pdf_settings['margin_left'] = $request->get('margin_left');
                $pdf_settings['margin_right'] = $request->get('margin_right');
		$pdf_settings['detailview'] = $request->get('detailview');
		$pdf_settings['listview'] = $request->get('listview');
		$settings = base64_encode(serialize($pdf_settings));
                # Get Data/Save - Module Installation
                if($request->get('parent')){
                    global $adb; 
                    $response = new Head_Response();
                    try{
                        $getallDetails = $adb->pquery('SELECT * FROM jo_pdfmaker');
                        while ($record = $adb->fetchByAssoc($getallDetails)) {
                            $pdf_settings['file_name'] = $record['name'];
                            $encodesetting=base64_encode(serialize($pdf_settings));
                            $sql = 'UPDATE `jo_pdfmaker` SET `settings`=? WHERE pdfmakerid=?';
                            $params = array($encodesetting,$record['pdfmakerid']);
                            $result = $adb->pquery($sql, $params);
                        }                             
                        $count =Settings_Head_UserDetailsSave_Action::completedpercentage("25",3); 
                        $data = array('success'=>true,'count' =>$count);
                        $response->setResult($data);
                    }catch(Exception $e) {
                        $response->setError($e->getCode(), $e->getMessage());
                    }
                    $response->emit();
                    die;
                }

                $recordModel->set('name', $request->get('templatename'));
                $recordModel->set('description', $request->get('description'));
                $recordModel->set('module', $request->get('modulename'));
                $recordModel->set('body', $request->get('templatecontent'));
                $recordModel->set('header', $request->get('templatecontent-header'));
                $recordModel->set('footer', $request->get('templatecontent-footer'));

		if(!empty($status))
			$recordModel->set('status', $status);
		else{
			$status = 0;
                        $recordModel->set('status', $status);
		}	
		$recordModel->set('settings', $settings);
                $recordModel->save();

                $loadUrl = $recordModel->getDetailViewUrl();
                header("Location: $loadUrl");

	}

}
