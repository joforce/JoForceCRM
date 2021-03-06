<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): JoForce.com
 *************************************************************************************/

/**
 * ModComments Record Model
 */
class ModComments_Record_Model extends Head_Record_Model {

	/**
	 * Functions gets the comment id
	 */
	public function getId() {
		//TODO : check why is modcommentsid is not set
		$id = $this->get('modcommentsid');
		if(empty($id)) {
			return $this->get('id');
		}
		return $this->get('modcommentsid');
	}

	public function setId($id) {
		return $this->set('modcommentsid', $id);
	}

	/**
	 * Function returns url to get child comments
	 * @return <String> - url
	 */
	public function getChildCommentsUrl() {
		return $this->getDetailViewUrl().'&mode=showChildComments';
	}


	public function addBlockQuotes(){
		$content = $this->get('commentcontent');
		$doc = new DOMDocument();
		$urls = array();

		if(!empty($content)){
			//handling utf8 characters present in the template source
			$formattedContent = mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
			$doc->loadHTML($formattedContent);
			$body = $doc->getElementsByTagName('body');
			 $body = $body->item(0);
		}
	}


	/**
	 * Funtion returns the fi
	 * @param type $recordId
	 * @return type
	 */
	public function getFileNameAndDownloadURL($recordId = false,$attachmentId = false){
        global $site_URL;
		if(empty($recordId))
			$recordId = $this->get('modcommentsid');
		$this->set('id',$recordId);
		$fileDetails = $this->getFileDetails($attachmentId);
		$attachmentDetails = array();
		if(!empty($fileDetails)){
			if(is_array($fileDetails[0])){
				foreach($fileDetails as $index => $fileDetail){
					if(!empty($fileDetail)){
						$rawFileName = $fileDetail['name'];
						$attachmentDetails[$index]['rawFileName'] = $rawFileName;
						$attachmentDetails[$index]['attachmentId'] = $fileDetail['attachmentsid'];
						$attachmentDetails[$index]['trimmedFileName'] = $this->trimFileName($rawFileName);
						$attachmentDetails[$index]['url'] = $site_URL.'ModComments/action/DownloadFile/'. $recordId .'?fileid='. $fileDetail['attachmentsid'];
					}
				}
			}
		}
		return $attachmentDetails;
	}

	/**
	 * Function trims the file name after 15 characters
	 * given in configuration editor
	 * @param string $fileName
	 * @return string
	 */
	public function trimFileName($fileName = false){
		if(!empty($fileName)){
			$fileDetails = explode('.',$fileName);
			$noOfParts = count($fileDetails);
			$fileExtension = $fileDetails[$noOfParts-1];
			$val = str_replace(".$fileExtension", '', $fileName);
			$field_val = $val;
			$listview_max_textlength = 50;
			global $default_charset;
				$temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val);
				if (function_exists('mb_strlen')) {
					if (mb_strlen(decode_html($temp_val)) > $listview_max_textlength) {
						$temp_val = mb_substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", decode_html($field_val)), 0, $listview_max_textlength, $default_charset) . '...';
					}
				} elseif (strlen(html_entity_decode($field_val)) > $listview_max_textlength) {
					$temp_val = substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength) . '...';
				}

