<?php

	// Init
	$sql = array();

	// Create Service Table in Database
	$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'k21_pesapal` (
			  `id_customer` VARCHAR(11) NOT NULL,
			  `token` varchar(32) NOT NULL,
			  PRIMARY KEY  (`id_customer`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
