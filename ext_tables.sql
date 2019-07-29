CREATE TABLE tx_uniquealiasmapper (
	uid int(11) NOT NULL auto_increment,
	updatedon int(11) DEFAULT '0' NOT NULL,
	tablename varchar(255) DEFAULT '' NOT NULL,
	# known from RealURL as "field_alias"
	aliasfieldname varchar(255) DEFAULT '' NOT NULL,
	# known from RealURL as "value_alias"
	aliasvalue varchar(255) DEFAULT '' NOT NULL,
	# known from RealURL as "value_id"
	recorduid int(11) DEFAULT '0' NOT NULL,
	# known from RealURL as "lang"
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	# known from RealURL as "expire"
	expireson int(11) DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY tablename (tablename),
	KEY resolverkey (aliasfieldname(20),recorduid,sys_language_uid,expireson),
	KEY resolverkey2 (tablename(32),aliasfieldname(20),aliasvalue(20),expireson)
);
