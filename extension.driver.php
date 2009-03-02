<?php

	Class extension_incrementnumberfield extends Extension{
	
		public function about(){
			return array('name' => 'Field: Increment Number',
						 'version' => '1.0',
						 'release-date' => '2008-12-19',
						 'author' => array('name' => 'Nick Dunn',
										   'website' => 'http://airlock.com',
										   'email' => 'nick.dunn@airlock.com')
				 		);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_incrementnumber`");
		}


		public function install(){
			return $this->_Parent->Database->query("CREATE TABLE `tbl_fields_incrementnumber` (
			  `id` int(11) unsigned NOT NULL auto_increment,
			  `field_id` int(11) unsigned NOT NULL,
			  PRIMARY KEY  (`id`),
			  UNIQUE KEY `field_id` (`field_id`)
			) TYPE=MyISAM");
		}
			
	}