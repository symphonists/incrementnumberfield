<?php

	Class extension_incrementnumberfield extends Extension{
	
		public function about(){
			return array('name' => 'Field: Increment Number',
						 'version' => '1.1',
						 'release-date' => '2011-02-08',
						 'author' => array('name' => 'Nick Dunn')
				 		);
		}
		
		public function uninstall(){
			Symphony::Database()->query("DROP TABLE `tbl_fields_incrementnumber`");
		}


		public function install(){
			
			if(!file_exists(EXTENSIONS . '/numberfield/fields/field.number.php')) {
				Administration::instance()->Page->pageAlert(
					__('Increment Number field could not be installed because the Number field needs to be installed first.'),
					Alert::ERROR
				);
				return false;
			}
			
			return Symphony::Database()->query("CREATE TABLE `tbl_fields_incrementnumber` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `field_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`),
			  UNIQUE KEY `field_id` (`field_id`)
			) TYPE=MyISAM");
		}
			
	}