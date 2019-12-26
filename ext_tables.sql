#
# Table structure for table 'tx_ffpinodeupdates_domain_model_node'
#
CREATE TABLE tx_ffpinodeupdates_domain_model_node (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	node_id varchar(255) DEFAULT '' NOT NULL,
	node_name VARCHAR(255) DEFAULT '' NOT NULL,
	role VARCHAR(255) DEFAULT '' NOT NULL,
	online tinyint(1) unsigned DEFAULT '0' NOT NULL,
	last_change int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);

#
# Table structure for table 'tx_ffpinodeupdates_domain_model_abo'
#
CREATE TABLE tx_ffpinodeupdates_domain_model_abo (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	email varchar(255) DEFAULT '' NOT NULL,
	confirmed tinyint(1) unsigned DEFAULT '0' NOT NULL,
	last_notification int(11) DEFAULT '0' NOT NULL,
	secret varchar(255) DEFAULT '' NOT NULL,
	node int(11) unsigned DEFAULT '0',

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_ffpinodeupdates_domain_model_abo'
#
CREATE TABLE tx_ffpinodeupdates_domain_model_gateway (
    node int(11) unsigned DEFAULT '0',

    http_adress varchar(255) DEFAULT '' NOT NULL,
    ping float unsigned default NULL,
    open_vpn int(1) DEFAULT '0',
    network_interface int(1) DEFAULT '0',
    firewall int(1) DEFAULT '0',
    exit_vpn  int(1) DEFAULT '0',
    last_health_check int(11) DEFAULT '0' NOT NULL,
    last_health_change int(11) DEFAULT '0' NOT NULL
);