			$fileName = $temp_val.'.'.$fileExtension;
		}
		return $fileName;
	}


	public function getImagePath() {
		$commentor = $this->getCommentedByModel();
		$isMailConverterType = $this->get('from_mailconverter');
		if($commentor) {
			$customer = $this->get('customer');
			if (!empty($customer)) {
				$recordModel = Head_Record_Model::getInstanceById($customer);
				$imageDetails = $recordModel->getImageDetails();
				if(!empty($imageDetails)) {
					return $imageDetails[0]['path'].'_'.$imageDetails[0]['name'];
				} else
					return vimage_path('CustomerPortal.png');
			} else {
				$imagePath = $commentor->getImageDetails();
				if (!empty($imagePath[0]['name'])) {
					return $imagePath[0]['path'] . '_' . $imagePath[0]['name'];
				}
			}
		} elseif ($isMailConverterType) {
			return vimage_path('MailConverterComment.png');
		}
		return false;
	}

	/**
	 * Function to create an instance of ModComment_Record_Model
	 * @param <Integer> $record
	 * @return ModComment_Record_Model
	 */
	public static function getInstanceById($record) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT jo_modcomments.*, jo_crmentity.smownerid,
					jo_crmentity.createdtime, jo_crmentity.modifiedtime FROM jo_modcomments
					INNER JOIN jo_crmentity ON jo_modcomments.modcommentsid = jo_crmentity.crmid
					WHERE modcommentsid = ? AND deleted = 0', array($record));
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, $i);
			$self = new self();
			$self->setData($row);
			return $self;
		}
		return false;
	}

	/**
	 * Function returns the parent Comment Model
	 * @return <Head_Record_Model>
	 */
	public function getParentCommentModel() {
		$recordId = $this->get('parent_comments');
		if(!empty($recordId))
			return ModComments_Record_Model::getInstanceById($recordId, 'ModComments');

		return false;
	}

	/**
	 * Function returns the parent Record Model(Contacts, Accounts etc)
	 * @return <Head_Record_Model>
	 */
	public function getParentRecordModel() {
		$parentRecordId = $this->get('related_to');
		if(!empty($parentRecordId))
		return Head_Record_Model::getInstanceById($parentRecordId);

		return false;
	}

	/**
	 * Function returns the commentor Model (Users Model)
	 * @return <Head_Record_Model>
	 */
	public function getCommentedByModel() {
		$customer = $this->get('customer');
		if(!empty($customer)) {
			try {
				return Head_Record_Model::getInstanceById($customer, 'Contacts');
			} catch(Exception $e) {
				return false;
			}
		} else {
			$commentedBy = $this->get('smownerid');
			if($commentedBy) {
				$commentedByModel = Head_Record_Model::getInstanceById($commentedBy, 'Users');
				if(empty($commentedByModel->entity->column_fields['user_name'])) {
				$activeAdmin = Users::getActiveAdminUser();
				$commentedByModel = Head_Record_Model::getInstanceById($activeAdmin->id, 'Users');
				}
				return $commentedByModel;
			}
		}
		return false;
	}

	/**
	 * Function returns the commented time
	 * @return <String>
	 */
	public function getCommentedTime() {
		$commentTime = $this->get('createdtime');
		return $commentTime;
	}

	/**
	 * Function returns the commented time
	 * @return <String>
	 */
	public function getModifiedTime() {
		$commentTime = $this->get('modifiedtime');
		return $commentTime;
	}
	/**
	 * Function returns latest comments for parent record
	 * @param <Integer> $parentRecordId - parent record for which latest comment need to retrieved
	 * @param <Head_Paging_Model> - paging model
	 * @return ModComments_Record_Model if exits or null
	 */
	public static function getRecentComments($parentRecordId, $pagingModel = false){
		$db = PearDatabase::getInstance();

		$listView = Head_ListView_Model::getInstance('ModComments');
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'assigned_user_id',
			'commentcontent', 'creator', 'id', 'customer', 'reasontoedit', 'userid', 'from_mailconverter', 'from_mailroom', 'is_private', 'customer_email', 'related_email_id', 'filename'));

		$query = $queryGenerator->getQuery();
		$query = $query ." AND related_to = ? ORDER BY jo_crmentity.createdtime DESC";

		if($pagingModel){
			$startIndex = $pagingModel->getStartIndex();
			$limit = $pagingModel->getPageLimit();
			$query = $query . " LIMIT $startIndex, $limit";
		}


		$result = $db->pquery($query, array($parentRecordId));
		$rows = $db->num_rows($result);

		for ($i=0; $i<$rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	/**
	 * Function returns all the parent comments model
	 * @param <Integer> $parentId
	 * @return ModComments_Record_Model(s)
	 */
	public static function getAllParentComments($parentId) {
			$db = PearDatabase::getInstance();
			$focus = CRMEntity::getInstance('ModComments');
			$query = $focus->get_comments();
			if($query) {
				$query .= " AND related_to = ? AND parent_comments = ? ORDER BY jo_crmentity.createdtime DESC";
				$result = $db->pquery($query, array($parentId, ''));
				$count = $db->num_rows($result);
				for($i = 0; $i < $count; $i++) {
					$rowData = $db->query_result_rowdata($result, $i);
					$recordInstance = new self();
					$recordInstance->setData($rowData);
					$recordInstances[] = $recordInstance;
				}

				return $recordInstances;
			} else {
				return array();
			}
	}

	/**
	 * Function returns all the child comment count
	 * @return <type>
	 */
	public function getChildCommentsCount() {
		$db = PearDatabase::getInstance();
		$parentRecordId = $this->get('related_to');

		$query = 'SELECT 1 FROM jo_modcomments WHERE parent_comments = ? AND related_to = ?';
		$result = $db->pquery($query, array($this->getId(), $parentRecordId));
		if($db->num_rows($result)) {
			return $db->num_rows($result);
		} else {
			return 0;
		}
	}

	/**
	 * Returns child comments models for a comment
	 * @return ModComment_Record_Model(s)
	 */
	public function getChildComments() {
		$db = PearDatabase::getInstance();
		$parentCommentId = $this->get('modcommentsid');

		if(empty($parentCommentId)) return;

		$parentRecordId = $this->get('related_to');

		$listView = Head_ListView_Model::getInstance('ModComments');
		$queryGenerator = $listView->get('query_generator');
		$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'id', 'assigned_user_id',
			'commentcontent', 'creator', 'reasontoedit', 'userid', 'from_mailconverter', 'from_mailroom','is_private', 'customer_email'));
		$query = $queryGenerator->getQuery();

		//Condition are directly added as query_generator transforms the
		//reference field and searches their entity names
		$query = $query. ' AND parent_comments = ? AND related_to = ?';

		$recordInstances = array();
		$result = $db->pquery($query, array($parentCommentId, $parentRecordId));
		$rows = $db->num_rows($result);
		for ($i=0; $i<$rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$recordInstance = new self();
			$recordInstance->setData($row);
			$recordInstances[] = $recordInstance;
		}

		return $recordInstances;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		return false;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isDeletable() {
		return false;
	}

	public function getCommentedByName() {
		$customer = $this->get('customer');
		$customerEmail = $this->get('customer_email');
		$fromMailConverter = $this->get('from_mailconverter');
		$fromMailroom = $this->get('from_mailroom');
		if($customer && !empty($customer)) {
			$label = Head_Util_Helper::getRecordName($customer);
			if(!$label)
				$label = $customerEmail;
			return $label;
		} else if(($fromMailConverter || $fromMailroom) && $customerEmail && !empty($customerEmail)) {
			return $customerEmail;
		} else {
			$commentedByModel = $this->getCommentedByModel();
			return $commentedByModel->getName();
		}
	}

	/**
	 * Function to get all comments related to record $parentId
	 * @param <Integer> $parentId
	 * @return ModComments_Record_Model(s)
	 */
	static function getAllComments($parentId) {
		$db = PearDatabase::getInstance();
		$focus = CRMEntity::getInstance('ModComments');
		$query = $focus->get_comments();
		if($query) {
			$query .= " AND related_to = ? ORDER BY jo_crmentity.createdtime DESC";
			$result = $db->pquery($query, array($parentId));
			$count = $db->num_rows($result);
			for($i = 0; $i < $count; $i++) {
				$rowData = $db->query_result_rowdata($result, $i);
				$recordInstance = new self();
				$recordInstance->setData($rowData);
				$recordInstances[] = $recordInstance;
			}

			return $recordInstances;
		} else {
			return array();
		}
	}

	function uploadAndSaveFile($emailId,$attachmentId) {
		$db = PearDatabase::getInstance();
		$db->pquery('INSERT INTO jo_seattachmentsrel(crmid,attachmentsid) VALUES(?,?)',array($emailId,$attachmentId));
	}

	function getAttachmentDetails() {
		$db = PearDatabase::getInstance();

		$attachmentRes = $db->pquery("SELECT * FROM jo_attachments
						INNER JOIN jo_seattachmentsrel ON jo_attachments.attachmentsid = jo_seattachmentsrel.attachmentsid
						WHERE jo_seattachmentsrel.crmid = ?", array($this->getId()));
		$numOfRows = $db->num_rows($attachmentRes);
		$attachmentsList = array();
		if($numOfRows) {
			for($i=0; $i<$numOfRows; $i++) {
				$attachmentsList[$i]['fileid'] = $db->query_result($attachmentRes, $i, 'attachmentsid');
				$attachmentsList[$i]['attachment'] = decode_html($db->query_result($attachmentRes, $i, 'name'));
				$path = $db->query_result($attachmentRes, $i, 'path');
				$attachmentsList[$i]['path'] = $path;
				$attachmentsList[$i]['size'] = filesize($path.$attachmentsList[$i]['fileid'].'_'.$attachmentsList[$i]['attachment']);
				$attachmentsList[$i]['type'] = $db->query_result($attachmentRes, $i, 'type');
				$attachmentsList[$i]['cid'] = $db->query_result($attachmentRes, $i, 'cid');
			}
		}
		return $attachmentsList;
	}

	public function getParsedContent(){
		require_once 'modules/Settings/MailConverter/handlers/MailParser.php';
		$htmlParser = new Head_MailParser($this->getName());
		return $htmlParser->parseHtml();
	}
}
