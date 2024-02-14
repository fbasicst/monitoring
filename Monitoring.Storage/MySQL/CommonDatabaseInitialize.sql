/*First create database, then execute other queries on it*/
CREATE DATABASE IF NOT EXISTS monitoring_common COLLATE=utf8_unicode_ci;

CREATE USER 'monitoring'@'monitoring' IDENTIFIED BY 'monitoring';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, SHUTDOWN, PROCESS, FILE, REFERENCES, INDEX, ALTER, SHOW DATABASES, SUPER, CREATE TEMPORARY TABLES, EXECUTE, SHOW VIEW ON *.* TO 'monitoring'@'monitoring';
GRANT SELECT, INSERT, UPDATE, REFERENCES, DELETE, CREATE, DROP, ALTER, INDEX, TRIGGER, CREATE VIEW, SHOW VIEW, EXECUTE, ALTER ROUTINE, CREATE ROUTINE, CREATE TEMPORARY TABLES, LOCK TABLES, EVENT ON `monitoring\_common`.* TO 'monitoring'@'monitoring';
GRANT GRANT OPTION ON `monitoring\_common`.* TO 'monitoring'@'monitoring';

CREATE TABLE monitoring_common.environments 
(
	id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
  name varchar(20) NOT NULL,
  comment varchar(20),
	accounting_name varchar(50),
	remote_name varchar(50)
);

CREATE TABLE monitoring_common.users 
(
	id int AUTO_INCREMENT PRIMARY KEY NOT NULL,
    firstname varchar(50) NOT NULL,
    lastname varchar(50) NOT NULL,
    username varchar(50) NOT NULL,
    password varchar(32) NOT NULL,
    environmentid int NOT NULL,
    profilephotopath varchar(100),
	CONSTRAINT usernameunique UNIQUE (username),
    CONSTRAINT userenvironments FOREIGN KEY (environmentid)
		REFERENCES monitoring_common.environments (id) 
        ON DELETE NO ACTION
        ON UPDATE NO ACTION	
);

CREATE TABLE monitoring_common.tokens
(
	id bigint AUTO_INCREMENT PRIMARY KEY NOT NULL,
	token varchar(100),
	userid int NOT NULL,
	/*todo dodati i expiration date*/
	CONSTRAINT usertoken FOREIGN KEY (userid)
		REFERENCES monitoring_common.users (id) 
		ON DELETE NO ACTION 
		ON UPDATE NO ACTION,
	CONSTRAINT uniqueuserid UNIQUE (userid)
);


CREATE TABLE userroles
(
	id smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
	enumdescription varchar(50) NOT NULL,
	description varchar(50),
	CONSTRAINT nameunique UNIQUE(enumdescription)
);

CREATE TABLE userrolesrel
(
	userid int NOT NULL,
	userroleid smallint NOT NULL,
	CONSTRAINT useridusers FOREIGN KEY(userid)
		REFERENCES users(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT userroleiduserroles FOREIGN KEY(userroleid)
		REFERENCES userroles(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);

CREATE TABLE userfunctions
(
	id smallint PRIMARY KEY AUTO_INCREMENT NOT NULL,
	enumdescription varchar(50) NOT NULL,
	description varchar(100)
);

CREATE TABLE userfunctionsrolesrel
(
	id smallint PRIMARY KEY AUTO_INCREMENT NOT NULL,
	userfunctionid smallint NOT NULL,
	userroleid smallint NOT NULL,
	CONSTRAINT userfunctionsrolesreluserfunctions FOREIGN KEY(userfunctionid)
	REFERENCES userfunctions(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION,
	CONSTRAINT userfunctionsrolesreluserroles FOREIGN KEY(userroleid)
	REFERENCES userroles(id)
		ON UPDATE NO ACTION
		ON DELETE NO ACTION
);




