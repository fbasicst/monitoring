/*First create database, then execute other queries on it*/
CREATE DATABASE IF NOT EXISTS monitoring_test COLLATE=utf8_unicode_ci;

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, SHUTDOWN, PROCESS, FILE, REFERENCES, INDEX, ALTER, SHOW DATABASES, SUPER, CREATE TEMPORARY TABLES, EXECUTE, SHOW VIEW ON *.* TO 'monitoring'@'monitoring';
GRANT SELECT, INSERT, UPDATE, REFERENCES, DELETE, CREATE, DROP, ALTER, INDEX, TRIGGER, CREATE VIEW, SHOW VIEW, EXECUTE, ALTER ROUTINE, CREATE ROUTINE, CREATE TEMPORARY TABLES, LOCK TABLES, EVENT ON `monitoring\_test`.* TO 'monitoring'@'monitoring';
GRANT GRANT OPTION ON `monitoring\_test`.* TO 'monitoring'@'monitoring';

CREATE TABLE areas
(
	id smallint AUTO_INCREMENT PRIMARY KEY NOT NULL,
	name varchar(50) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE objecttypes 
(
	id smallint AUTO_INCREMENT PRIMARY KEY NOT NULL,
	name varchar(50) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE cities 
(
	id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
	name varchar(50) NOT NULL,
	postalcode int NOT NULL,
	post varchar(50) NOT NULL
);

CREATE TABLE customers
(
	id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
	remoteid varchar(10) NOT NULL,
	name varchar(100),
	oib varchar(11),
	address varchar(100),
	postalcode varchar(5),
	streetnumber varchar(5),
	postname varchar(50),
	active bool NOT NULL DEFAULT true,
	CONSTRAINT remoteidunique UNIQUE(remoteid)
);

CREATE TABLE contracts
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	remoteid int,
	barcode bigint NOT NULL,
	customerid int NOT NULL,
	conclusiondate date NOT NULL,
	startdate date NOT NULL,
	enddate date,
	class varchar(30),
	docketnumber varchar(30),
	active bool NOT NULL,
	CONSTRAINT contractscustomers FOREIGN KEY(customerid)
		REFERENCES customers(id) 
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT barcodeunique UNIQUE (barcode)
);

CREATE TABLE objects
(
	id int auto_increment PRIMARY KEY NOT NULL,
	customerid int NOT NULL,
	contractid bigint,
	annexid bigint,
	name varchar(50) NOT NULL,
	streetname varchar(50),
	streetnumber varchar(5),
	cityid int NOT NULL,
	googleplaceid varchar(255),
	areaid smallint NOT NULL,
	objecttypeid smallint NOT NULL,
	contactpersonname varchar(50),
	contactpersonphone varchar(20),
	contactpersonemail varchar(50),
	notes varchar(500),
	useridinsert int NOT NULL,
	datetimeinsert datetime NOT NULL,
	active bool NOT NULL DEFAULT TRUE,
	CONSTRAINT objectcity FOREIGN KEY(cityid)
		REFERENCES cities(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectcustomer FOREIGN KEY(customerid)
		REFERENCES customers(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectarea FOREIGN KEY(areaid)
		REFERENCES areas(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectobjecttype FOREIGN KEY(objecttypeid)
		REFERENCES objecttypes(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectcontract FOREIGN KEY(contractid)
		REFERENCES contracts(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectuseridinsert FOREIGN KEY(useridinsert)
		REFERENCES monitoring_common.users(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION
);

CREATE TABLE objectitems 
(
	id int AUTO_INCREMENT NOT NULL PRIMARY KEY, 
	name varchar(50) NOT NULL, 
	seasonal bool NOT NULL,
	sublocation varchar(50),
	objectid int NOT NULL,
	active bool NOT NULL DEFAULT true,
	useridinsert int NOT NULL,
	datetimeinsert datetime NOT NULL,
	CONSTRAINT objectobjectitem FOREIGN KEY(objectid)
	REFERENCES objects(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemuseridinsert FOREIGN KEY(useridinsert)
		REFERENCES monitoring_common.users(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION
);

CREATE TABLE contractservicetype
(
	id tinyint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	statusname varchar(50) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE services 
(
	id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
	name varchar(50) NOT NULL,
	active bool NOT NULL DEFAULT true 
);


CREATE TABLE analysis
(
	id smallint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	name varchar(50) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE serviceitems 
(
	id int AUTO_INCREMENT NOT NULL PRIMARY KEY,
	name varchar(50) NOT NULL,
	serviceid int NOT NULL,
	active bool NOT NULL DEFAULT true,
	CONSTRAINT servicesserviceitem FOREIGN KEY(serviceid)
		REFERENCES services(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION 
);

CREATE TABLE objectitemmonitorings
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	contractservicetypeid tinyint NOT NULL,
	serviceitemid int NOT NULL,
	quantity tinyint NOT NULL,
	description varchar(200),
	objectitemid int NOT NULL,
	useridinsert int NOT NULL,
	datetimeinsert datetime NOT NULL,
	active bool NOT NULL DEFAULT TRUE,
	CONSTRAINT objectitemmonitoringcontractservicetype FOREIGN KEY(contractservicetypeid)
		REFERENCES contractservicetype(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemmonitoringserviceitem FOREIGN KEY(serviceitemid)
		REFERENCES serviceitems(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemmonitoringobjectitems FOREIGN KEY(objectitemid)
		REFERENCES objectitems(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemmonitoringuseridinsert FOREIGN KEY(useridinsert)
		REFERENCES monitoring_common.users(id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION
);

CREATE TABLE objectitemmonitoringanalysisrel
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	objectitemmonitoringid bigint NOT NULL,
	analysisid smallint NOT NULL,
	CONSTRAINT obitemmonanalysisrelobitemmon FOREIGN KEY(objectitemmonitoringid)
		REFERENCES objectitemmonitorings(id)
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT obitemmonanalysisrelanalysis FOREIGN KEY(analysisid)
		REFERENCES analysis(id)
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION		
);

CREATE TABLE schedulelevels
(
	id tinyint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	description varchar(20) NOT NULL,
	enumdescription varchar(20) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE objectitemplans
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	validfurther bool NOT NULL,
	enddate date,
	schedulelevelid tinyint NOT NULL,
	monthlyrepeats smallint NOT NULL DEFAULT 1,
	objectitemmonitoringid bigint NOT NULL,
	objectitemid int NOT NULL,
	CONSTRAINT objectitemplanobjectitem FOREIGN KEY(objectitemid)
		REFERENCES objectitems(id)
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemplanschedulelevel FOREIGN KEY(schedulelevelid)
		REFERENCES schedulelevels(id)
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT objectitemplanmmonitoring FOREIGN KEY(objectitemmonitoringid)
		REFERENCES objectitemmonitorings(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE objectitemplanschedules
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	schedulemonth tinyint,
	scheduledate date,
	objectitemplanid bigint NOT NULL,
	CONSTRAINT objectitemplanscheduleobjectitemplan FOREIGN KEY(objectitemplanid)
		REFERENCES objectitemplans(id)
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION
);

CREATE TABLE planstatuses
(
	id smallint AUTO_INCREMENT PRIMARY KEY NOT NULL,
	description varchar(20) NOT NULL,
	enumdescription varchar(20) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE planlevels
(
	id smallint AUTO_INCREMENT PRIMARY KEY NOT NULL,
	description varchar(20) NOT NULL,
	enumdescription varchar(20) NOT NULL,
	label varchar(20) NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE plans
(
	id bigint AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	startdate date NOT NULL,
	enddate date NOT NULL,
	month smallint NOT NULL,
	year smallint NOT NULL,
	planlevelid smallint NOT NULL,
	useridinsert int NOT NULL,
	useridcontrolled int NOT NULL,
	daysamount varchar(5) NOT NULL,
	objectsamount varchar(5) NOT NULL, 
	label varchar(50),
	locked bool NOT NULL DEFAULT false,
	uploaded bool NOT NULL DEFAULT false,
	datetimeinsert datetime NOT NULL,
	CONSTRAINT planplanlevel FOREIGN KEY(planlevelid) 
		REFERENCES planlevels(id) 
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planuserinsert FOREIGN KEY(useridinsert) 
		REFERENCES monitoring_common.users(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planusercontrolled FOREIGN KEY(useridcontrolled) 
		REFERENCES monitoring_common.users(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE nonworkingdays
(
	nonworkingdate date NOT NULL,
	active bool NOT NULL DEFAULT true
);

CREATE TABLE planusersrel
(
	id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
	planid bigint NOT NULL,
	userid int NOT NULL,
	CONSTRAINT planuserrelplans FOREIGN KEY(planid)
		REFERENCES plans(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planisersrelusers FOREIGN KEY(userid)
		REFERENCES monitoring_common.users(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE planitems
(
	id bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
	planid bigint NOT NULL,
	objectid int NOT NULL,
	scheduledate date NOT NULL,
	planstatusid smallint NOT NULL,
	notes varchar(500),
	useridinsert int NOT NULL,
	datetimeinsert datetime NOT NULL,
	finishnotes varchar(500),
	CONSTRAINT planobjectsplan FOREIGN KEY(planid)
	REFERENCES plans(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planobjectobject FOREIGN KEY(objectid)
	REFERENCES objects(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planobjectplanstatus FOREIGN KEY(planstatusid)
	REFERENCES planstatuses(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planobjectuser FOREIGN KEY(useridinsert)
	REFERENCES monitoring_common.users(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE planitemobjectitems
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	objectitemid int NOT NULL,
	name varchar(50) NOT NULL,
	seasonal bool NOT NULL,
	sublocation varchar(50),
	planitemid bigint NOT NULL,
	CONSTRAINT planioiobjectitem FOREIGN KEY(planitemid)
	REFERENCES planitems(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT planitemobjectitemsobjectitem FOREIGN KEY (objectitemid)
	REFERENCES objectitems(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE planitemobjectitemmonitorings
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	objectitemmonitoringid bigint NOT NULL,
	contractservicetypeid tinyint NOT NULL,
	serviceitemid int NOT NULL,
	quantity tinyint NOT NULL,
	description varchar(200),
	planitemobjectitemid bigint NOT NULL,
	CONSTRAINT pioitemmonitoringcontractservicetype FOREIGN KEY(contractservicetypeid)
	REFERENCES contractservicetype(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT piobjectitemmonitoringserviceitem FOREIGN KEY(serviceitemid)
	REFERENCES serviceitems(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT piobjectitemmonitoringobjectitems FOREIGN KEY(planitemobjectitemid)
	REFERENCES planitemobjectitems(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT planitemobjectitemmonitoringsobjectitemmonitoring FOREIGN KEY (objectitemmonitoringid)
	REFERENCES objectitemmonitorings(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE planitemobjectitemmonitoringanalysisrel
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	planitemobjectitemmonitoringid bigint NOT NULL,
	analysisid smallint NOT NULL,
	CONSTRAINT planitemobitemmonanalysisrelobitemmon FOREIGN KEY(planitemobjectitemmonitoringid)
	REFERENCES planitemobjectitemmonitorings(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT planitemobitemmonanalysisrelanalysis FOREIGN KEY(analysisid)
	REFERENCES analysis(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);

CREATE TABLE planitemobjectitemplans
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	objectitemplanid bigint NOT NULL,
	validfurther bool NOT NULL,
	enddate date,
	schedulelevelid tinyint NOT NULL,
	monthlyrepeats smallint NOT NULL,
	planitemobjectitemmonitoringid bigint NOT NULL,
	planitemobjectitemid bigint NOT NULL,
	CONSTRAINT planitemobjectitemplanobjectitem FOREIGN KEY(planitemobjectitemid)
	REFERENCES planitemobjectitems(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT planitemobjectitemplanschedulelevel FOREIGN KEY(schedulelevelid)
	REFERENCES schedulelevels(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION,
	CONSTRAINT planitemobjectitemplanmmonitoring FOREIGN KEY(planitemobjectitemmonitoringid)
	REFERENCES planitemobjectitemmonitorings(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT planitemobjectitemplansobjectitemplan FOREIGN KEY (objectitemplanid)
	REFERENCES objectitemplans(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE planitemobjectitemplanschedules
(
	id bigint AUTO_INCREMENT NOT NULL PRIMARY KEY,
	schedulemonth tinyint,
	scheduledate date,
	planitemobjectitemplanid bigint NOT NULL,
	CONSTRAINT planitemobjectitemplanscheduleobjectitemplan FOREIGN KEY(planitemobjectitemplanid)
	REFERENCES planitemobjectitemplans(id)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
);

CREATE TABLE planitemmonthlyschedules
(
	id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	objectid int NOT NULL,
	assignednumber smallint NOT NULL,
	completednumber smallint NOT NULL,
	month smallint NOT NULL,
	year smallint NOT NULL,
	CONSTRAINT objectplanschedulemonthunique UNIQUE(objectid, month, year),
	CONSTRAINT planitemmonthlyscheduleobject FOREIGN KEY(objectid)
	REFERENCES objects(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

