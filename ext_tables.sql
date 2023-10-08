#
# Add fields to table 'fe_users'
#
CREATE TABLE fe_users
(
	easyverein_pk     varchar(255)         NOT NULL DEFAULT '',
	welcome_mail      smallint(1) unsigned NOT NULL DEFAULT '0',
	welcome_mail_sent bigint(20)           NOT NULL DEFAULT '0'
);

#
# Add fields to table 'fe_groups'
#
CREATE TABLE fe_groups
(
	easyverein_g_short varchar(10) NOT NULL DEFAULT ''
);
