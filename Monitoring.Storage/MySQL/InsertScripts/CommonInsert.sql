INSERT INTO environments (name, comment, accounting_name, remote_name)
VALUES ('monitoring_test', 'Test', 'accounting_test', 'monitoring_remote_test');

INSERT INTO environments (name, comment, accounting_name, remote_name)
VALUES ('monitoring_prod', 'Produkcija', 'accounting_prod', 'monitoring_remote_prod');

INSERT INTO userroles(enumdescription, description)
VALUES
('SUPER_ADMIN','Super admin'),
('LEAD_MONITORING','Voditelj odsjeka'),
('SAMPLER_MONITORING','Uzorkivač');

/*uzorkivači*/
INSERT INTO monitoring_common.users ( firstname, lastname, username, password, environmentid )
VALUES
( 'Tomislav', 'Maleš', 'tmales', md5('tmales987!'), 2),
( 'Tomislav', 'Maleš', 'tmales_test', md5('test'), 1),
( 'Mirna', 'Zwirn', 'mzwirn', md5('mzwirn987!'), 2),
( 'Mendi', 'Ribarević', 'mribarevic', md5('mribarevic987!'), 2),
( 'Danijela', 'Ribarević', 'dribarevic', md5('dribarevic987!'), 2),
( 'Olga', 'Glavina', 'oglavina', md5('oglavina987!'), 2),
( 'Mara', 'Mustapić', 'mmustapic', md5('mmustapic987!'), 2),
( 'Hrvoje', 'Čipčić', 'hcipcic', md5('hcipcic987!'), 2),
( 'Radoslav', 'Maleš', 'rmales', md5('rmales987!'), 2),
( 'Toni', 'Karačić', 'tkaracic', md5('tkaracic987!'), 2),
( 'Roko', 'Peračić', 'rperacic', md5('rperacic987!'), 2),
( 'Ante', 'Ivanović', 'aivanovic', md5('aivanovic987!'), 2),
( 'Nada', 'Punda', 'npunda', md5('npunda987!'), 2),
( 'Ivica', 'Norac', 'inorac', md5('inorac987!'), 2),
( 'Kristian', 'Domazet', 'kdomazet', md5('kdomazet987!'), 2),
( 'Anđelina', 'Ajduković', 'aajdukovic', md5('aajdukovic987!'), 2),
( 'Mirela', 'Blagojević', 'mblagojevic', md5('mblagojevic987!'), 2),
( 'Tina', 'Maršić', 'tmarsic', md5('tmarsic987!'), 2),
( 'Sanja', 'Škorput', 'sskorput', md5('sskorput987!'), 2);

/* 3 mora biti 'SAMPLER_MONITORING' id */
INSERT INTO userrolesrel
SELECT U.id, '3'
FROM users U
WHERE
OR U.username = 'mribarevic'
OR U.username = 'dribarevic'
OR U.username = 'oglavina'
OR U.username = 'mmustapic'
OR U.username = 'hcipcic'
OR U.username = 'rmales'
OR U.username = 'tkaracic'
OR U.username = 'rperacic'
OR U.username = 'aivanovic'
OR U.username = 'npunda'
OR U.username = 'inorac'
OR U.username = 'kdomazet'
OR U.username = 'aajdukovic'
OR U.username = 'mblagojevic'
OR U.username = 'tmarsic'
OR U.username = 'sskorput';

/* 2 mora biti 'LEAD_MONITORING' id */
INSERT INTO userrolesrel
SELECT U.id, '2'
FROM users U
WHERE
U.username = 'tmales'
OR U.username = 'mzwirn'
OR U.username = 'tmales_test';

INSERT INTO userfunctions (enumdescription)
VALUES
('MASTERDATA_READ'),
('OBJECTS_READ'),
('OBJECTS_WRITE'),
('PLANS_READ'),
('PLANS_WRITE');

INSERT INTO userfunctionsrolesrel(userfunctionid, userroleid)
VALUES
(1,2),
(2,2),
(3,2),
(4,2),
(5,2);