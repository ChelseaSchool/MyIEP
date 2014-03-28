-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 09, 2007 at 06:42 PM
-- Server version: 5.0.27
-- PHP Version: 5.1.6
-- 
-- Database: `ipp`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `accomodation`
-- 

CREATE TABLE IF NOT EXISTS `accomodation` (
  `uid` bigint(20) NOT NULL auto_increment,
  `accomodation` varchar(255) NOT NULL default '',
  `subject` varchar(255) default NULL,
  `student_id` bigint(20) NOT NULL default '0',
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date default NULL,
  PRIMARY KEY  (`uid`),
  KEY `accomodation` (`accomodation`,`student_id`,`start_date`,`end_date`)
) ENGINE=MyISAM  AUTO_INCREMENT=822 ;

-- 
-- Dumping data for table `accomodation`
-- 

INSERT INTO `accomodation` (`uid`, `accomodation`, `subject`, `student_id`, `start_date`, `end_date`) VALUES 
(820, 'present information with visual cues', 'all subjects', 304, '2007-02-09', NULL),
(821, 'enlarge materials', 'all subjects', 304, '2007-02-09', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `address`
-- 

CREATE TABLE IF NOT EXISTS `address` (
  `address_id` bigint(20) unsigned NOT NULL auto_increment,
  `po_box` varchar(255) default NULL,
  `street` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `province` varchar(255) default NULL,
  `country` varchar(255) default NULL,
  `postal_code` varchar(63) default NULL,
  `home_ph` varchar(63) default NULL,
  `business_ph` varchar(63) default NULL,
  `cell_ph` varchar(63) default NULL,
  `email_address` varchar(255) default NULL,
  PRIMARY KEY  (`address_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=281 ;

-- 
-- Dumping data for table `address`
-- 

INSERT INTO `address` (`address_id`, `po_box`, `street`, `city`, `province`, `country`, `postal_code`, `home_ph`, `business_ph`, `cell_ph`, `email_address`) VALUES 
(279, '', '123 Anywhere St', 'Vancouver', 'BC', 'Canada', 'V9H 4A2', '604-123-4567', '', '', ''),
(280, 'Same', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `anecdotal`
-- 

CREATE TABLE IF NOT EXISTS `anecdotal` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `report` text NOT NULL,
  `file` mediumblob,
  `filename` varchar(255) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`date`)
) ENGINE=MyISAM  AUTO_INCREMENT=71 ;

-- 
-- Dumping data for table `anecdotal`
-- 

INSERT INTO `anecdotal` (`uid`, `student_id`, `date`, `report`, `file`, `filename`) VALUES 
(70, 304, '2007-02-09', 'Test Annecdotal', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `area_of_strength_or_need`
-- 

CREATE TABLE IF NOT EXISTS `area_of_strength_or_need` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `strength_or_need` enum('Strength','Need') NOT NULL default 'Strength',
  `is_valid` enum('Y','N') NOT NULL default 'Y',
  `description` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=671 ;

-- 
-- Dumping data for table `area_of_strength_or_need`
-- 

INSERT INTO `area_of_strength_or_need` (`uid`, `student_id`, `strength_or_need`, `is_valid`, `description`) VALUES 
(667, 304, 'Strength', 'Y', 'Work is done neatly and on time'),
(669, 304, 'Strength', 'Y', 'Test 1');

-- --------------------------------------------------------

-- 
-- Table structure for table `assistive_technology`
-- 

CREATE TABLE IF NOT EXISTS `assistive_technology` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `technology` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=71 ;

-- 
-- Dumping data for table `assistive_technology`
-- 

INSERT INTO `assistive_technology` (`uid`, `student_id`, `technology`) VALUES 
(70, 304, 'Use of computer screen magnification program');

-- --------------------------------------------------------

-- 
-- Table structure for table `background_info`
-- 

CREATE TABLE IF NOT EXISTS `background_info` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL,
  `type` enum('Family History','Social/Emotional','Attendance','Additional Coding') NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`type`)
) ENGINE=MyISAM  AUTO_INCREMENT=275 ;

-- 
-- Dumping data for table `background_info`
-- 

INSERT INTO `background_info` (`uid`, `student_id`, `type`, `description`) VALUES 
(273, 304, 'Family History', 'Jane lives at home with her two brothers, mother, and Grandmother.'),
(274, 304, 'Attendance', 'Jane has had near perfect attendance.');

-- --------------------------------------------------------

-- 
-- Table structure for table `bugs`
-- 

CREATE TABLE IF NOT EXISTS `bugs` (
  `uid` bigint(20) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `status` enum('Active','Under Consideration','In Progress','On hold','Resolved','Future Consideration') NOT NULL,
  `bug` text NOT NULL,
  `resolution` text,
  `referring_page` text,
  PRIMARY KEY  (`uid`),
  KEY `username` (`username`,`status`)
) ENGINE=MyISAM  AUTO_INCREMENT=81 ;

-- 
-- Dumping data for table `bugs`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `coding`
-- 

CREATE TABLE IF NOT EXISTS `coding` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `code` int(11) NOT NULL default '0',
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date default NULL,
  PRIMARY KEY  (`uid`),
  KEY `code` (`code`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=256 ;

-- 
-- Dumping data for table `coding`
-- 

INSERT INTO `coding` (`uid`, `student_id`, `code`, `start_date`, `end_date`) VALUES 
(255, 304, 59, '2006-02-16', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `coordination_of_services`
-- 

CREATE TABLE IF NOT EXISTS `coordination_of_services` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `agency` varchar(255) NOT NULL default '-unknown-',
  `report_in_file` enum('Y','N') NOT NULL default 'N',
  `date` date NOT NULL default '0000-00-00',
  `description` text,
  `file` mediumblob,
  `filename` varchar(255) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`agency`)
) ENGINE=MyISAM  AUTO_INCREMENT=488 ;

-- 
-- Dumping data for table `coordination_of_services`
-- 

INSERT INTO `coordination_of_services` (`uid`, `student_id`, `agency`, `report_in_file`, `date`, `description`, `file`, `filename`) VALUES 
(487, 304, 'Occupational Therapist', 'Y', '2007-01-10', '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `error_log`
-- 

CREATE TABLE IF NOT EXISTS `error_log` (
  `uid` bigint(20) NOT NULL auto_increment,
  `level` enum('WARNING','ERROR','INFORMATIONAL') NOT NULL default 'ERROR',
  `username` varchar(128) default NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  `student_id` bigint(20) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `level` (`level`,`username`,`time`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `error_log`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `grades_repeated`
-- 

CREATE TABLE IF NOT EXISTS `grades_repeated` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `grade` int(11) NOT NULL default '0',
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `year` int(20) unsigned NOT NULL default '0',
  `ipp_present` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`uid`),
  KEY `grade` (`grade`,`student_id`,`year`)
) ENGINE=MyISAM  AUTO_INCREMENT=17 ;

-- 
-- Dumping data for table `grades_repeated`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `guardian`
-- 

CREATE TABLE IF NOT EXISTS `guardian` (
  `guardian_id` bigint(20) NOT NULL auto_increment,
  `last_name` varchar(255) NOT NULL default '',
  `first_name` varchar(255) NOT NULL default '',
  `address_id` bigint(20) default NULL,
  PRIMARY KEY  (`guardian_id`),
  KEY `last_name` (`last_name`,`address_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=324 ;

-- 
-- Dumping data for table `guardian`
-- 

INSERT INTO `guardian` (`guardian_id`, `last_name`, `first_name`, `address_id`) VALUES 
(320, 'Smith', 'John', 279),
(321, 'Smith', 'Stan', NULL),
(322, 'Smith', 'Stan', NULL),
(323, 'Smith', 'Mary', 280);

-- --------------------------------------------------------

-- 
-- Table structure for table `guardian_note`
-- 

CREATE TABLE IF NOT EXISTS `guardian_note` (
  `uid` bigint(20) NOT NULL auto_increment,
  `guardian_id` bigint(20) NOT NULL default '0',
  `note` text NOT NULL,
  `priority_note` enum('Y','N') NOT NULL default 'N',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`uid`),
  KEY `guardian_id` (`guardian_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=64 ;

-- 
-- Dumping data for table `guardian_note`
-- 

INSERT INTO `guardian_note` (`uid`, `guardian_id`, `note`, `priority_note`, `date`) VALUES 
(59, 320, 'Met with John December 2, 2006 to discuss progress', 'N', '2007-01-30 07:47:55'),
(62, 320, 'Met with John January 21, 2007 to discuss program changes.', 'N', '2007-02-04 03:21:06'),
(63, 323, 'Met with Mary December 20th, 2006.', 'N', '2007-02-09 01:26:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `guardians`
-- 

CREATE TABLE IF NOT EXISTS `guardians` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `guardian_id` bigint(20) NOT NULL default '0',
  `from_date` date NOT NULL default '0000-00-00',
  `to_date` date default NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`guardian_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=310 ;

-- 
-- Dumping data for table `guardians`
-- 

INSERT INTO `guardians` (`uid`, `student_id`, `guardian_id`, `from_date`, `to_date`) VALUES 
(306, 304, 320, '2007-01-30', NULL),
(309, 304, 323, '2007-02-09', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `logged_in`
-- 

CREATE TABLE IF NOT EXISTS `logged_in` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `ipp_username` varchar(128) NOT NULL default '',
  `session_id` text NOT NULL,
  `last_ip` varchar(15) NOT NULL default '',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  AUTO_INCREMENT=1728 ;

-- 
-- Dumping data for table `logged_in`
-- 

INSERT INTO `logged_in` (`uid`, `ipp_username`, `session_id`, `last_ip`, `time`) VALUES 
(1727, 'admin', '', '192.168.0.102', '2007-02-09 03:02:13');

-- --------------------------------------------------------

-- 
-- Table structure for table `long_term_goal`
-- 

CREATE TABLE IF NOT EXISTS `long_term_goal` (
  `goal_id` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `review_date` date default NULL,
  `goal` text,
  `is_complete` enum('Y','N') NOT NULL default 'N',
  `area` text NOT NULL,
  PRIMARY KEY  (`goal_id`),
  KEY `student_id` (`student_id`,`review_date`),
  KEY `is_complete` (`is_complete`)
) ENGINE=MyISAM  AUTO_INCREMENT=322 ;

-- 
-- Dumping data for table `long_term_goal`
-- 

INSERT INTO `long_term_goal` (`goal_id`, `student_id`, `review_date`, `goal`, `is_complete`, `area`) VALUES 
(320, 304, '2008-02-01', 'To participate in classroom activities by listening, raising her hand, and remaining in her seat. ', 'N', 'Behaviour'),
(321, 304, '2008-02-14', 'To consistently comply with school requests and expectations', 'N', 'Behaviour');

-- --------------------------------------------------------

-- 
-- Table structure for table `medical_info`
-- 

CREATE TABLE IF NOT EXISTS `medical_info` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `date` date NOT NULL default '0000-00-00',
  `copy_in_file` enum('Y','N') NOT NULL default 'N',
  `is_priority` enum('Y','N') NOT NULL default 'N',
  `description` text NOT NULL,
  `file` mediumblob,
  `filename` varchar(255) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=156 ;

-- 
-- Dumping data for table `medical_info`
-- 

INSERT INTO `medical_info` (`uid`, `student_id`, `date`, `copy_in_file`, `is_priority`, `description`, `file`, `filename`) VALUES 
(154, 304, '2007-02-13', 'Y', 'N', 'Moderately severe sensorinearal hearing loss in right ear, normal in left.', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `medication`
-- 

CREATE TABLE IF NOT EXISTS `medication` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `medication_name` varchar(255) NOT NULL default '',
  `doctor` varchar(255) NOT NULL default '-unknown-',
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date default NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM  AUTO_INCREMENT=65 ;

-- 
-- Dumping data for table `medication`
-- 

INSERT INTO `medication` (`uid`, `student_id`, `medication_name`, `doctor`, `start_date`, `end_date`) VALUES 
(64, 304, 'Ritalin', 'Dr. Who', '2007-01-02', NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `performance_testing`
-- 

CREATE TABLE IF NOT EXISTS `performance_testing` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `test_name` varchar(255) NOT NULL default '',
  `results` text,
  `file` mediumblob,
  `filename` varchar(255) default NULL,
  `date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=291 ;

-- 
-- Dumping data for table `performance_testing`
-- 

INSERT INTO `performance_testing` (`uid`, `student_id`, `test_name`, `results`, `file`, `filename`, `date`) VALUES 
(290, 304, 'Woodcock Johnson Reading Test', 'Word ID Grade 2', '', '', '2006-12-22');

-- --------------------------------------------------------

-- 
-- Table structure for table `permission_levels`
-- 

CREATE TABLE IF NOT EXISTS `permission_levels` (
  `level` int(11) NOT NULL default '100',
  `level_name` varchar(15) NOT NULL default '-unknown-',
  PRIMARY KEY  (`level`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `permission_levels`
-- 

INSERT INTO `permission_levels` (`level`, `level_name`) VALUES 
(50, 'Teacher'),
(100, 'Login'),
(60, 'Teaching Assist'),
(40, 'Vice Principal'),
(30, 'Principal'),
(20, 'Assistant Admin'),
(10, 'Administrator'),
(0, 'Super Administr');

-- --------------------------------------------------------

-- 
-- Table structure for table `program_area`
-- 

CREATE TABLE IF NOT EXISTS `program_area` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `area` text NOT NULL,
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date default '0000-00-00',
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`start_date`,`end_date`)
) ENGINE=MyISAM  AUTO_INCREMENT=27 ;

-- 
-- Dumping data for table `program_area`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `school`
-- 

CREATE TABLE IF NOT EXISTS `school` (
  `school_code` varchar(255) NOT NULL default '',
  `school_name` varchar(255) NOT NULL default '',
  `school_address` text,
  `red` char(2) NOT NULL default 'FF',
  `green` char(2) NOT NULL default 'FF',
  `blue` char(2) NOT NULL default 'FF',
  PRIMARY KEY  (`school_code`)
) ENGINE=MyISAM COMMENT='red green and blue are the RGB components of the school colo';

-- 
-- Dumping data for table `school`
-- 

INSERT INTO `school` (`school_code`, `school_name`, `school_address`, `red`, `green`, `blue`) VALUES 
('3201', 'County Central High', '1234 Anywhere St.', '33', 'CC', '99'),
('3203', 'Oak Alternative', '42- Oak Street', 'CC', '00', 'CC');

-- --------------------------------------------------------

-- 
-- Table structure for table `school_history`
-- 


CREATE TABLE IF NOT EXISTS `school_history` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `start_date` date NOT NULL default '0000-00-00',
  `pat_accomodations` varchar(255) default NULL,
  `end_date` date default NULL,
  `school_code` varchar(255) default NULL,
  `school_name` varchar(255) default NULL,
  `school_address` text,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `ipp_present` enum('Y','N','?') NOT NULL default '?',
  `accommodations` text,
  `grades` varchar(255) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=442 ;

-- 
-- Dumping data for table `school_history`
-- 

INSERT INTO `school_history` (`uid`, `start_date`, `pat_accomodations`, `end_date`, `school_code`, `school_name`, `school_address`, `student_id`, `ipp_present`, `accommodations`, `grades`) VALUES 
(436, '2007-01-30', NULL, '2007-01-30', '', '', '', 303, 'Y', NULL, NULL),
(437, '2007-01-30', NULL, NULL, '3201', 'County Central High', '1234 Anywhere St.', 303, 'Y', '', NULL),
(438, '2007-01-30', NULL, NULL, '3201', 'County Central High', '1234 Anywhere St.', 304, 'Y', NULL, NULL),
(439, '2007-01-30', NULL, NULL, '3201', 'County Central High', '1234 Anywhere St.', 305, 'Y', NULL, NULL),
(440, '2007-01-30', NULL, NULL, '3201', 'County Central High', '1234 Anywhere St.', 306, 'Y', NULL, NULL),
(441, '2007-02-03', NULL, NULL, '3201', 'County Central High', '1234 Anywhere St.', 307, 'Y', NULL, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `short_term_objective`
-- 

CREATE TABLE IF NOT EXISTS `short_term_objective` (
  `uid` bigint(20) NOT NULL auto_increment,
  `goal_id` bigint(20) NOT NULL default '0',
  `description` text NOT NULL,
  `achieved` enum('Y','N') NOT NULL default 'N',
  `review_date` date default NULL,
  `results_and_recommendations` text,
  `strategies` text,
  `assessment_procedure` text,
  PRIMARY KEY  (`uid`),
  KEY `goal_id` (`goal_id`),
  KEY `review_date` (`review_date`)
) ENGINE=MyISAM  AUTO_INCREMENT=579 ;

-- 
-- Dumping data for table `short_term_objective`
-- 

INSERT INTO `short_term_objective` (`uid`, `goal_id`, `description`, `achieved`, `review_date`, `results_and_recommendations`, `strategies`, `assessment_procedure`) VALUES 
(577, 320, 'To remain at a task when distractions are present', 'N', '2008-02-01', NULL, 'Working in a private corner', 'Observation, annecdotals, performance indicators.'),
(578, 320, 'To work effectively with a teacher assistant at recess to learn games and cooperate with other children', 'Y', '2008-02-01', NULL, 'To teach positive playground rules.', 'Obeservation and Anecdotals.');

-- --------------------------------------------------------

-- 
-- Table structure for table `snapshot`
-- 

CREATE TABLE IF NOT EXISTS `snapshot` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `file` mediumblob NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`date`)
) ENGINE=MyISAM  AUTO_INCREMENT=94 ;

-- 
-- Dumping data for table `snapshot`
-- 

INSERT INTO `snapshot` (`uid`, `student_id`, `date`, `file`, `filename`) VALUES 
(92, 304, '2007-01-30 13:35:55', 0x255044462d312e330a332030206f626a0a3c3c2f54797065202f506167650a2f506172656e742031203020520a2f5265736f75726365732032203020520a2f436f6e74656e74732034203020523e3e0a656e646f626a0a342030206f626a0a3c3c2f46696c746572202f466c6174654465636f6465202f4c656e67746820313532363e3e0a73747265616d0a789cad58dd6e9b4814becf539cab552bc5d3f9077a97a46dd27615791b6fa54ab9a16662b335d0024e9447dab7dc333098496cec9a8da220c6f67c73ce77fe87c3a7134a54000f27e73378f38101e3845298ddc1fbd9098b42220504514444001928ad49b459afe0067e01251a7fef3fcb0520968c04911c42ca49a86096c0ab6fc5ba849bf9b22856af61f60f1e007fb5a77250fda17b2075404488c75312840d24e30226c0aa1aceee4dbe36a7705e16c58fea14ce56df4d59c7a730635f80fdc9fb137f01938c0402101c84b6d2f190080541c889d030cfe0cd4706ef8a4e3a019c3e152fd4b89bb048bb672b1ebe9190412005097423dec73c49efd3641daf605a168b32ce60ba8a73f085d10db6fd6bdf641011aa21a02142b7c2704f1809cc1386924832f09fe5e2c47e4fe1b291943614f2e6497b2243690908846ca05052b82eea938b2231c933d3e079ea7794e7aab173c0379013f814e7066eb2b45ec2e4192cfa99f4d508f19d35123227e74927bc60129f0172f2e5f2a4b5940e98657ae3906edd39a4ddd7082598e5500792e8a891e9a65e2726afe1637e5794595ca745de0bd6f1d609f8cce81b5415118567ca8028f504f53acecc5bd8f2ed412026a50d3d0fa9676c9baf2114e48768e5a39c9dc3fb84c0f53ac31838462081818d4c7950527036d93c7e5f28c79110365158a077716da0b883f3b4ac976348eaa1d09be984f20915c793d4a35caccbd21aedb28c93a3acd691d4437d4ef3c4948bb8ac4d0e4589b16e269ffd18f789c1dc8369d1db7d34a92cc2806eb65e1a7bf018367b8c0f268b57e678267b848ec9b3c5281e7b20fda24942859a045ed572eb8124a1c2c8aad5847353a25e224728cd89624f486ac14798ccc72ad679fd081788576269b94a174bb87d253865b7af8f36a4877b93e6f33126f420303683096513418f756b25d16ab2cd5f49529aaa1a43520f82ad8184b3fcf161694acca935f18dd8d6b0fef965a745153a0c0525f0a3d0fa117ec765b7def223a706d654a67d3fba4aabba281ff7eab253a2ed0fd1e1dde6708007ae300ad02268db8837525c17b06c2500f417e422394084d39a0644524feb766db57e89e09481269cf7c1e9d603c1697b2246f7b6542f10ad523112e927d16aabd60837f490666b5325f123088a5d10d63facf0eb186d6103e5e858f5709d737d33f1980aef01a1207a729c348e2e11746d7847976d20c7d0d5230df6a187c5e1826897c7d63f4d799f564579fbaaba7d3d46a21e0c922ccd8f16864644ca4e989f4559c3ccc4d918497aa45192884811eea691e9b44b462304f180fece7fe4c5c30e51fe474210584342d12704b71e4808422b12b4f9ed12c32949e3bc1a9bdf05d60cadf164ceb57543b71ec8ef4284968da6592f96f9ce66fd29994f61370975e818ce23eb7ade3957456660ba2c728c2d4de5042bdb442a1d0c74981d004e6292baf9a434a67e0b765af62ae281fd564cd186775aa3c77c8df379b1be37e5fe7d1ca760da365298a0efdb96e2fce2c0a61007b8b06f6eac875ec4799cc407f669cc1c6d9c4e8baac6a2d0a6a0afd115c8336fe86f4dcd757373b0b1815bbf5451e382db58ddf8b05b0ff830178a88d687cfe3f98f45898a2743756c7c7bc0184e1a781ae691c0ba9f54c20e776eedab3eb849486a7b9a0972c30240dfb983c3a84f73086bca3b9619c25ba7fe1067e9ea71b0391aea6f1c10c35ede3504cdc8bc4aef4d05710d4b1b2b0ff6c6019d1cea8702be97458defd52964cdcb29c448334e7d79d27ee035864e2b865ec565af955befe7cafd6898ab3da83b5554e8da6e9eaf71b04c30fc9e8f698779927854d8f3b48c2bfc4f20c7b601b048de99798db475f0e445533aa3d206f5261cdc7a201c18369841eb1b67f379916545d244c1a1bcfe9b61f012b74ecdf5607fe9d42c87ee9cb058aa4df2cd17f5b2823fe0da98a4da6e4f87ebd59681775f8c328ded61089c75f75c435d32e616ef566eb2ab29c549721a2f0cb037626b9ae4a03dbff788dbd0e72c1f10ca70140aadf3f9a1e07fddfb45b3dc7f9bcc42616f4543c25cd52fe3aa428d920a8674bd79ac6a93a13affa25acab6b81abc6dd3f5f7553a77f378e5abfa1fc9235b420a656e6473747265616d0a656e646f626a0a352030206f626a0a3c3c2f54797065202f506167650a2f506172656e742031203020520a2f5265736f75726365732032203020520a2f436f6e74656e74732036203020523e3e0a656e646f626a0a362030206f626a0a3c3c2f46696c746572202f466c6174654465636f6465202f4c656e67746820313237313e3e0a73747265616d0a789cad57db6edb38107def57ccd3a2016296179192f29634dd22452fd9d84051202faac4d8dac8624ad229fc49fb973bd42592d7569b608d004286a4e772e6cc0cc9e1c32b4a640c3f5f5d2ce0cd9f0c58442885c51dbc5be04ea2048cbf37efc3221e6078aaffdae5d38ff9f0639626241210a7291131ac412a45d227b98239fc40a50acf8fbf7609a82b4a05893824949344c2a280d7dfccc6c23c5f19539dc0e26f34007fb55639c8c1e82f54aa988804cd5312278d4ac605cc80390fe78fbadee853b8b0c6dcbb5338afbe6bebb35358b01b601ff960f107e2c3488c70e09f50c13b9e1021214e38110af235bcb96270697aef7601ed97e8b024158929c4b2d1b506ce55f0af937b9402ca4d14322512318cf0906a82987babeba55fb9b33d58c65676d562327093479366384f8962633b57b727f0d5d87b281d14a6d650ebcc575bc8ea024c0dbe5cebc18129deec2f4e0322e2b03400d2ca5380e0a194378e7ed6ba782618bd891e8c09133d18838d00c6d5fa6153390d6f4deded989407e3ec82e274d7622b078b13e5b75f69ad63940816e137a62aa86f39a85246941c4aad93f702128cb014772322a3269e4fba28f36c278670f8607a0ea0af4269a5634d0195a2f4a5a91df855e6035a59eebb022eebe57e8292dfb49d67ba2269a8f2e0cadb8dc5d2f0d0bad4f8f24bab8c8910851211491582184941a4ece57d106334a8701b2b8875a59859af8b33e094c633ca6674d4387aedbc693f83f656ded3ce98229ce3b62451dc68bf297d86c8c1edeb4b937b63cfe0d212f8ba32b727ff89eaff91482648d551bfeee40912c9240d140b0e2eb4f39859f006e69b8707633db2a0082bb7afcf9dd3cead311d636f9fd926faf172285b5261a8e9806727ef7b9be03052b82d49da72fe32f3ba4f159f51b507e2a4311151c214cc10651683d570072ff4a6cbae94110ea8c69b6bb7c5d2a8ccb2a99ed0542ff42a7b2c71eaa13cc017b02cd6655d3aafad2ee0fbf60c3e98550d17a6f2a6dee3c20e66077dc060704a071fc616af6ad48f5331940d5c5bb3b4d9fa143e19241eb8fbb2aa1c94e323c16397dd69bf85dcd4b9b6b53b2e2b6933750756b6f2142b2902cb77bac047fda82b307770aded9db1eb0c9d843fe03c5f95b813a03d2a2f23ec0e540e4ce8e4295e46092529dde5a59a318e1c7b062f3be507787928e3914ac3852fd8fa6a4c919bfcbea190c334dee8aca9d850cb2f655224653f05f0a250c0d525bcb759a1811f9509115a53e9c0844e9e6042c430d86e1e9819ba55d6cd28084c98237dcb5cbba3265ee054a56c487c274f255ea41255ee35243663f41989ef943f33f12289c2880cb6bee4f9e6a101026b7db1d2367b285f9e7111d39091a0f0a8291662f71ed3c9132916e2e91e837d12c3281f3512385fd5a19f6e8f93dd2344c57154b2510bebe489a8788a4fa9b685bd3719b6db2f8f81aefae7fe25edc58ee0ad5eb19123ad3ce548841544478e5c6a9f95953b821f340daf8bc18f569ef203df96fd4dc366b52bdbd154e1bf3814bfe9ccc23b1c42373a376b6ce7457be3bb3d399bb8d5fefec639fd966438d755822e85ced33c08f0b6fb58161baca66e58369e014e1af890e16b69be2efd6a26a85f85ce834b9bcc6e9b6247e7afb32576c937e2c0f456a39a1f01fb046f8b248b431b902209bd6edc06c6db4f38b7e2af1fe02c1104739510d671d066ce61448583a958e75bbc93ac319c7f302c390b130c463fbbde7cafcabc7b01b871a8ff0270efe2070a656e6473747265616d0a656e646f626a0a372030206f626a0a3c3c2f54797065202f506167650a2f506172656e742031203020520a2f5265736f75726365732032203020520a2f436f6e74656e74732038203020523e3e0a656e646f626a0a382030206f626a0a3c3c2f46696c746572202f466c6174654465636f6465202f4c656e677468203936373e3e0a73747265616d0a789cb557cb6edb4810bce72bfa9800d6687ade3ce66938d883d6d265815c1889969948e486a46c247f94bfdc2639e28c6c449a001b181ea0f998eaeaae1ab6047c7cc199b6f0f8e2cd0ae61f0438c639aceee0fd8a6e3823215e6faffb8bf400d253c7b5d98eef22a0082f63e6989260b38c490b7bd0c6b06c8a77b0846fb4a9a1e7e3b5d902eda532c914a5c205731a561b78f94f7d6860b9beafebdd2b587d2100f8fb98b10ea067b634964947f09c59376c8942c20cb0ede0f543511d8a2b78d3d4f5d7f60a5eef3e174d975fc10a6f01ff1201f11ba04266a91cf4274d9f9d704c6ab04e306960bd87f90dc2bb3a6417d573b82241f0d384c7f26266fc3a262c24d5cf821519d3d990f0cd6201cb725be5dda129da276550803a695b3dd4d4f28c39396c3b838f7955c0725f76f7307bb2ad063cc976ec388fd6715b2b99e5601c326dc664e1506d8aa6edf26a03c3ffb6290a78ec41bafb02caeaae6ef67957d615acebaacbcbaad8d055ba59b670536dca877273c877e50fbabc68ea6d93ef61b1cbab270992e8d489629f8bf398ad4445abe5a697f1d836a3328632a8d3c74775f6ef0df4245209c150edb818e8853ec0a797b7c543593cc2bbbc2b003fbd0a198e3b5c1fa51097529ba160d43444c2174632ea978fcfbb8384cb9403c3a9e1630b17795354ddfcfa90379b322e91e4926807184d2908950623353e81e905f8b6ae0983b8d7cd7c55e4ebfba2097023296d1d132690f2f12552e426a43e3886eeb4c443619f719a503ca72414493aa5432901052d35cb8116d89f8e7b7ad3328ec7f83c8ad0aa3f6b340e051c1ad494d5bafc37df3d03504e312b02808f2f0028a2cc4159d5d7f71c8dffc1234a59e6a213dcc7bff088d2f4ba39ef11f15b1e5128fb136b92938f933ca2482bd626796482f17a4a82f11e8960123d32749b07523e4ef288ecfb20523c32a1784e4928de2309285ec252f4ba0912f6719247649f98bce011e1282313007c9ce411416ca4fdd31e11ca3083c1233efe8547047d65343fef11f95b1e11347999484e3e4ef288a0294b6392472618afa72418ef910826d1236835b32a90f2719247d068664c8a472614cf2909c57b2401c54b1831635c0709fb38c923c81dcbec058fd0fc2978d87f0c931c62453f0b5c34c8c9247ba6fe86be49d46d64c64fadd3287732c6018d7fd1e039939c06c3faaebf44eafb4e72e1962cb1c8b7e485f9891b7c3a26faad111975b2ebe84c1c3fdb344c6b0733f2385a20827710df9e7c3b86171446a70f799f2688f174bc6ef2b625469b786c3de5bafcde76c59ee8fc245a7a468b81e8b5c5e1f3ae5cfb9f376d4cf53f26aa36000a656e6473747265616d0a656e646f626a0a312030206f626a0a3c3c2f54797065202f50616765730a2f4b696473205b3320302052203520302052203720302052205d0a2f436f756e7420330a2f4d65646961426f78205b302030203539352e3238203834312e38395d0a3e3e0a656e646f626a0a392030206f626a0a3c3c2f54797065202f466f6e740a2f42617365466f6e74202f48656c7665746963612d426f6c640a2f53756274797065202f54797065310a2f456e636f64696e67202f57696e416e7369456e636f64696e670a3e3e0a656e646f626a0a31302030206f626a0a3c3c2f54797065202f466f6e740a2f42617365466f6e74202f48656c7665746963612d4f626c697175650a2f53756274797065202f54797065310a2f456e636f64696e67202f57696e416e7369456e636f64696e670a3e3e0a656e646f626a0a31312030206f626a0a3c3c2f54797065202f466f6e740a2f42617365466f6e74202f54696d65732d526f6d616e0a2f53756274797065202f54797065310a2f456e636f64696e67202f57696e416e7369456e636f64696e670a3e3e0a656e646f626a0a31322030206f626a0a3c3c2f54797065202f466f6e740a2f42617365466f6e74202f54696d65732d426f6c640a2f53756274797065202f54797065310a2f456e636f64696e67202f57696e416e7369456e636f64696e670a3e3e0a656e646f626a0a31332030206f626a0a3c3c2f54797065202f466f6e740a2f42617365466f6e74202f48656c7665746963612d426f6c644f626c697175650a2f53756274797065202f54797065310a2f456e636f64696e67202f57696e416e7369456e636f64696e670a3e3e0a656e646f626a0a31342030206f626a0a3c3c2f54797065202f584f626a6563740a2f53756274797065202f496d6167650a2f5769647468203230300a2f4865696768742035320a2f436f6c6f725370616365202f4465766963655247420a2f42697473506572436f6d706f6e656e7420380a2f46696c746572202f466c6174654465636f64650a2f4465636f64655061726d73203c3c2f507265646963746f72203135202f436f6c6f72732033202f42697473506572436f6d706f6e656e742038202f436f6c756d6e73203230303e3e0a2f4c656e67746820383334393e3e0a73747265616d0a789ced5d795c13d7f63f2c6a6b0d9b2c32ecfb0e21c316361392b087b0af610b10c26ad8448408221478a05804151054142db82128a228560b4add79fab0b5adcfa7d58ae559b5d5f22bc2fcfe98bc8840adedf359fb1edf4ffe9ab9e7dc3333df7beeb9e79e9900368739fc07007fb40173f8efc41cb1e6f03bf1f0e9e3579c9d23d61c7e036e8ddefbe8c46e9ff5f168295b86a93eed6c53ff41e51cb7c64f0f6073c49ac36be2a313bbcd0b7d37f4b52dad4a003b5530d7b24df1fbc73ffe819f75ae4c38fcd74fd1523668c946d4adc0e68835875fc5d5bb5f551eaed75ee90376aa403690f0249a329d984c665959d9f3e7cf9796b39bfa0f420c114c15c1d18842a12424246073c49ac3aba194cdd8746a2ff8188085964982bb8b8b4b4040405959d9850b175acf7457766e827812a008d819cef32151289455ab56ddbe7d1b9b23d61c7e0949ad658d9f1e000735b100d423c4372828a8aaaa6a7474746262a2a9ffe0e1bff6bb7f9406eea87f7c049d4e4f4d4dadafaffffbdfff2e129f23d61ca6e3f6c3fb5d439f44341580868c9797575c5cdce5cb97272727310cbb7cfb0bf144ab9e6b67641238e8323feb02efbae33b6b4fb641b81970d1a94ae688358797b062ff868fcf1e61d565829182853f252525e5ce9d3bf8a99bdfdddd767a6feff067153ddb2dd784e7eeab69bfd0bbb67727b2dc1d34644c96fb4ed53347ac39bc0070d19e6b6796b555819a947894657e7e3eeea8300c134bb43a7cb57fdb99aec0cdcb09e94e7a057ee6c5a110640cead280105483c8bebe73c49ac36c30290a3afab7b3b9fb6ac05903904564d7a55f7ef9257e8adfb6b673e8d48ec1c31a79de10620201c6106d012c439c5592e1a88787475b5bdb546dbf99586b7b77ce4bb2d97fa9affd426ff3c0c1ba93edb527db4a0e378536e6296598be994b9cc35bc7eecf8e740e9d8a6a5e05246540081a01e4952b578ace421c096c5440430610022004d090061d394008a0a384ba3b858585ddbb776f9ac2d725d6939f9ecaf19d8f5c1b6838bd5f299b01f66a405c022465083395e15354733de432a86028df747cef1bbbd639bc2d90cb02b7f477f8d4654090312004a09b73389c91911151839a23add25136ea8164431f07b520b204451510824494959b9b5b7777f7d8d8d84c9daf4bace6937bba864ecf4fb6031401840056ba4035061d45b058026166104304aa162004467ad8acddcce19d854e3eabfd426fdcf662e9654bc14e15108272885d7777f7f3e7cf456dc6c6c65a5a5ae28a322c57fb456f2d040d6972a2af9b9b5b4747c7c4c4c4ac6a7f9d5849ad653dd7ce1808fcc1401e18a6106e46a3d1783c5e5d5dddf6eddbc1521b289aa0bf1810c27c77b3b8b8b8478f1ebdb18bfef3e0f9c4c4d9afffcad9be9ab826ecab0777fe68737e1dcd0307f150bd73e894e0e02642ba13c412414d1a22cd8383834574a93ab663491675d7b91eee8e52b1442bd0579288b0623299ebd6ad9bead266e25788e55c15de35745a864f0184005c94c5626ddab4e99ffffca7a841654f0ba8480142108f26f9fafadeba756b563dcbdaaae4f8cebed5b1d3b21d6ab98c5de77ab2f6549bad7479851977bf7ff0f8a71f5f6deadbc48f63cff4057e1b7ab76f3eb5cfae3c4682670d3144e9654b95b21956a5915e1b96c9f029f313897fb499b3607272d2fac3c8945d15e0a907246da0dbe915f82d48260317056f7d40086e6e6e0303035b073a310c5b7ba4a1a063e3bc245b309007370bf308069e0bbd79f3e6af76f42a6225b596edb978bcb4bb0974e4e6f912a954eac0c0c04f3ffd34ad996c3a45966debeaeadadddd3d7b1f5cb4fbeac0fe4b7d29bb2a20d27ce4b190970a999463c3836de78fc5b7ac817033fabaa499b2a49288b0faf4a6fe83155ddb379ddabba1afadfa782bfecbda53fd3e4be757aff08d6361aafd5f0e6f3a7cb53f79573978e901d5548c6da5c85b2a166605345340a4c0401e74e5c4d5e51e3f7e5561c95bc6fdc7ffd4c875cbfdb80aa289106c1356ce8f8b8b23ae422dd7844bf0ac218104346dd0925994462e3fb2153896b9fb6a20960886f28010e685a0542a352a2aead34f3f7df8f0e1eb74f72a626d3bd395d45a06f66a8010180cc6be7dfb666d36393939343434abaf5a986a7ff8af9f760d9d4edc596a5e1c0a6166a02dbb88677ff28b0b1b7a76ecb978dca326ed83344770d1027569712f7d91a05c06a5e3525f697713445a80b12220045095027b35f94c5af896fc9086bca0fa5c70d3996f8c3434344cebb4eff3f3c0458bf7afab3dd986ff56b4af335d4153cbf57c9d3bf20a64b4afddf1e9819e6b679afa0f42020954a4c449ba5e5e5e0d0d0de7ce9dbb7efdfaf1e3c76b6b6b6dd99e2aa1641a8d565e5efe6ff6f8a610b22935757705d0b515f9b4e5cb97f378bcc1c141e342d6d6814ef3e250b1442b8825025d7b9ee512b042c0de382c3d3e794d2e75558c85a71383c1282828f8e69b6f4439add7c1ecc4fae6fb91bcbd65b527db60a90620049560f29e3d7b7e294cfb2528e7b86de9ef08df92af9ce3269668055c1418da801088fed45de77a4c8a82c0480148caf8980084e040a73c79f204c3b0eac3cd3dd7ce44341580bab4049be4e9e9999595959191219be8085c54864f012e0a11668010e4c36dababab453da6ecaa68ea6b3bfab7b3893b4b81aa099a0aa0a304164b20ca82ba36d1724d38f81b1576d6ffa6ab10a179e0e09e8bc7770c1e269544403c0934a4e77b11190cc6175f7c31b3f193274fbefffefbdff4247e1fbefeee1bd3a260f3e2d0a2ae59aeebd8f0e096137b8e0d0f6aaff4010d592b3f5a5252d2d0d0d0df47efeefeec48d7d0e988a6028823819f21e8e2e903454f4eb0a7a7677777f7d3a74f474646befdf6db67cf9efd0ec36627d68264dbe68183ccda0cd0920565829b9bdb6ff5eaeb7a5b6bfa3e06270d30518058227051e0a2e0aa03460a1a2b688b335cc4b4e5c08f28cc8bf85ae8b31c929292300c332a0c3c7cb53fb25900560818a9da33960e0e0ee23a1532691e35690a5974e0a2e0a90708814ea75fb97245d4e9ee7347bbaf0eacee6a001f035095a252a90c06432dc41e1002e8ca81a92268ca4c0df28c0a035dd745d5f5b5151f6a5cd5b9b9f664dbb6335d1bfadaf20ed4fa5545fce5e8f6a957e45e1259d7d7ee5dcb174bb4025f434008863e0e7979796f813db3c2b42818b8289eb78c682a58e8aa36adc1e5db5fec3b7fb4fbeac0ca0375a02d2b1669e1efefffedb7df6218e6be36b6e1f4feac3dd5106d01362aa0290308014cd5addc9cd3d3d33ff9e493a94bc2df875988b5f7e2894da7f6aeeadc0cfe468010c413d0f6f6f6dfa4b4f5b32310610e1a323afef692a1e61a79de42624559400209fc8dc054cddcdb69f9f2e5b4ac08bd00472693b96bd7aeb1b1b1e075296de78f453415e0f3af9eaf7d555595486d6acbaabc03b50b531df0ea1f31cdc5e5e5e5223f9abbaf8652c575a8e088275a03c712c2cdde8b345994663b3636d6d6d626198e82959e44a80583c1c0db1fb87cf2f0d5fea6fe8352e9ce60a220ee4b94649880a12a5034218608911660aa289741c51bff7df46ed991adaeeb53e627db419405182b809a6c6262e2f7df7fff7beefa6b4035d74378d3b868697793e878dbf963f86aae6be874cbd9c3f91d75104304bdc5f399e66bd7ae15357b3fd5ae73e854f3c041dd025f20ab818a546060e0e0950b3a2bdc3d6bd281660ddab278de1c1002b899a3ee4ededede478f1efdadf3d22f611662391487ac3fbe6b69550290d5002118331d67e6557f1592d1e8bc008be0e0e085290e4e95f1c27b144f021f0350913262398685857df7dd774f9e3cd9b97327be26b872e78bfad3fb2a8fb648a53be3f90b269379e3c60d5c6146fbda4da7f666eda996e05943801168c8809fe1f1e3c7453deebd78025806106a0a0924499e8d42165d23cf5b2add79519addf8f8f88d1b37a2a2a2020202f04192df515779b485dd24d0c8f3061f03b05ca2174b8b8f8ff78e0a023d65e1ed460846a9c2b04c6b25b3f850e3926c57e0a2e063006ad292f156cdcdcdafbe09ee1fa50117b55a1db0b2b5a2b0adaab4a3f6c38e4d1587b694ecdfb8e6409d757e80b4afc634117279ec87fb6bf75e3cd176fed8b6335deb7a5b21c414f7b2df7c3f025c747557c3b2b6aaeae3ad210d7986ab02162493c15801108285a75359599948cfaecf8e7c7cfea87d452cc491c05675410c9996c3ceda5504be86e042cac9c901440a54a4c1d54c33806ce3b6342d2d6d6060e0df7754224c27962c9f5a7664ab474d9a58a21568cb0217e57038afaf4e39c74dbf80a1926a3d3131d1d1d1f1f0e1436a492cbd3a49482c4f3d5093128f20a6a7a78f8e8e4e938dd89452d8596f5c1804314450919aefacd7d2d2824f341e35696de78f1576d66bad64021705477540082e2e2e3ffef8520e62a18f35a84b83bd1ac410818b4aa53b6be4794bf0ac17a5394eeb8b519d2c6421c7727eb29d728e9b6aae87d94a52e7d0a96bd7aef973c21d9874171717369b8d61d8a5db9f1776d6c76d2f16468aa68af82c3c7370df7938025c74ef85de4da7f6423c09289a4052012345305103aa56f4d6c2945d1571db8ba3b716420c11d4a4b4525cf1ea5edf8d595b4eed8ddb5e1cda9857d1b33d6557856d59b4469ef782643238aa4ba5d9aef8b80a98fa40b5485e57e0cc0b3010f84bf26c808b4288292084f90cf3ececec1f7ef8013723643d676def4ea7ca78e05882871e982981de6220298bd388be2951b1b1b1972e5d7af4e8514545455151516f6fefcd9b37df94a312613ab196effba8a063e3a23427609b0342d0f1b7efefef7f857c7e47dd7b29e4ea435bf65e3c91df51079e7aa0b7d86945c4c0c00086615bfa3bbcaad34825114277a52b872f3067b20ac3b0d86dab29555c499e0d849800429048b0c20b363e48756838bd9fdfb656318b217cb4668a8010d2d3d3a769181f1f0f2c4b071519d09103172d882389255a11d29dc0454bd6677ae53fd81981aa14109740b48504cf7a49b6ab5161a00c9fb228858861d8d8d8d8a3478ff011cc2a89293ed4a8bdd207b8284e7a30565bbd7af5546db746ef6d3cbee3d8f060fb855e609b8396ac440c894ea773381c2e979b959565c873a754711767b888d8b028dadacfcf0fef02ac8dc0c3dc72992f70510381ff7b2964e1508c30031305d09605770b369b1d1e1eded3d3c3dbbc3c7957b958a215c493c04b0f3464895e4ec0456df29df75e3cb1aa73b3621603fc8d80b84434d9cd0b41592c565454d4c0c080887fff51cc422ccef6d5a2f8d4c7c7e7ebafbf9e2976f28b0bc045d774d61cb87cf2c0e593ed177a93779583a9a23893a81be4e8eeee8e47dc264541210d79fa023fe0a23853252d3566120247445381f0e105180142d00cb41f1b1bab3ededa3c7010c2cd20d84498c7e3a260a302fa8b17a63a78958425ee2cc5c52ffee33a8661636363f4cc084008a02903a1a6c2f6a68a8010a6ad6e2e5fbe0c56ba8010c00a017f23484015b318ca396ee0a5b7a5bf63da3d59b17f83f0610718034210e3a27d7d7d53db64b6ace9b976a6e1f47e593e1554a500d57673736b686878fcf8f18f3ffe3839395975b485b7f343593e55e8c910828b8b8b6832bd7bf76e4040404c4c0ca534d2b3265dd8571c09df12065b7d3a9d9e9d9dfdf5d75ffb6fca293ed4e85dcb178e55a6fe7b364a3241ba10680cd6865b3fe9e8fbfc3c588a667329497fd4dccb91c964d6d5d5ddbd7bf7f599f16fe22562c967d2f86d6b4d8a82208104ce1a80100402c14c19c1c14d4e95f1fa02bf841d25c10d2b2cd784432c11f4168b879ac5c5c5eddebdfb871f7ec007a2d64a66f4d642193e051248e0a20508413d883c2b53bd6bf9ec26c107698e420a3aaa2bc7386deedbed54190f7a4b16c4594bf26c94b21942a2041983b73e049980b6ac582c297cf3b2eeab03fe9b72166708d3f7030303f312c9f3926c218608c4258010244c358f1d3b8661d8e1abfd457bd7e251cbe8e8687e4d19e823a0218dd74002c71274e42412ad9f3e7d8aab6a1e3898d9be0e8f962001058a26208ba854eaf8f8b8c8f8928e1a3ca0793fc51e2c94406b31c9c3f9c2850bf8fc92d1be76eff9de9d83dd7a057e123c6b609b839e9c6438a9baba7ae68a32ef40ad5365bc04cf1ae284fc530d26d3e9f4b367cfe20dd25a3fccefa8837812d0b4c14c0954088010c42db46352b94c2673cf9e3d18863d79f24427c811e7537171716f6fefdbdfc07d895841b5bc9086bc0fd21c816309164b16d8689e3b776e9ac0c57f5c17f3b69489b643dd9c20dc4ce8453c740121a80792f1449408ccda8cb0c695923c1b882682a1fc7c37e3e0e0e0d9ede0a2966bc2c512ad200105b639d0b5c14605d4a5c5e25006832116479a9f6c071166e0ad0fe64af826d2028a318d46e37038efa7d88b255a01db5c864f312d0a66fd25586b856bc5c18d40d3025529400862a1a6542af5c68d1bab3a37370f1cac3ab6c3a9325e25cdeeafdf7c8961d8f5ebd7b5525c3f4873049232bef0d60cb41f1a1ac20dd35ee11dbb6d35b2dc5d482c0f5d657f730d1eddbe844948b6c52de7b7adb52d8b164ed3fa8bc512d0fcfc7c0cc3066f5e5db56f1d9ecb302f0e95e4d988255a41b819982aaa06936d4afc49025ad991ada29ba0b29c5edeb34d926703a1a660ab02aa52e211441e8f37752802431b8fd67187348f6546a552636262060606a6b2e7e79f7f1e191999cafeb78c9788c56e1250aab8123c6b88258289c2fc00e3cf3fff7ca64c5f5f5f5252525959997161d0a23427e0a2b8377277779fd6d2776356c0e61cb1442b0832064d198978aba2a2a259edd0cc6342880978ea015d1b0c842953ad10470683b17bf76e9d7c165821a029830f505093956019797a7ad6d7d77ff3cd37a0ab002a52a042000379b05802eeba106a8a07f880eaa90493994c667979f9f8f8b8d64a26f81b014513bcf42196e85dcbcfda59f6e1c19ad59d0d800aa78ff97e964c26f3abafbec20da3ad0db6af8895e4d900c712028dc1491decd520d4141cd424c946fff77fff475cc5c6370785de94acba28da3abeaec0a8c03b6b4f35d0b5e705b9420c715e92ad70f20a34c63510d29dc00a29ead88cfbad4da7f6161f6a24a43b81913c68c9e2c6987a3b4e4dd46118067606a0210f446df148a2a587138bc56a6e6efeeebbef7e3703fe43788958a18d79ea2bbc84097e1345f0357c459e26ef406d6863dec2540761d0335b34cdaacb5c5a95001c4b3c56b0f1583ad305be30e55f8b7cd055826013073a65d5aa55a282ebe00dd9e201960b63ac0d580e0101017c3e5f74eafefdfbf6497e5251360b38560b63ace5226c4d988e26de8efefefe6c36bbb1b171aa1f9d178a82893a20528010407d31eed2c048759e3b5135984ca3d1525353a7a657e47ca92fa57c100210b5e6b32ce9747a5454d4be7dfb16252c0547753052c0b70841550a74e5c05c093c2c24c3d1d0d0d00f22edc1511d0ce5414f0e34fea547551a22cd511635333313df7dd360eb83350208014cd4c087a4cf7220b93b6564644cbb4b4f9e3c696969110804dbb66d1b1a1a7a830982378b17c4aa3cdae2bf294718e5702c81b844d2df78dad4f692241765d5658a275a0317c5b76570ff2f42eaee8aa0fa5c083611de4dbd255c2e775a82602a6edcb8e1ebebebeded1d1616b675ebd6695b25131313172f5e3c79f2e4975f7e39ab871f1d1dfdfcf3cfaf5fbf3e3c3c7ce7ce9d070f1eccdaecd9b3675d5d5debd7af8f8e8e0e0f0f8f8c8c8c8c8ce47038cdcdcd434343b30e7df538ca3c5f9274a48d8e9fbd8b8b4b4c4cccfaf5eb4509b6d3372e4944a060a4028e864031118f4675fdeca9542a87c3d9b265cbc8c848dbf963e28124b0d40417537196a59eaf0399be94cd663737375fbb764dc48c15fb6ab8c5d9743a9dc7e37df4d147ddddddf7eedd7be35980b78617c4d25ee9c3aacb147aec384bb05579df0199352f804376d952ef5a3e249020c20c34a40159d4d8d8f8926a2e0ac126f85817e3a0a8bbd3d434fa4c3c7ffefcd6ad5b5f7df5d5fdfbf7df4270f0f0e1437c2f6c7474f4d509f4fefefeeaea6afc791f38706066e35bb76eb1d96c2693191818181d1d5d5e5ebe7bf7ee274f9e8862f3e1e1e1d0d0d0808080b0b0b0ececec9d3b778e8c8c4c8bdc7ffae9a7fdfbf70f0e0e3e7cf8f09df543af8f17c492cb70f1a9cb1046a05c14183ae25ad2af58a0faac4fa05471c15e0d9f4de6b1cc4e9e3c39b501c1c5009409108f52a9d4dcdcdcb367cffe79c7df1c7e2b5e10cb59c0f5a84913ed4f01cb0010c2f5ebd76715fb71ec597ccb1a609be30b34ad007b3a9d3e6df2b2af8865b07d5d5d5d1b1b1b675671cde1bf1b2f88e550ed65571ef382585116a027d771b86ba64cf1a1c696810e7a7512a8488987a3961e4ea5a5a5a2507a2aae5fbf3e5702ffbf8917c45a92ed2a4c918b7e4c7d8578c39b232f18035cf4d095d355c776e08b799560725050507777f71f553a328777162f88f57e3c5958ea24fac59380636999c3002e9ab643b073e050e7d0a9c49da560ae04068872885d5050d0d5ab57e7583587997841ac053c7b6152ea5f3ff1446bed953e3e7519c5871ae35bd62c4826e3d52c62a166aeaeaee5e5e5af4846cce17f1c2f88a55be00b8126104f7ab130c4135afe4660a288a7f5c463500b4fc79c9c9cffcd77bce6f0fa7841acdee1cfc05a057c0d2196080928c491c0cf082c85db6760ad231f6eebe6e6969f9fff9f2b9b9cc37f0d5edad2c1eb8440550a3465847b1d0841c256df96e1ecebeb5b5d5dfd96e73eef5a7ee7d0a9caa32dd5c75bd7f5b65a7f188996b25fd1bea6efe3c2ce7a7af5f4d7c8e25bd6aceddda9bdd207c3b075bdadc032784d0364f9d45de77aca7bb6796fe0737794bea225bd3aa9bc671b849bbda6e65fc2be4b27806dfe6f2a7917f012b1262727f3aad67cc0b1816054328264c274f4f4f4e4f178274f9efc43b206ca396e56a591ff7a81440ec2cd20d434b6b9f097da932b62e533699a29aed38eeb16f8ea16f84af31c6fddba055c543ad1a1a9a969560d53517f7a1f6fe787c22d61b21ab8eae4eefde8971ad3ab93f40afcc050fe975ed97d4df86fca0143f9d731ef1dc7f442bfa74f9f9e3e7d3a3333332121a1a8a8687070f00f8cd009e94e1061066c730a8542a150c0450bbcf5c1c760dfa513c045710f84e17b475c14c3308d3cef0fd21c15a2edf123a297a71767b84094c5fb1c9be1e1e1a9c47af8f4315aca062e3aab378ade5a082c03a2971385425108b30584004cfd9f7ffe193f3b4d905e9da498c5006b15ee96d5c045935acba6aa4a6a2d032e5adeb36deac1f60bbdb89d538feb16f882ae1c5e06376b833f0bdee9ef63492d5b2a9f4983480bdc5fda944503c712685a944a6ef1a146202ec1cbf156773580bfd1cd9b37d5577819ae0a00630570d68018e2826432702c310c5b9c411311abb0b31e2c97dcbc7933a9b56c5d6fabb08a8663094e336a973df520c4142f262e3bb2b5b2b2924aa576757525b596755cf964aae0e5db5fd0ab934c8b8281a40c76aa1fa439a2a56c88216218b6f2405dc3e9fdc036077b355249c47b29645cb94e3ecba3260d5cb4c0558754128137c6a6106bc5fe9a75bdad405603277570d60037ddb776dbdf08de7562b9ae4f8178123e7057756e065b1588253a57c49bad0e015db9e1e1610cc3cc568780e5128140802c778730534091ecec6cb9483b882182b9d2e4e4a47ce60b62991787e24f2ebe650db8e9422cd1d2cb19cc9500215cbc71756aef40d3026d59083565d5654635af5a98ea70f9f2e5fbf7ef73b6af6654274f1584280b7a75124459808a54426126fef219582a3f78f020a83e1798061062c2603080a10d5c543dd713c3b09abe8f4147ce92eb65c072008636b00cf07d7711b1a29a572d4a730a0808d00824838a14c41245cef24f81779a58847427e51c373092073b55b0530515294011777777da5a9ef64a1f11b1740b7cc15c492010a82cf7001f038530dba1a1a189890948208191fce9d3a7a7124bf4e43c6ad240458ae1edfee8d1a3a74f9f060505d5d4d44c2d2b78fefcf9eaba4a70d715beef1a610e4cfdc9c949ef5a3e9055a70ac6c5c5d1d62541b00968c894979727b494c865504143a6aaaa8a5597099a32aeaeaef7eedd636dc8002e0aa83267b340318b21196f959a9a3a3e3eceda90017e86482a6d7c7c5c641e58216085c867d298b5190b92c91b376e7ca7be04f1ab78d7893535c6727171090b0bbb72e50abd3a4923cf5b442c97753c202e1108049a794cb1442b85187b5c5c279f057a8b4b4a4aa6c658a227372fc916d4a52393e367ed9a5e9db463f0b076becf9d3b77366fdecce3f12086080821a2843f3fd90ec86ad304e9d549106e06ba72434343dc1da5fa023f4008cb972f072e0abab2d9d9d9188605d5e7ea0bfcc048c1393b0c22cc24e3ad2e5dba841f072f7de918bb1b376e88cc7bf6ec995212058c14c046457ba50f384e9fa9df71bcd3c4924a77861822b0cdcf9f3f7ff9f2e5a1a1a1fbf7ef631816549f2bc1b30643f9e1e1e1874f1f07d5e78289a24020505fe16554180821a61886f57d7e7e4132195408454545b3120b98fa4052b64e666118a69ce3a69ce336f54b49ed177a7d3766e90bfcd67436601856d7d766fd612420049fb830f0d2039ad654c1a4d6327a75124498e15ce7ee28d52bf00384c0e7f3c143176c54d0657e1886e915f8412c1198facb0bf2c0430f7c0cfef6b7bf098f9b2b59f851ae5fbf2e32afbc679b7aae674a4a8a66903d445980aa94e88ba07f0abcd3c482280b305304b6f9cc64075821a0bf18e249aab91e60a684bf50047e4660a200ba728cea64e29a30d0908658625b5b1b449881b182705548d1c49f1cb7a504346480aeedbb31ebbd1432a849f5f5f54d8d6380ae0d9a32e06f64551a095c1434659060bb9494146e4b09a84a4d15649526d3d6f2c0505e442ca06ae2c45ab1af06d4a581a2e959930e0c1dfc8b12870e1d820023309207b6b9d78665c0d00177dd848484c78f1f8bcc8318e2a234277d819febfa14b0530514797599e4bb86779a58f82a1d09b69b49acf1f1f179f1d6c045fd52a3f0d05e2010e0af257a7808bf7a40a7d38383837ffae927ee8e52e0a2fa414e78bae1fd586b7c3ddfb0bd492ac616b8a84e22834aa57674744cdd501f1f1f6f6d6dc55549c659512894a4a424fcff3c6edfbe3d55303d3d7defc513b8e6e1e1e1cbb7bfc0a5f87c3e8661870e1dd2643b031745399e341aadbdbd7d626262727292b94c6839caf1f4f4f4c46b2a45e63d78f0c021d91f6f6093e0c36030667d6dee9dc53b4d2c0cc38e1d3bc6e7f367ad54be70e1424646864020b872e54a6969299e9a6a6a6a2a2d2d3d74e8505e5e5e6565a5a850b1a3a3233b3bfbd6ad5b2323237c3e1f6f3c3e3e3e3838989d9dcde7f3dbdbdb67f6323e3eded9d9c9e7f3f97c7e7d7dfdd442eda982f8f1ddbb77f3f97c3c418af7525b5b8b61d8c4c4c4f0f0b04020c8cdcd6d6f6f17ad0f1e3f7edcddddcde7f3737373f1604b24889b373a3aba71e3463e9f9f9595f5c9279fbcc9dbfa9fc7bb4eac39fc49f1ffdb5178250a656e6473747265616d0a656e646f626a0a31352030206f626a0a3c3c2f54797065202f584f626a6563740a2f53756274797065202f496d6167650a2f57696474682036300a2f4865696768742036300a2f436f6c6f725370616365202f4465766963655247420a2f42697473506572436f6d706f6e656e7420380a2f46696c746572202f466c6174654465636f64650a2f4465636f64655061726d73203c3c2f507265646963746f72203135202f436f6c6f72732033202f42697473506572436f6d706f6e656e742038202f436f6c756d6e732036303e3e0a2f4c656e67746820353231393e3e0a73747265616d0a78da8c5a8972e3c69244371a37408814258d46e3f7ecffff878dd85fd81f98597b34ba7893b88fdeac6a00a424c75bcb081ad48840a2ba2a2bb39ae2bffee7bfadf1476b3d9d0b21acbff9c12fa52570284bd896850327729b1d95ed845e742acb79b2f05d1f7fd6f5fde3ea398e66aeed084b5b420babb7746f59ada53b4bb77cde8d6ff99c5ef5c5bd04ddc51ce3bd847415feb1effbcfe82e1fc0fc346d7b2a8aa6eba370160509a1e707687babac9a3fbe7efbfefc330aaefe7a7b7eb8f9ea28f7f1ed976d3b755defab4de807219e44f4829019c4fc3abc05743c8fa6d77788c570a2395292a0e37fd2fa673f5dd7238a7130fb727d7fc84e5951710094255551564934c345433feefa0e706da980584ae548a7ac8f37e9559eef7b02daeabe1d105b1f10f7ef11334acb1eeec237e2b77855ff10b438e645e0866972e57be1c3cd6f87222beb560b5b6b59779de706b8e2f26a09d0f7d75f9ed6cfc0ed2bafeb1a4959d6691c7dadfbe68c587f40ac3f65a04da165948cd819d00bfb9f80165959da4a2dd26b935bbe17dd2eeeb2b2a881dbb295ed354811818ba96f375f5fb6af38f16c1741ad9bdc779ca6a96c20e939ccf4dafc23c403c401e8f0009cdfff2f6851352d0e2cfdcfd5ebb128f8933209ae66e115e16e1aa5dcaa6938c5e96779758db81e8aed3edffa9e5aa657c7fce029a501b16f346705270997dd3f428cc31911d31fa8ff8c187fe7b9f880b3cbb22898ed4e596f39693c47bc912a96740e791e0669dd957955841e9244fb8efbede64b591588aea3c4feb8edbbc6715d466c12b73764c2e71f10db2335a9f7d0993dccf370c6fc27c4e6429ee7b95e824cb0a477c8116c378daff07bc22dd4213ff96e883c066920190887ee7d0757ee3787d5f17408f04b420ca09de80ddce9f880783a2e411bbe63d04c2667d09a2f212c7181f8e2129644e2e65519f8611cce46eeb4d2384536ef4fa7d08b7fbd3d45be9f0421961e797c2cf65d5323f008397283c86e022af47bee1fa3280c195f82fe80987ed46560c7d331b12cfbf29379b54fc2544ae7fbd35f42da0f370f3e91869582efb4b53fed677e52d6d973b60768217a4748df5692680ecfd56ba919aa1eef23dee7b1fc18dd735698059fe0f59f0b712448ebb260555e564a79c88a7d7e4ac285ef44e876655399cfa4718cd43fe49b5ed75ab781e3fa52290a70ab2f2919afe27d564c01fe58796a0cf07bc49a724ffe3d624331e79552455d21cc788be8b67d8724f66c1f1da4ac2bd3cceee68bafcbdbdbab6b2013ba17040e29d18b9122f0088257731408e3624afb53e55d9e7c44fc21d217943e203e734dd9d4a11fe10f10ecb229d0443cc7776deff1edb1ac4b231b6ca11fdffef2a42da596026f2d29b4b435da2e9d70573688c5485edc353ef09a09f39833268bb43536795a31f911b12905eb826bf8a00ed2e133125d7a995e0fb8c187b65aed5ed1f9703cad7e218f5d1be07b4229354e07c474792138d48c587c2a1b75218cec21c6136266a491da3b755ea9e12a9f9689afe279515e9669ec0d9567e9d56eed48bbeb9b657cf5ba7d699a42a39f2b871a35093a028d24a13b135e8e31c1bea872cbee2d9915a525d09bec2098297b4c09eb12f1c0ee4355e8560d694e7f372a12c68a6e87ee8d8687f33088437fb6396de330b14963e9344a42d743abf39dc4739ce7d52172911782851b61e5c3e29430908ddc7dc74b872ccbaa8a299f6ebadead7d3f4e93eb779527f4d83807c47835b4c24c39ca28acf4a9384829bf2cbe401ee1364788b4ece02a64f0af879b7b1b58748f6eb748123cfdd3fa17bab4a2d2a384130368c22a39b2a33a7f871802a06ebbdfefffb06dd750c47c663dae5eaaba762162353d33877992daa33cd43d090956abf605e232f66344d7713c93584918e378d9bef59dfcf1f4230de3380c1d5be4c5699fedbab6091d74e996e9420bc931066f085b88f788c77c8586a9ea6639bf7ddd61f566a46c2d1b6b88e2aedb1a62863ec295f7de370c1acb083f39248654a753160709a043cbe343b7f33bc0350b75375f7e7ffa117b715114fbd3baef5b25852bec40292026761313629c88b66b2048601da220c6715e4c89dcdbfe76fbafcde968ab00aaa6d7a4085e362bdc175de865fd13266816cdd09246c698cc0e0aa69783601de5bc9436926c733cdc2cbe5e2537dbd30189618dacb388d3b23eb9ca8e5490384120116e3d48368499780d684198b8b5cecb220e62e4189986b2643e26c4c80a7091e3f8be1be19150369e170071ddb6d0024595fd71ffafbc3ca031918e1db2a2bd488f6e6a25041d0219de84946738cb8a0ce911f9316e89166d48c7b125a8cdeab9263806c238284e0c290676838fcbca0c2a258d52320db7df0e195a2aac808da3aa3be49ed15b8811fe757f3a824e0337eabbb6ef3bd44ccfb25b9fcdce146cbad7c4f0041da4c6e925e141b2f28887969078ca5fefd7909aa366efb46e043d6e679a086335eda3a78b4999d7057cee22991bfe62d3f085c5373e6bdbcac5ff4d15dd2fef4f458ed8bb641ababc3a85be674c03de925d60b3030176e1297bd95bf631cb57bb4d51415db8c68338ca816b2acaac6d2b3cb7a7dced610dc4659ddb1c720191297b1bed43c26a9297a0c597f41f58a18669f0a29fab17320d5c7c4994ce922be6d0ce75c2bc3216930a749ecc5d47edf3cda1d843bbde2f6ef7a7ad0f0ed08834701baced850beed5cbe63589ae9651bacf4ed441aa220515581614fd3c4ed0410210b0b000b7ebeafd71e34925742b86001b6aeb0d45981f8f5cb7da654738f6ddf1c84546214fa385656587a208a5e379f16ab75d9250c1334b94f82249bbaef55d8525856f984749df37a0206843a6d19ec4ade16c21d4eff7bf0b09e56e8741f2f3ed7973da5f749099e7a8ed61436d6f76b5dabd190b2fd87ab0f4410633110f0dda9c211f023cbf269a8369c8d14fd38871033dcc4e56044152d4c53e3b9ae68a03d5e2d80a1cfab47a0c1066e461df931d66a565191ad146210af5e3f9d7b7bbdf1ca58cc91b3bc8179bc0747022f78b9baeaf81b8ad2b684eac1aa7876978c2b0db48c662e8c0dc2cd83454811f73710f1a86a38e64c8e1edb1b6c71cb8136470d7b77979cab2236ee1299b525990d3616fd233ddf793e0478b4e9ed6afcbf486a94d8274b27c6f3a48e821b17a5c0b4986488414808e1e77ecd293a0185bee85b28569288f4865693bdf9f7fa24cc0123eeca625b96f4b3046e4c57553aeb62b4d34af011d5a80ec42df506b41747130f17350ce76451555198557af7be46e888bd675d15b0d10e7457e386ea8fd08113b3e45535307e12911b31b7dbcb74c9b3606c492937a011129c74b93c5cfd573122c5a1e91116ec73792abacca7db651c4e84d845f1aaf4ee83bc0d5148b9e96d112a36990936f90b328a9ea320de748a9be0325b5008418b75d15b941a8203e6d32fdba3194372a63cdea5c7cb2fe83d42caa66340d113a84ef849e8a1edf9e46b3a3ef164b360d0b73d9a1a19aa0f0c1882dfe27618d82cb2ca65cce9660f22c3f30e2ae692bea20a85c6138987a075859e8a995e0535a8e5cf1de481b214f5abe6c9a90e77d641a6aea2b6c1ad8ec34a56955a8e9c7b79f1eb4fa782f293a7e356f8d28109c8de27256463127ebef399be3db3e5b23a2cb748e3c46800721cfafdc4738030c62392016c6e67c10f202aacf67cab71de5a355956d09ca459f82d959ed56641a7a360d52bae49288f5e171601a7057622423bcc554dfe2b260401ad420007499ce9aa676942cab1c819f47332e082a85618829ce7047c46abcd0e41806e89e0bcaaf52b2ebc28c1956fbb563bb20fb6502d3f08cb6a7dbd69806931826eb243b5f6172c1d0a99497e615a6418db3888e25b2344c0981afb9ec342f1383d606ea88580ae393c7d948565659b987e821d3e0276130db1cf77174653321a2f2d0adc834b8c6343ca2f8402ca319612ee22515c3f9d0af2eb322ab4a4898284cd4cfd5af240898291b20ce8b2310bb94662d21d6670b7dc16e8c584ae3cd20244ee5510af5e5fa9e4c03744191eff3cc7502541e72cfb42a47d9687b88ebd3ea0979cc03866e70ec5479ec2629ae62243831ba2a8a0ec4dce194817f4237520ef99c55cf821817420048c5f6c847ac54c76e4f98c418687978ee09b1995b27a197a02d1886063d431abc6c5720b11fcfff9b8633360d322fb3fd69d3b575088ea3f6d19b61030fcdb518b978442c26c4b8cbee74bcbff98a7e0479a52056848626ac74d76a8b75208421330ec11b72cbfa8878acb95399a3e1e1a27fad5eb02ab78b3bd689f481bbf9cdf7e73f632f29ca1c58a12540f970ef01b262300da6b38e1caaad4371cce882f12c4a2f8789a7325ba6b7a8ec96e329694061f76cf1992c69bcd29da79a427f669ca9eca0359115641a0e879bf9d7ab194cc3118a621abab169c85ce9440e4c4318280715d28f225358c3a4417216ecf33de4f51ff7bf4324eef3d384980c65d5c095857e5c35207da2177a505698663f83a807bf4452db524c1d6434d2efa658307a317510857c40846046a260068f3d999dc1341855c90348a67c93cadd8098e5f8a938e11677f35b5b2a246ed3b4c73c63d320ebb6771dcfb61d5c7f06b71ec4d2d42c2963dbe2836992dc070f2998e12e662b438cb5e9d5550db83859ce6f605520dc1078cf09d6fb2d8f9dfad13410508e4e67a37dc86e30386630222db449f82ea49336d327a9ee970f279a6114000dbb6e281f189024207ec929011b42532c0a36d13be97acb36792cb5252f0798e06d48fbf56e8b2553ca230f4263270f1e04b9db76b52dedc134e81e946f930826452108318546d15d183187466bbd3f1d96e9f275b75e1fb68631e0f41e6e7f030b91b9041cdb2d69b38170874144a0a9bf4bf3e86c9c28d2f0a61473263cd30a29c0b07a70a088c4f2ea065edff342583f336608bd709ea4877c57d5396e5bd60512639fed1c1ae198f4ed29f78449c2212b04dfeb2a4957870d9e1c017fd96dcc2ccf7769470a1c0cdc5004abfd7eda09c0fd5ac917b58c196193370e5a7886286c4e069555157ac7eff77f2caf6ea192bf5c7f454d6c4ebbaed7a6f2a08c1f965f10d1ce2ac934ec5f697588d1bad1370c65c788c5542db0c0d7e932f0a2593c4726bc6cd7a3b924538c6ae1e9aea4df331fc869b754fced4e1e8f1660708aa629caea7671ffe3f911153c56a4301da4ebcdee096db8dc5fdfc03740bd906920ba300aae1b1d9a35d688e9ad66023654395a340a6e315b8e7350c67df32dab326476ddf4df9ffe4295abb1917ededf358dda36db7859715cce6ff3ba0e83f469fb7a4da6e16469ead85971f8f10cd33023d36069687f320dd2466735a33db333641ab5983cce208ec5b435014309677d3dbbfef3f557d78bbbc51dd91cc21d20bf1fdfa0cb53575bf4678319f9bc2fc665a769b4a5403a646a40b54ebfc387c3f9db6e177871e0e1e9ab5eb7404ca6e1b4a539ac14b10a90bea6fe86218ed4d2fab077212e630c5d5c77fdc3edfd6abf853e51d2dd1e335438eda4214f5cb54caf577ba4bb852a577fb7237d96f382551b39039bcc2fa87216a66553a7f1b5d63d7e4067a801c4f8906d2101c8f15b66c242c5cda661687b425a178e69d4154398d13e8a24a4910b1c71561f7d1876cbde1ed0621c12892c1569aa6fe997cd8bfc94c7f6348cd4168bfaf3809daebe9c2fd1d290128cb86fdadab1694a4d95624cc3e81bd8fa77a657cbf3c6c568cc06c4c3bd2a631ad0a78219121d34023e05e56f0f07b849934e905c4feb2766f6bfd92d255d0fc4829a089d3b4e500d344977a57d2dcfdd9cd83458ed32bd62d3609a2bf8be9703a9fdedc6853cbb49cb9e648cad68af9a13dfbe9ddf154d51b78db25df81df0b729f497cd6bdf698f26efef1183ddb8afd882b75d784b57491bd500a6dc8de315b14c17f00dc69b9524738ff37806b3860bf0768e36335ff95ed39eb580c13d8a5b1c6021b48f94de0aaabc9b6f4f9bd7b6a14a03fd6f0f5b98b4b6697c1b425fab77254c49ccc34871fe9e82112eb3f81a9c00ba99c62bc82b529b641a7e1151f43476323b8896b192c21a49e30362ba17383f2ba1336b5cdc75832848f332076e9ff6aa1123ff5f77bf9d8acc18883f5ffe44270562d605c62fe9b3b4d01c5a31f4ed69f3061dd28a82f8901f87f10a1a5247e3953c8769705ddaaf6f87266246225a5f8e44dec55848882a48b9797afd8d1d7b5937300dd0438fab17c4d827ea849a901c206b73d880901d47f15c8fe65b6a18049eb7780de24ffbbb5814e5127594d97ab7eed93e820e23c7431ad07893373607bbce86cf1217330631e53175107400b0dbc851d4417c2f06f16b5dfc5abf46013c055c4580d823466555f9ca33bb2216d7891aca6b94c8e29c121f77a44d777095638ba86f6b333fe6af70f06850ea61b7455f9a86695b76189721c682444ff2fdf927de5e2557106ee65f7987b24515354dffbc7e6dc934d80e4c03100b1e80706fa2cabe70ff8ef519b118f6c58c61e6c63f0c15066ae3fe3cecd29aa19bd09723ac4bebdff67d5157902e08ea22bdb9bb7e28ebfe65b79eeeb5982daaba706d3754e1cc4942153860b09efb941eb647ccc8537dfc0ecbb86a17f3b39113cc48c5e86f1e4e0fec66dc831c667c6312bfdfe1b486b113d405220d036169b8c9ab1ab8b72bc3608e723a381ba364e83a66f578977a18c7f11c679a545c6c955eeca14ffbbb6690ce6bf4ce344cb8c771c8901562dafd3e479a3a884f1d6431bb06f51ef213ae1af9299804866dda00efa9adea495d0d3b0de3c0c8125a7efa26cbe7184f5ba5dce1d831709218d3c0af24d726bae0b1d319318d5736c71d143d7510deabc6334073c2dd14558990784eb8396e7027fc86e708c35758a0d96cb3553fac21eb02de7351e754bedcdf3deffa77e7ed30a381447b21eaf559850f064d4e463aabcad7edbaa7225bd8d4c86c74086e61f83be7e1f6a16c2bc84eb6baa2691bb01bec3aadaac5be468eda5032e593ad248e9afac888f8f27b0a5677b1237d864eca1b1f867b3f7fe9445c28a1336270058f7743fc26113614dcb128a1b7e82b71427a2e3ac8b7b7ddbaed9a344ea0136133c06e46ca1a4bcd437b8aba1c070482fa88ed1059bd63b7a1e18d5ff03b6f2bf150818fe9fb911fc5f1c06ef8a35351fc76fb6f288734e6711eedaed0c4f769fdf6b0fceab9dcb3a475bfb8c5ed36872d9ca2ef7a04426b495927e44817ac0bc4184afd7f020c00ca02c3510a656e6473747265616d0a656e646f626a0a322030206f626a0a3c3c0a2f50726f63536574205b2f504446202f54657874202f496d61676542202f496d61676543202f496d616765495d0a2f466f6e74203c3c0a2f46312039203020520a2f4632203130203020520a2f4633203131203020520a2f4634203132203020520a2f4635203133203020520a3e3e0a2f584f626a656374203c3c0a2f4931203134203020520a2f4932203135203020520a3e3e0a3e3e0a656e646f626a0a31362030206f626a0a3c3c0a2f50726f647563657220284650444620312e3533290a2f5469746c652028496e646976696475616c2050726f6772616d20506c616e202d204a616e6520536d697468290a2f417574686f72202820646d696e290a2f43726561746f72202847726173736c616e6473204950502053797374656d2d204d69636861656c204e69656c73656e20446576656c6f706572290a2f4372656174696f6e446174652028443a3230303730313330313333353535290a3e3e0a656e646f626a0a31372030206f626a0a3c3c0a2f54797065202f436174616c6f670a2f50616765732031203020520a2f4f70656e416374696f6e205b3320302052202f46697448206e756c6c5d0a2f506167654c61796f7574202f4f6e65436f6c756d6e0a3e3e0a656e646f626a0a787265660a302031380a303030303030303030302036353533352066200a30303030303034323139203030303030206e200a30303030303138383830203030303030206e200a30303030303030303039203030303030206e200a30303030303030303837203030303030206e200a30303030303031363834203030303030206e200a30303030303031373632203030303030206e200a30303030303033313034203030303030206e200a30303030303033313832203030303030206e200a30303030303034333138203030303030206e200a30303030303034343139203030303030206e200a30303030303034353234203030303030206e200a30303030303034363233203030303030206e200a30303030303034373231203030303030206e200a30303030303034383330203030303030206e200a30303030303133343231203030303030206e200a30303030303139303530203030303030206e200a30303030303139323439203030303030206e200a747261696c65720a3c3c0a2f53697a652031380a2f526f6f74203137203020520a2f496e666f203136203020520a3e3e0a7374617274787265660a31393335330a2525454f460a, 'IPP-Jane Smith January-30-2007.pdf');

-- --------------------------------------------------------

-- 
-- Table structure for table `student`
-- 

CREATE TABLE IF NOT EXISTS `student` (
  `student_id` bigint(20) unsigned NOT NULL auto_increment,
  `first_name` varchar(255) NOT NULL default '',
  `last_name` varchar(255) NOT NULL default '',
  `gender` enum('M','F') NOT NULL default 'M',
  `current_grade` tinyint(3) NOT NULL default '0',
  `prov_ed_num` varchar(64) default NULL,
  `birthday` date NOT NULL default '0000-00-00',
  `address_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`student_id`),
  KEY `last_name` (`last_name`),
  KEY `address_id` (`address_id`),
  KEY `prov_ed_num` (`prov_ed_num`)
) ENGINE=MyISAM  AUTO_INCREMENT=308 ;

-- 
-- Dumping data for table `student`
-- 

INSERT INTO `student` (`student_id`, `first_name`, `last_name`, `gender`, `current_grade`, `prov_ed_num`, `birthday`, `address_id`) VALUES 
(304, 'Jane', 'Smith', 'F', 0, '4321-4321-4321', '2000-02-04', 0),
(306, 'Test', 'Student', 'M', 0, '1245-12354-1233', '1997-02-14', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `supervisor`
-- 

CREATE TABLE IF NOT EXISTS `supervisor` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `egps_username` varchar(255) NOT NULL default '',
  `position` varchar(255) default NULL,
  `start_date` date NOT NULL default '0000-00-00',
  `end_date` date default NULL,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=127 ;

-- 
-- Dumping data for table `supervisor`
-- 

INSERT INTO `supervisor` (`uid`, `egps_username`, `position`, `start_date`, `end_date`, `student_id`) VALUES 
(126, 'admin', '', '2007-01-30', NULL, 304);

-- --------------------------------------------------------

-- 
-- Table structure for table `support_list`
-- 

CREATE TABLE IF NOT EXISTS `support_list` (
  `uid` bigint(20) NOT NULL auto_increment,
  `egps_username` varchar(128) NOT NULL default '',
  `student_id` bigint(20) NOT NULL default '0',
  `permission` enum('READ','WRITE','ASSIGN','ALL') NOT NULL default 'READ',
  `support_area` text,
  PRIMARY KEY  (`uid`),
  KEY `egps_username` (`egps_username`),
  KEY `student_id` (`student_id`)
) ENGINE=MyISAM  AUTO_INCREMENT=994 ;

-- 
-- Dumping data for table `support_list`
-- 

INSERT INTO `support_list` (`uid`, `egps_username`, `student_id`, `permission`, `support_area`) VALUES 
(986, 'admin', 303, 'ASSIGN', NULL),
(987, 'admin', 304, 'ASSIGN', NULL),
(988, 'admin', 305, 'ASSIGN', NULL),
(989, 'admin', 306, 'ASSIGN', NULL),
(990, 'admin', 307, 'ASSIGN', NULL),
(993, 'mnielsen', 304, 'READ', 'Career');

-- --------------------------------------------------------

-- 
-- Table structure for table `support_member`
-- 

CREATE TABLE IF NOT EXISTS `support_member` (
  `egps_username` varchar(128) NOT NULL default '',
  `school_code` varchar(255) NOT NULL default '',
  `permission_level` int(10) unsigned NOT NULL default '100',
  `last_ip` varchar(15) default NULL,
  `last_active` datetime default NULL,
  `is_local_ipp_administrator` enum('Y','N') NOT NULL default 'N',
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL default 'root@localhost',
  PRIMARY KEY  (`egps_username`),
  KEY `egps_username` (`egps_username`),
  KEY `school_code` (`school_code`),
  KEY `is_local_ipp_administrator` (`is_local_ipp_administrator`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `support_member`
-- 

INSERT INTO `support_member` (`egps_username`, `school_code`, `permission_level`, `last_ip`, `last_active`, `is_local_ipp_administrator`, `first_name`, `last_name`, `email`) VALUES 
('admin', '0', 0, '192.168.0.102', '2007-02-09 02:02:13', 'N', 'Administrator', 'Administrator', 'root@localhost'),
('mnielsen', '3201', 50, NULL, NULL, 'N', 'Michael', 'Nielsen', 'mnielse@telus.net');

-- --------------------------------------------------------

-- 
-- Table structure for table `testing_to_support_code`
-- 

CREATE TABLE IF NOT EXISTS `testing_to_support_code` (
  `uid` bigint(20) unsigned NOT NULL auto_increment,
  `student_id` bigint(20) unsigned NOT NULL default '0',
  `test_description` text NOT NULL,
  `administered_by` varchar(255) default NULL,
  `recommendations` text NOT NULL,
  `date` date NOT NULL default '0000-00-00',
  `filename` varchar(255) NOT NULL default '',
  `file` mediumblob NOT NULL,
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`date`)
) ENGINE=MyISAM  AUTO_INCREMENT=379 ;

-- 
-- Dumping data for table `testing_to_support_code`
-- 

INSERT INTO `testing_to_support_code` (`uid`, `student_id`, `test_description`, `administered_by`, `recommendations`, `date`, `filename`, `file`) VALUES 
(378, 304, 'Psychological and Behavioural Assessment', 'John Bolton', 'Behavioural Intervention Program, Motor skills intervention and safety concerns', '2007-02-06', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `transition_plan`
-- 

CREATE TABLE IF NOT EXISTS `transition_plan` (
  `uid` bigint(20) NOT NULL auto_increment,
  `student_id` bigint(20) NOT NULL default '0',
  `plan` text NOT NULL,
  `date` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`uid`),
  KEY `student_id` (`student_id`,`date`)
) ENGINE=MyISAM  AUTO_INCREMENT=58 ;

-- 
-- Dumping data for table `transition_plan`
-- 

INSERT INTO `transition_plan` (`uid`, `student_id`, `plan`, `date`) VALUES 
(57, 304, 'Attempt to have Jane participate in some work experience outside of school.', '2007-02-23');

-- --------------------------------------------------------

-- 
-- Table structure for table `typical_accomodation`
-- 

CREATE TABLE IF NOT EXISTS `typical_accomodation` (
  `uid` bigint(20) NOT NULL auto_increment,
  `accomodation` varchar(255) NOT NULL default '',
  `order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `accomodation` (`accomodation`),
  KEY `order` (`order`)
) ENGINE=MyISAM  AUTO_INCREMENT=58 ;

-- 
-- Dumping data for table `typical_accomodation`
-- 
INSERT INTO `typical_accomodation` (`uid`, `accomodation`, `order`) VALUES 
(1, 'Environmental: Seat student near teacher', 0),
(2, 'Environmental: Seat student in an area with minimal distractions', 0),
(3, 'Environmental: Seat student near a positive peer model', 0),
(4, 'Environmental: Stand near student when giving instructions', 0),
(5, 'Environmental: Provide access to study carrel', 0),
(6, 'Environmental: Use a destop easel or slant board to raise reading materials', 0),
(7, 'Environmental: Allow student to move around the classroom', 0),
(8, 'Environmental: Modify text materials by adding, adapting,or substituting information', 0),
(9, 'Make materials self-correcting', 0),
(10, 'Highlight important concepts and information and/or passages', 0),
(11, 'Prepare recordings of reading/textbook materials & tasks', 0),
(12, 'Environmental:  Provide an extra textbook for home use', 0),
(13, 'Environmental: Provide graph paper or large spaced paper for writing', 0),
(14, 'Allow use of personal word lists & cue cards', 0),
(15, 'Increase use of pictures, diagrams, and concrete manipulators', 0),
(16, 'Environmental: Increase print size in photocopying', 0),
(17, 'Environmental: Provide a visual summary of the daily schedule', 0),
(18, 'Instructional: Vary amount of material to be learned', 0),
(19, 'Instructional: Vary amount of material to be practised', 0),
(20, 'Instructional: Vary time for practice activities', 0),
(21, 'Instructional: Use advance organizers and graphic organizers', 0),
(22, 'Instructional: Provide an outline or study guide', 0),
(23, 'Instructional: Use assignment notebooks or homework checklists', 0),
(24, 'Instructional: Repeat directions or have student repeat directions', 0),
(25, 'Instructional: Shorten directions', 0),
(26, 'Instructional: Highlight instructions', 0),
(27, 'Instructional: Pair written instructions with oral instructions', 0),
(28, 'Instructional: Reduce number of tasks required in assignments', 0),
(29, 'Instructional: Break long-term assignments into shorter tasks', 0),
(30, 'Instructional: Use strategies to enhance recall, e.g., cues & cloze', 0),
(31, 'Instructional: Accept dictated or parent-assisted homework assignments', 0),
(32, 'Instructional: Provide extra assignment time', 0),
(33, 'Instructional: Provide models of written work or other assignments to guide students', 0),
(34, 'Instructional: Permit student to print', 0),
(35, 'Instructional: Provide student a buddy for reading', 0),
(36, 'Instructional: Provide access to peer or cross-aged tutoring', 0),
(37, 'Instructional: Provide time with a teacher assistant', 0),
(38, 'Instructional: Provide nonverbal reminders for student to stay on task', 0),
(39, 'Instructional: Provide immediate positive reinforcement for behaviour', 0),
(40, 'Instructional: Implement self-monitoring systems so student takes responsibility for own behaviour', 0),
(41, 'Assessment: Adjust the test appearance, e.g., margins & spacing', 0),
(42, 'Assessment:  Adjust the test design (T/F, multiple choice, matching)', 0),
(43, 'Assessment: Adjust to recall with cues, cloze, word lists', 0),
(44, 'Assessment: Vary test administration, e.g., small groups, individual', 0),
(45, 'Assessment: Record test questions', 0),
(46, 'Assessment: Reduce number of test items or select items specific to ability level', 0),
(47, 'Assessment: Give extra test time', 0),
(48, 'Assessment: Permit breaks during tests', 0),
(49, 'Assessment:  Adjust readability of test', 0),
(50, 'Assessment: Allow alternative formats such as webs or key points in place of essays or long answers', 0),
(51, 'Assessment: Read test questions', 0),
(52, 'Assessment:  Allow use of a scribe or reader', 0),
(53, 'Assessment: Allow oral exams', 0),
(54, 'Assessment: Practise taking similar test questions', 0),
(55, 'Reader', 1),
(56, 'Scribe', 2),
(57, 'Additional Writing Time', 3);


-- --------------------------------------------------------

-- 
-- Table structure for table `typical_long_term_goal`
-- 

CREATE TABLE IF NOT EXISTS `typical_long_term_goal` (
  `ltg_id` bigint(20) NOT NULL auto_increment,
  `goal` text NOT NULL,
  `cid` bigint(20) NOT NULL default '0',
  `is_deleted` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`ltg_id`),
  KEY `area_type_id` (`cid`)
) ENGINE=MyISAM  AUTO_INCREMENT=26 ;

-- 
-- Dumping data for table `typical_long_term_goal`
-- 

INSERT INTO `typical_long_term_goal` (`ltg_id`, `goal`, `cid`, `is_deleted`) VALUES 
(8, 'Behaviour: To exhibit self-control and co-operation in classroom and school activities', 2, 'Y'),
(10, 'Articulation: To improve the production of speech sounds.', 4, 'N'),
(11, 'Expressive Language: To increase proficiency in expressive language.', 4, 'Y'),
(12, 'Expressive Languague/Non-Verbal: To increase proficiency in the use of an alternative communication system', 4, 'N'),
(14, 'Career: To identify and explore a variety of career opportunities', 3, 'N'),
(15, 'Some long-term goal', 7, 'Y'),
(16, 'Long term (test) goal', 7, 'Y'),
(17, 'Long term (test2) goal', 8, 'N'),
(18, 'Long term (test2) goal2', 8, 'N'),
(19, 'To exhibit self-control and co-operation in classroom and school activities', 9, 'Y'),
(20, 'To identify and explore a variety of career opportunities', 10, 'Y'),
(21, 'Articulation: To improve the production of speech sounds', 11, 'Y'),
(22, 'Expressive Language: To increase proficiency in expressive language.', 11, 'Y'),
(23, 'Expressive Languague/Non-Verbal: To increase proficiency in the use of an alternative communication system', 11, 'Y'),
(24, 'To improve math accuracy', 12, 'N'),
(25, 'Receptive Language: To increase proficiency in the use of an alternate communication system', 11, 'Y');


-- --------------------------------------------------------

-- 
-- Table structure for table `typical_long_term_goal_category`
-- 

CREATE TABLE IF NOT EXISTS `typical_long_term_goal_category` (
  `cid` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `is_deleted` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`cid`),
  KEY `name` (`name`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  AUTO_INCREMENT=49 ;

-- 
-- Dumping data for table `typical_long_term_goal_category`
-- 

INSERT INTO `typical_long_term_goal_category` (`cid`, `name`, `is_deleted`) VALUES 
(3, 'Career', 'Y'),
(2, 'Behaviour', 'Y'),
(4, 'Communication', 'Y'),
(7, 'Test', 'Y'),
(8, 'Test2', 'Y'),
(9, 'Behaviour', 'N'),
(10, 'Career', 'N'),
(11, 'Communication', 'Y'),
(12, 'math', 'Y'),
(13, 'Language Arts', 'Y'),
(14, 'Academic', 'Y'),
(15, 'Math', 'Y'),
(16, 'Deaf & Hearing Impaired', 'N'),
(17, 'Domestic and Family Life', 'N'),
(18, 'English as a Second Language', 'N'),
(19, 'Fine Motor', 'N'),
(20, 'Gifted and Talented', 'N'),
(21, 'Gross Motor', 'N'),
(22, 'Mathematics', 'N'),
(23, 'Personal and Social Development', 'N'),
(24, 'Reading', 'N'),
(25, 'Selft Esteem', 'Y'),
(26, 'Self Esteem', 'N'),
(27, 'Spelling', 'N'),
(28, 'Thinking Skills', 'N'),
(29, 'Work Habits', 'N'),
(30, 'Writing', 'N'),
(31, 'Vision', 'N');

-- --------------------------------------------------------

-- 
-- Table structure for table `typical_short_term_objective`
-- 

CREATE TABLE IF NOT EXISTS `typical_short_term_objective` (
  `stg_id` bigint(20) NOT NULL auto_increment,
  `goal` text NOT NULL,
  `ltg_id` bigint(20) NOT NULL default '0',
  `is_deleted` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`stg_id`),
  KEY `ltg_id` (`ltg_id`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  AUTO_INCREMENT=31 ;

-- 
-- Dumping data for table `typical_short_term_objective`
-- 
INSERT INTO `typical_short_term_objective` (`stg_id`, `goal`, `ltg_id`, `is_deleted`) VALUES 
(2, 'Interpersonal Conflict - To utilize appropriate strategies for interpersonal conflict resolution', 8, 'N'),
(3, 'Non-Compliance - To consistently comply with school requests and expectations', 8, 'N'),
(4, 'Anxieties - To overcome anxieties', 8, 'N'),
(5, 'Remaining in Designated Area - To remain in designated seat or area', 8, 'N'),
(6, 'Disruptive - To reduce disruptive behaviour', 8, 'N'),
(7, 'Inappropriate Behaviour - To control inappropriate behaviour', 8, 'N'),
(8, 'Production of sounds - To Demonstrate production of sounds in connected speech', 10, 'N'),
(9, 'Cry to get attention - To cry to get attention', 11, 'N'),
(10, 'Vocalize to express pleasure/displeasure - To vocalize to express pleasure or displeasure', 11, 'N'),
(11, 'Stop vocalizeing when another vocalizes - To stop vocalizing when another person vocalizes', 11, 'N'),
(12, 'Awareness of Interests/Abilities - To analyze personal interests, desires and abilities', 14, 'N'),
(13, 'Relationship of Education to Careers - To understand the relationship between education and specific careers', 14, 'N'),
(14, 'Some short term objective to achieve long term goal', 15, 'N'),
(15, 'Long term (test) sto1', 16, 'Y'),
(16, 'Long term (test) sto2', 16, 'Y'),
(17, 'Long term (test2) sto1', 17, 'Y'),
(18, 'Long term (test2) sto2', 17, 'Y'),
(19, 'Long term (test2) goal2 sto1', 18, 'N'),
(20, 'Production of Sounds- To demonstrate production of the following sounds in connected speech:', 21, 'Y'),
(21, 'Cry to get attention - To cry to get attention', 22, 'Y'),
(22, 'Vocalize to express pleasure/displeasure', 22, 'Y'),
(23, 'Stop vocalizing when another vocalizes', 22, 'Y'),
(24, 'Smile & reach for self in mirror', 22, 'Y'),
(25, 'Awareness of Speaker', 23, 'Y'),
(26, 'To discriminate between angry and friendly voices', 23, 'Y'),
(27, 'To utilize appropriate strategies for interpersonal conflict resolution', 19, 'Y'),
(28, 'To consistently comply with school requests and expectations', 19, 'Y'),
(29, 'To understand and accept the consequences of inappropriate behaviours', 19, 'Y'),
(30, 'addition acuracy 9 out of 10 attempts with 80% ', 24, 'N');


-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE IF NOT EXISTS `users` (
  `login_name` varchar(128) NOT NULL default '',
  `encrypted_password` varchar(128) NOT NULL default 'changeme',
  `unencrypted_password` varchar(128) NOT NULL default 'pass123',
  `first_name` varchar(128) NOT NULL default 'Unknown',
  `last_name` varchar(128) NOT NULL default 'Unknown',
  `school_code` smallint(6) default NULL,
  `aliased_name` varchar(255) default NULL,
  PRIMARY KEY  (`login_name`),
  UNIQUE KEY `login_name` (`login_name`),
  KEY `last_name` (`last_name`),
  KEY `first_name` (`first_name`),
  KEY `login_name_2` (`login_name`),
  KEY `school_code` (`school_code`),
  KEY `aliased_name` (`aliased_name`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `users`
-- 

INSERT INTO `users` (`login_name`, `encrypted_password`, `unencrypted_password`, `first_name`, `last_name`, `school_code`, `aliased_name`) VALUES 
('admin', '43e9a4ab75570f5b', 'admin', 'Administrator', 'Administrator', NULL, NULL);

-- --------------------------------------------------------

-- 
-- Table structure for table `valid_coding`
-- 

CREATE TABLE IF NOT EXISTS `valid_coding` (
  `uid` bigint(20) NOT NULL auto_increment,
  `code_number` int(11) NOT NULL default '0',
  `code_text` varchar(255) default NULL,
  PRIMARY KEY  (`uid`),
  UNIQUE KEY `code_number` (`code_number`),
  KEY `code_number_2` (`code_number`)
) ENGINE=MyISAM  AUTO_INCREMENT=22 ;

-- 
-- Dumping data for table `valid_coding`
-- 

INSERT INTO `valid_coding` (`uid`, `code_number`, `code_text`) VALUES 
(1, 30, 'Mild/Moderate Disabilities (ECS)'),
(2, 51, 'Mild Cognitive Disability, Grades 1-12'),
(3, 52, 'Moderate Cognitive Disability, Grades 1-12'),
(4, 53, 'Emotional/Behavioural disability, Grades 1-12'),
(5, 54, 'Learning Disability'),
(6, 55, 'Hearing Disability, Grades 1-12'),
(7, 56, 'Visual Disability, Grades 1-12'),
(8, 57, 'Communication Disability, Grades 1-12'),
(9, 58, 'Physical or Medical Disability, Grades 1-12'),
(10, 59, 'Multiple Disability, Grades 1-12'),
(11, 80, 'Gifted and Talented, ECS-Grade 12'),
(12, 41, 'Severe Cognitive Disability, ECS-Grade 12'),
(13, 42, 'Severe Emotional/Behavioural Disability, ECS-Grade 12'),
(14, 43, 'Severe Multiple Disabilitiy, ECS-Grade 12'),
(15, 44, 'Severe Physical or Medical Disability, ECS-Grade 12'),
(16, 45, 'Deafness, ECS-Grade 12'),
(17, 46, 'Blindness'),
(18, 47, 'Severe Delay Involving Language, ECS'),
(19, 301, 'English as a Second Language (Funded)'),
(20, 302, 'English as a Second Language (non funded)'),
(21, 303, 'Canadian-born English as a Second Language');
