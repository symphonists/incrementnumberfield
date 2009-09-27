<?php
	
	require_once(EXTENSIONS . '/numberfield/fields/field.number.php');
	
	Class fieldIncrementNumber extends fieldNumber {
		
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = 'Increment Number';
			$this->_required = true;
		}
		
		function appendFormattedElement(&$wrapper, $data, $encode=false){

			if(!is_array($data) || empty($data)) return;
			
			$value = (int) $data["value"];
			
			if ($this->_Parent->_Parent instanceof Frontend) {
				$value = ++$value;				
				$entry_id = $wrapper->getAttribute("id");
				$this->Database->update(
					array("value" => $value),
					"tbl_entries_data_{$this->_fields['id']}",
					"entry_id={$entry_id}"
				);
			}
			
			$increment_number = new XMLElement($this->get('element_name'), $value);
			$wrapper->appendChild($increment_number);

		}
		
		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			$value = $data['value'];		
			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', 'Optional'));
			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, (strlen($value) != 0 ? $value : 0)));

			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label);
		}

	}