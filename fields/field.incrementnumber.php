<?php
	
	Class fieldIncrementNumber extends Field {
		
		function __construct(){
			parent::__construct();
			$this->_name = 'Increment Number';
			$this->_required = TRUE;
			$this->set('required', 'no');
		}
		
	/*-------------------------------------------------------------------------
		Setup:
	-------------------------------------------------------------------------*/
		
		public function isSortable() {
			return true;
		}

		public function canFilter() {
			return true;
		}

		public function allowDatasourceOutputGrouping() {
			return true;
		}

		public function allowDatasourceParamOutput() {
			return true;
		}

		public function canPrePopulate() {
			return true;
		}
		
		function canToggle(){
			return ($this->get('developers_only') !== 'yes' || (Symphony::Engine()->Author instanceof Author && Symphony::Engine()->Author->isDeveloper()) ? true : false);
		}
		
		public function createTable() {
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `value` double default 0,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `value` (`value`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
			);
		}
		
		function getToggleStates(){
			return array('0' => __('Reset to 0'));
		}
		
		public function fetchIncludableElements(){
			return array(
				$this->get('element_name') . ': value only',
				$this->get('element_name') . ': increment'
			);
		}
		
		function toggleFieldData($data, $value){
			$data['value'] = $value;
			return $data;
		}

		public function commit(){
			if(!parent::commit()) return false;

			$id = $this->get('id');

			if($id === false) return false;

			$fields = array();

			$fields['field_id'] = $id;
			$fields['developers_only'] = ($this->get('developers_only') ? $this->get('developers_only') : 'no');

			Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
			return Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle());
		}
		
		function appendFormattedElement(&$wrapper, $data, $encode=FALSE, $mode=NULL, $entry_id=NULL){
			if(!is_array($data) || empty($data)) return;
			
			$value = (int) $data['value'];
			
			if($mode == NULL) $mode = 'increment';
			
			if (Symphony::Engine() instanceof Frontend && $mode == 'increment') {
				$value = ++$value;
				$entry_id = $wrapper->getAttribute('id');
				Symphony::Database()->update(
					array('value' => $value),
					"tbl_entries_data_{$this->_fields['id']}",
					"entry_id={$entry_id}"
				);
			}
			
			$increment_number = new XMLElement($this->get('element_name'), $value);
			$wrapper->appendChild($increment_number);
		}

		function displaySettingsPanel(&$wrapper, $errors=NULL) {
			parent::displaySettingsPanel($wrapper, $errors);
			
			$div = new XMLElement('div', NULL, array('class' => 'compact'));
			$label = Widget::Label();
			$input = Widget::Input( 'fields['.$this->get('sortorder').'][developers_only]', 'yes', 'checkbox');
			if ($this->get('developers_only') == 'yes') $input->setAttribute('checked', 'checked');
			$label->setValue(__('%s Value editable by developers only', array($input->generate())));
			$div->appendChild($label);
			
			parent::appendShowColumnCheckbox($div);
			
			$wrapper->appendChild($div);
		}

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			$value = $data['value'];		
			$label = Widget::Label($this->get('label'));

			$readonly = ($this->get('developers_only') != 'yes' || (Symphony::Engine()->Author instanceof Author && Symphony::Engine()->Author->isDeveloper()) ? false : true);

			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', 'Optional'));
			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, (string)(strlen($value) !== 0 ? $value : 0), 'text', ($readonly ? array('readonly' => 'readonly') : array())));

			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			} else {
				$wrapper->appendChild($label);
			}
		}
		
		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$message = NULL;

			if($this->get('required') == 'yes' && strlen($data) == 0) {
				$message = __('This is a required field.');
				return self::__MISSING_FIELDS__;
			}

			if(strlen($data) > 0 && !is_numeric($data)) {
				$message = __('Must be a number.');
				return self::__INVALID_FIELDS__;
			}

			return self::__OK__;
		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate=false, $entry_id=null) {

			$status = self::__OK__;

			if ($this->get('developers_only') == 'yes' && (!(Symphony::Engine()->Author instanceof Author) || !Symphony::Engine()->Author->isDeveloper())) {
				if (!empty($entry_id)) {
					$data = Symphony::Database()->fetchVar('value', 0, "SELECT `value` FROM `tbl_entries_data_" . $this->get('id') . "` WHERE `entry_id` = '$entry_id' LIMIT 1");
				}
				else {
					$data = 0;
				}
			}

			return array(
				'value' => $data,
			);
		}
		
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/

		public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false) {

			// X to Y support
			if(preg_match('/^(-?(?:\d+(?:\.\d+)?|\.\d+)) to (-?(?:\d+(?:\.\d+)?|\.\d+))$/i', $data[0], $match)) {

				$field_id = $this->get('id');

				$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id` ON (`e`.`id` = `t$field_id`.entry_id) ";
				$where .= " AND `t$field_id`.`value` BETWEEN {$match[1]} AND {$match[2]} ";

			}

			// Equal to or less/greater than X
			else if(preg_match('/^(equal to or )?(less|greater) than (-?(?:\d+(?:\.\d+)?|\.\d+))$/i', $data[0], $match)) {
				$field_id = $this->get('id');

				$expression = " `t$field_id`.`value` ";

				switch($match[2]) {
					case 'less':
						$expression .= '<';
						break;

					case 'greater':
						$expression .= '>';
						break;
				}

				if($match[1]){
					$expression .= '=';
				}

				$expression .= " {$match[3]} ";

				$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id` ON (`e`.`id` = `t$field_id`.entry_id) ";
				$where .= " AND $expression ";

			}

			else parent::buildDSRetrievalSQL($data, $joins, $where, $andOperation);

			return true;
		}

	/*-------------------------------------------------------------------------
		Grouping:
	-------------------------------------------------------------------------*/

		public function groupRecords($records) {
			if(!is_array($records) || empty($records)) return;

			$groups = array($this->get('element_name') => array());

			foreach($records as $r) {
				$data = $r->getData($this->get('id'));

				$value = $data['value'];

				if(!isset($groups[$this->get('element_name')][$value])) {
					$groups[$this->get('element_name')][$value] = array(
						'attr' => array('value' => $value),
						'records' => array(),
						'groups' => array()
					);
				}

				$groups[$this->get('element_name')][$value]['records'][] = $r;

			}

			return $groups;
		}
	
	}