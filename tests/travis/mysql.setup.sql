DROP DATABASE IF EXISTS phormium_tests;
CREATE DATABASE phormium_tests;

USE phormium_tests;

CREATE TABLE person (
  id INTEGER NOT NULL AUTO_INCREMENT,
  name 	VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  birthday DATE,
  created DATETIME,
  income DECIMAL(10,2),
  PRIMARY KEY (id)
);

/*
INSERT INTO person (id,name,email,birthday,created,income) VALUES (1,'Florence Ramsey','nibh@quis.org','2012-04-09','2013-07-11 22:24:56',8164.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (2,'Carl Blankenship','magna.Praesent@dui.edu','2013-09-10','2012-07-01 16:07:57',105.48);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (3,'Dai Carey','sem@eratSed.ca','2011-11-14','2013-07-19 19:16:26',5366.02);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (4,'Alec Oconnor','hymenaeos.Mauris@vitaemauris.ca','2013-05-07','2012-09-24 15:34:40',1881.83);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (5,'Violet Bolton','eu@erat.com','2013-08-17','2012-11-14 07:43:29',3501.35);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (6,'Xavier Rodriguez','vitae@hendrerit.ca','2012-02-13','2013-02-21 00:51:57',9805.08);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (7,'Aiko Spence','dictum.magna@fermentumrisusat.org','2012-12-20','2013-02-17 01:26:06',3694.93);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (8,'Hedda Bowman','ullamcorper.velit.in@Curabituregestasnunc.edu','2011-11-15','2012-12-16 12:10:44',2950.96);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (9,'TaShya Ingram','cursus@maurisSuspendissealiquet.ca','2012-07-10','2012-08-13 07:57:46',9401.12);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (10,'Len Collins','aliquet@Phasellusfermentum.com','2012-07-17','2013-09-02 21:55:20',3079.72);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (11,'Destiny Keller','ante.bibendum@blanditviverra.ca','2012-12-17','2011-11-14 22:38:19',366.51);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (12,'April Atkinson','Proin.non.massa@ligulatortor.com','2013-09-21','2012-09-28 12:55:12',3501.35);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (13,'Amity Drake','at.pretium@lobortis.org','2013-03-28','2012-05-26 06:58:12',366.51);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (14,'Francesca Colon','bibendum.ullamcorper@Curabiturvel.edu','2012-09-16','2013-07-20 17:08:12',4447.69);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (15,'Tate Shepherd','imperdiet.non.vestibulum@fringilla.org','2013-01-19','2012-10-18 11:29:28',3031.24);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (16,'Marsden Carney','dapibus.ligula@dolor.org','2013-05-07','2011-12-19 09:05:53',3077.71);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (17,'Edward Lambert','Sed.nulla.ante@laciniamattisInteger.edu','2011-11-24','2012-02-28 23:08:02',6244.59);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (18,'Shoshana Trujillo','parturient@diam.org','2012-02-14','2012-03-04 22:57:45',3317.03);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (19,'Dillon Lambert','pede.Nunc@loremvitaeodio.org','2013-06-11','2013-08-16 20:18:36',8164.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (20,'Brenden Gill','Nulla.facilisis@Sedauctorodio.edu','2013-01-18','2013-08-24 11:17:41',6244.59);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (21,'Moana Diaz','aliquam.adipiscing@Sed.com','2012-10-18','2012-08-17 00:55:29',3905.42);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (22,'Isadora Hoffman','sed.est.Nunc@ipsumSuspendissenon.ca','2012-01-01','2012-07-25 20:42:12',7817.81);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (23,'Hashim Joseph','molestie.pharetra.nibh@nibhAliquamornare.com','2013-07-18','2013-06-03 20:25:45',2157.51);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (24,'Lana Washington','In.tincidunt.congue@Sedneque.org','2012-01-21','2012-07-14 22:43:56',3533.76);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (25,'Alan Hudson','vehicula.Pellentesque@posuerevulputate.ca','2011-12-10','2012-07-15 04:33:16',2677.85);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (26,'Cedric Ashley','elit.pellentesque.a@Sed.ca','2012-02-27','2012-03-14 14:30:30',9726.29);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (27,'Ramona Underwood','libero.at@morbitristiquesenectus.com','2012-06-27','2013-02-22 09:16:50',504.34);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (28,'Vladimir Gay','lobortis.risus@nequeNullam.ca','2013-10-16','2012-07-23 08:09:48',3079.72);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (29,'Rose Townsend','metus.sit@vitaepurusgravida.org','2013-01-03','2013-07-08 10:54:17',9576.57);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (30,'Brynn Schroeder','eu@sitamet.com','2013-06-12','2012-08-11 06:03:50',1498.39);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (31,'Elaine Curry','parturient@lobortismauris.edu','2012-08-17','2011-12-05 05:31:30',8712.92);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (32,'Yasir Rosa','pretium.neque@Donectincidunt.ca','2013-01-24','2013-01-05 04:00:42',7516.68);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (33,'Hu Lawrence','lectus@pedeac.org','2013-08-31','2013-08-14 13:21:19',9349.04);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (34,'Ursa Valdez','pharetra.nibh@idanteNunc.ca','2013-06-17','2013-05-14 11:06:03',3379.81);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (35,'Lucy May','non@ipsumnuncid.org','2013-01-17','2012-12-05 18:58:05',877.57);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (36,'Clark Barber','Nulla.tempor.augue@iaculis.org','2011-11-09','2012-05-13 01:00:46',1862.08);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (37,'Nissim Ingram','porttitor.vulputate.posuere@Vivamus.ca','2012-09-25','2013-09-05 07:58:36',3077.71);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (38,'Alana Hull','mi.lorem.vehicula@Duisvolutpat.com','2013-02-16','2013-07-06 08:32:32',3516.83);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (39,'Nigel Lyons','Aenean@egetmassa.ca','2012-02-27','2013-06-09 17:20:43',5126.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (40,'Ross Mayo','egestas@mattisvelitjusto.com','2012-03-25','2013-09-29 12:39:10',1097.87);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (41,'Kevin Valencia','inceptos.hymenaeos@in.edu','2013-05-29','2013-08-26 04:46:16',5126.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (42,'Mariam Adams','Aliquam.nec@Nuncsollicitudincommodo.ca','2012-10-22','2012-08-08 21:33:52',7516.68);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (43,'Gray Morse','ultricies@volutpatornarefacilisis.org','2012-12-06','2012-01-26 01:32:20',3077.71);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (44,'Anjolie Durham','dictum@fringilla.com','2013-05-02','2012-09-01 17:26:00',1097.87);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (45,'Iola Wolfe','erat@Curabitursedtortor.org','2012-08-19','2012-04-06 16:52:00',3434.23);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (46,'Timon Howell','amet.metus.Aliquam@eratnonummy.ca','2012-08-23','2013-01-26 03:54:27',3267.45);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (47,'Karyn Foreman','at.auctor@Etiamlaoreetlibero.ca','2013-03-02','2012-08-08 13:53:07',7817.81);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (48,'Emery Cole','dolor.sit@sollicitudina.org','2011-10-25','2013-04-12 07:33:50',1498.39);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (49,'Lael Pickett','lorem@augue.com','2012-04-03','2013-03-11 12:38:42',9349.04);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (50,'Jonas Hines','lacus.Quisque@utpharetrased.edu','2012-09-12','2012-03-22 05:26:39',2311.12);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (51,'Bradley Donaldson','non@liberoMorbi.org','2013-01-03','2013-04-03 03:57:00',2311.12);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (52,'Juliet Sellers','Pellentesque@portaelit.ca','2012-04-29','2012-04-14 23:48:40',853.28);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (53,'Kenyon Byers','ligula@Duiselementum.ca','2012-10-30','2013-06-24 12:29:52',940.67);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (54,'Vera Pierce','aptent.taciti.sociosqu@arcu.edu','2013-08-03','2011-11-16 19:34:23',827.44);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (55,'Stella Beach','tempor@nisl.com','2013-07-20','2013-09-18 07:50:54',8118.92);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (56,'Akeem Rodgers','ad.litora.torquent@faucibusorci.ca','2012-03-27','2012-09-29 09:23:56',486.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (57,'Cleo Snyder','magna.tellus.faucibus@montesnasceturridiculus.com','2011-12-26','2013-09-01 21:15:17',6602.74);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (58,'Miranda Moon','Aliquam@Aliquamadipiscing.edu','2012-10-18','2013-05-14 20:14:44',4599.09);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (59,'Gillian Merritt','urna@Phasellus.ca','2012-11-22','2013-06-17 22:47:49',2744.49);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (60,'Lucian Chaney','ipsum.cursus.vestibulum@Vivamussit.org','2012-03-26','2013-08-18 06:10:10',366.51);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (61,'Quamar Parks','orci@aliquetsem.org','2013-08-02','2012-08-14 11:57:54',3379.81);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (62,'Hanna Jenkins','In.lorem@utcursus.ca','2013-07-19','2013-02-14 23:30:21',486.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (63,'Uriel Lynn','sagittis@molestiein.edu','2013-01-03','2011-12-29 14:33:18',4132.71);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (64,'Yetta Jarvis','Proin.velit@arcuSed.edu','2013-05-03','2012-03-23 15:12:33',1862.08);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (65,'Luke Holmes','ut.ipsum.ac@lectus.edu','2012-04-30','2012-07-06 23:13:50',1484.00);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (66,'Jessica Smith','Aliquam@tempor.org','2012-04-21','2012-01-07 00:25:54',2574.72);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (67,'Jasmine Savage','Duis@sempertellus.edu','2012-06-29','2013-02-04 05:05:15',1094.16);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (68,'Justin Turner','iaculis.aliquet.diam@pede.ca','2012-12-10','2012-10-01 20:44:28',1249.31);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (69,'Debra Hale','magna.Cras@massanon.edu','2013-06-18','2011-11-14 23:27:08',1432.14);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (70,'Emery Roth','at.pede@Sedegetlacus.edu','2013-04-26','2012-07-03 04:22:38',1432.14);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (71,'Lynn Barker','magna.Lorem.ipsum@libero.com','2013-04-17','2012-09-14 12:53:01',7516.68);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (72,'Willa Mclean','auctor.nunc.nulla@magna.org','2012-04-03','2012-07-13 17:35:29',6602.74);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (73,'Charles Hancock','magna.Cras.convallis@sempereratin.edu','2012-11-08','2013-08-05 01:03:08',4203.41);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (74,'Aimee Cook','Nunc.sollicitudin@mollisPhaselluslibero.edu','2012-08-23','2013-04-10 09:31:54',9576.57);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (75,'Madeline Fowler','suscipit.est.ac@ultricesposuerecubilia.ca','2011-12-04','2012-06-22 18:58:24',4447.69);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (76,'Bevis Sherman','sapien@purus.com','2013-02-09','2012-06-26 05:41:14',5102.39);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (77,'Keely Farley','netus.et.malesuada@scelerisquedui.ca','2012-05-09','2013-03-15 08:12:51',3434.23);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (78,'Holly Avery','fringilla.ornare@accumsaninterdumlibero.ca','2012-01-22','2012-09-24 14:21:45',8712.92);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (79,'Abraham Russo','sed.hendrerit@In.com','2011-11-12','2012-12-18 07:17:37',3455.13);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (80,'Eugenia Noel','vulputate@Sedpharetra.org','2013-04-16','2011-11-26 23:35:27',9643.48);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (81,'Isadora Briggs','sem.Pellentesque.ut@atortor.org','2013-07-07','2013-08-22 23:11:54',2574.72);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (82,'Dominique Montoya','odio.Etiam.ligula@consequatnecmollis.com','2012-09-02','2012-01-27 04:02:51',5102.39);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (83,'Kylynn Delacruz','accumsan@sociosquad.com','2013-06-03','2012-07-16 00:13:28',2157.51);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (84,'Simone Waller','facilisis.magna.tellus@Proinvelarcu.edu','2013-03-25','2013-04-11 08:07:26',8164.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (85,'Carlos Mann','vitae.orci.Phasellus@utmolestiein.org','2012-12-17','2012-06-03 11:05:55',2677.85);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (86,'Camille Lambert','auctor.Mauris@massa.ca','2012-06-21','2013-08-15 12:39:55',5163.65);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (87,'Jesse Cooley','sapien.cursus.in@maurisMorbinon.com','2012-03-01','2013-01-27 03:25:26',5013.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (88,'Yetta Phelps','egestas.Aliquam.nec@magna.ca','2012-09-07','2013-08-18 22:28:59',5163.65);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (89,'Armando Osborn','hendrerit.neque.In@SednequeSed.ca','2011-12-30','2012-11-21 19:30:02',1315.16);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (90,'Penelope Obrien','turpis.Nulla@sagittis.ca','2013-04-13','2012-09-07 03:41:47',5126.97);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (91,'Angelica Mcpherson','ornare.tortor@fermentum.edu','2011-10-30','2012-12-07 02:22:59',4605.98);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (92,'Christine Mcneil','non.bibendum@etultricesposuere.ca','2011-12-20','2013-07-26 15:30:49',877.57);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (93,'Hilda Richards','nonummy.ipsum@diamluctus.ca','2012-11-26','2012-08-22 10:12:33',1698.76);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (94,'Brittany Santiago','Fusce@liberoestcongue.com','2012-10-19','2013-06-15 10:58:30',6542.34);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (95,'Malik Fischer','ac.ipsum@laoreetposuere.com','2012-05-03','2012-09-05 16:04:37',6542.34);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (96,'Yasir Phillips','dolor.Quisque@Morbisit.edu','2012-12-08','2013-10-04 04:56:12',2744.49);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (97,'Madaline Carlson','sit.amet.consectetuer@uterosnon.ca','2011-12-02','2012-03-10 04:12:30',3079.72);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (98,'Hashim Cobb','pellentesque@Curae.Phasellus.org','2013-03-06','2013-05-06 09:08:28',1432.14);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (99,'Callum Crawford','auctor@Pellentesque.org','2013-01-15','2012-06-14 10:06:39',4786.60);
INSERT INTO person (id,name,email,birthday,created,income) VALUES (100,'Rhona Hutchinson','vitae@ornareelitelit.ca','2012-08-26','2012-10-05 06:34:27',3694.93);
*/