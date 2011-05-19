<?php
	
	require_once(EXTENSIONS . '/numberfield/fields/field.number.php');
	
	Class fieldIncrementNumber extends fieldNumber {
		
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = 'Increment Number';
			$this->_required = TRUE;
		}
		
		function canToggle(){
			return ($this->get('developers_only') != 'yes' || (Symphony::Engine()->Author instanceof Author && Symphony::Engine()->Author->isDeveloper()) ? true : false);
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

			$wrapper->appendChild($div);
		}

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			$value = $data['value'];		
			$label = Widget::Label($this->get('label'));

			$readonly = ($this->get('developers_only') != 'yes' || (Symphony::Engine()->Author instanceof Author && Symphony::Engine()->Author->isDeveloper()) ? false : true);

			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', 'Optional'));
			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, (strlen($value) != 0 ? $value : 0), 'text', ($readonly ? array('readonly' => 'readonly') : array())));

			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			} else {
				$wrapper->appendChild($label);
			}
		}

		public function processRawFieldData($data, &$status, $simulate=false, $entry_id=null) {

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

	}