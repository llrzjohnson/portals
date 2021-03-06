<?php
class WpProQuiz_Helper_DbUpgrade {
	
	const WPPROQUIZ_DB_VERSION = 18;
	
	private $_wpdb;
	private $_prefix;

	public function __construct() {
		global $wpdb;
		
		$this->_wpdb = $wpdb;
	}
	
	public function upgrade($version) {
		
		if($version === false || ((int)$version) > WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION) {
			$this->install();
			return WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION;
		}
		
		$version = (int) $version;
		
		if($version === WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION)
			return WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION;
				
		do {
			$f = 'upgradeDbV'.$version;
			
			if(method_exists($this, $f)) {
				$version = $this->$f();
			} else {
				die("WpProQuiz upgrade error");
			}
		} while ($version < WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION);
		
		return WpProQuiz_Helper_DbUpgrade::WPPROQUIZ_DB_VERSION;
	}
	
	public function delete() {
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_master`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_question`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_lock`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_toplist`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_prerequisite`');
		$this->_wpdb->query('DROP TABLE IF EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_category`');
	}
	
	private function install() {
		
		$this->delete();
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_master` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(200) NOT NULL,
			  `text` text NOT NULL,
			  `result_text` text NOT NULL,
			  `result_grade_enabled` tinyint(1) NOT NULL,
			  `title_hidden` tinyint(1) NOT NULL,
			  `btn_restart_quiz_hidden` tinyint(1) NOT NULL,
			  `btn_view_question_hidden` tinyint(1) NOT NULL,
			  `question_random` tinyint(1) NOT NULL,
			  `answer_random` tinyint(1) NOT NULL,
			  `check_answer` tinyint(1) NOT NULL,
			  `back_button` tinyint(1) NOT NULL,
			  `time_limit` int(11) NOT NULL,
			  `statistics_on` tinyint(1) NOT NULL,
			  `statistics_ip_lock` int(10) unsigned NOT NULL,
			  `show_points` tinyint(1) NOT NULL,
			  `quiz_run_once` tinyint(1) NOT NULL,
			  `quiz_run_once_type` tinyint(4) NOT NULL,
			  `quiz_run_once_cookie` tinyint(1) NOT NULL,
			  `quiz_run_once_time` int(10) unsigned NOT NULL,
			  `question_on_single_page` tinyint(1) NOT NULL,
			  `numbered_answer` tinyint(1) NOT NULL,
			  `hide_answer_message_box` tinyint(1) NOT NULL,
			  `disabled_answer_mark` tinyint(1) NOT NULL,
			  `show_max_question` tinyint(1) NOT NULL,
			  `show_max_question_value` int(10) unsigned NOT NULL,
			  `show_max_question_percent` tinyint(1) NOT NULL,
			  `toplist_activated` tinyint(1) NOT NULL,
			  `toplist_data` text NOT NULL,
			  `show_average_result` tinyint(1) NOT NULL,
			  `prerequisite` tinyint(1) NOT NULL,
			  `quiz_modus` tinyint(3) unsigned NOT NULL,
			  `show_review_question` tinyint(1) NOT NULL,
			  `quiz_summary_hide` tinyint(1) NOT NULL,
			  `skip_question_disabled` tinyint(1) NOT NULL,
			  `email_notification` tinyint(3) unsigned NOT NULL,
			  PRIMARY KEY (`id`)
			)   DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_question` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `quiz_id` int(11) NOT NULL,
			  `sort` smallint(5) unsigned NOT NULL,
			  `title` varchar(200) NOT NULL,
			  `points` int(11) NOT NULL,
			  `question` text NOT NULL,
			  `correct_msg` text NOT NULL,
			  `incorrect_msg` text NOT NULL,
			  `correct_same_text` tinyint(1) NOT NULL,
			  `tip_enabled` tinyint(1) NOT NULL,
			  `tip_msg` text NOT NULL,
			  `answer_type` varchar(50) NOT NULL,
			  `show_points_in_box` tinyint(1) NOT NULL,
			  `answer_points_activated` tinyint(1) NOT NULL,
			  `answer_data` longtext NOT NULL,
			  `category_id` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `quiz_id` (`quiz_id`),
			  KEY `category_id` (`category_id`)
			)   DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` (
			  `quiz_id` int(11) NOT NULL,
			  `lock_ip` varchar(100) NOT NULL,
			  `user_id` bigint(20) unsigned NOT NULL,
			  `lock_type` tinyint(3) unsigned NOT NULL,
			  `lock_date` int(11) NOT NULL,
			  PRIMARY KEY (`quiz_id`,`lock_ip`,`user_id`,`lock_type`)
			)  DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic` (
			  `quiz_id` int(11) NOT NULL,
			  `question_id` int(11) NOT NULL,
			  `user_id` bigint(20) unsigned NOT NULL,
			  `correct_count` int(10) unsigned NOT NULL,
			  `incorrect_count` int(10) unsigned NOT NULL,
			  `hint_count` int(10) unsigned NOT NULL,
			  `points` int(10) unsigned NOT NULL,
			  PRIMARY KEY (`quiz_id`,`question_id`,`user_id`)
			)  DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_prerequisite` (
			  `prerequisite_quiz_id` int(11) NOT NULL,
			  `quiz_id` int(11) NOT NULL,
			  PRIMARY KEY (`prerequisite_quiz_id`,`quiz_id`)
			)  DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_toplist` (
			  `toplist_id` int(11) NOT NULL AUTO_INCREMENT,
			  `quiz_id` int(11) NOT NULL,
			  `date` int(10) unsigned NOT NULL,
			  `user_id` bigint(20) unsigned NOT NULL,
			  `name` varchar(30) NOT NULL,
			  `email` varchar(200) NOT NULL,
			  `points` int(10) unsigned NOT NULL,
			  `result` float unsigned NOT NULL,
			  `ip` varchar(100) NOT NULL,
			  PRIMARY KEY (`toplist_id`,`quiz_id`)
			)   DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_category` (
			  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `category_name` varchar(200) NOT NULL,
			  PRIMARY KEY (`category_id`)
			)   DEFAULT CHARSET=utf8;
		');
	}
	
	private function upgradeDbV1() {
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
				ADD  `back_button` TINYINT( 1 ) NOT NULL AFTER  `answer_random`,
				ADD  `check_answer` TINYINT( 1 ) NOT NULL AFTER  `answer_random`,
				ADD  `result_text` TEXT NOT NULL AFTER  `text`
		');
		
		return 2;
	}
	
	private function upgradeDbV2() {
		return 3;
	}
	
	private function upgradeDbV3() {
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_question`
				ADD  `incorrect_count` INT UNSIGNED NOT NULL AFTER  `incorrect_msg` ,
				ADD  `correct_count` INT UNSIGNED NOT NULL AFTER  `incorrect_msg` ,
				ADD  `correct_same_text` TINYINT( 1 ) NOT NULL AFTER  `incorrect_msg`
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
 				ADD  `statistics_on` TINYINT( 1 ) NOT NULL ,
 				ADD  `statistics_ip_lock` INT UNSIGNED NOT NULL
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` (
				`quiz_id` int(11) NOT NULL,
				`lock_ip` varchar(100) NOT NULL,
				`lock_date` int(11) NOT NULL,
				PRIMARY KEY (`quiz_id`,`lock_ip`)
			)  DEFAULT CHARSET=latin1;
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` 
				ADD INDEX (  `quiz_id` )
		');
		
		return 4;
	}
	
	private function upgradeDbV4() {
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` 
				ADD  `tip_enabled` TINYINT( 1 ) NOT NULL AFTER  `incorrect_count` ,
				ADD  `tip_msg` TEXT NOT NULL AFTER  `tip_enabled` ,
				ADD  `tip_count` INT NOT NULL AFTER  `tip_msg`
		');
				
		return 5;
	}
	
	private function upgradeFixDbV4() {
		if($this->_wpdb->prefix != 'wp_') {
			$this->_wpdb->query('SELECT * FROM `'.$this->_wpdb->prefix.'wp_pro_quiz_question` LIMIT 0,1');
		
			$names = $this->_wpdb->get_col_info('name');
		
			if(!in_array('tip_enabled', $names)) {
				$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` ADD `tip_enabled` TINYINT( 1 ) NOT NULL AFTER  `incorrect_count`');
			}
		
			if(!in_array('tip_msg', $names)) {
				$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` ADD `tip_msg` TEXT NOT NULL AFTER  `tip_enabled`');
			}
		
			if(!in_array('tip_count', $names)) {
				$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` ADD  `tip_count` INT NOT NULL AFTER `tip_msg`');
			}
		}
	}
	
	private function upgradeDbV5() {
		
		$this->upgradeFixDbV4();
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD  `result_grade_enabled` TINYINT( 1 ) NOT NULL AFTER  `result_text`
		');
		
		return 6;
	}
	
	private function upgradeDbV6() {
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question`
				ADD  `points` INT NOT NULL AFTER  `title`
		');
		
		$this->_wpdb->query('
			UPDATE `'.$this->_wpdb->prefix.'wp_pro_quiz_question` SET `points` = 1
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD  `show_points` TINYINT( 1 ) NOT NULL
		');
		
		return 7;
	}
	
	private function upgradeDbV7() {
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
				CHANGE  `name`  `name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `text`  `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `result_text`  `result_text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` 
				CHANGE  `title`  `title` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `question`  `question` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `correct_msg`  `correct_msg` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `incorrect_msg`  `incorrect_msg` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `tip_msg`  `tip_msg` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `answer_type`  `answer_type` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				CHANGE  `answer_json`  `answer_json` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` 
				CHANGE  `lock_ip`  `lock_ip` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
		');
			
		return 8;
	}
	
	private function upgradeDbV8() {
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
				ADD  `btn_restart_quiz_hidden` TINYINT( 1 ) NOT NULL AFTER  `title_hidden` ,
				ADD  `btn_view_question_hidden` TINYINT( 1 ) NOT NULL AFTER  `btn_restart_quiz_hidden`
		');
		
		return 9;
	}
	
	private function upgradeFixDbV8() {
		if($this->_wpdb->prefix != 'wp_') {
			$this->_wpdb->query('SELECT * FROM `'.$this->_wpdb->prefix.'wp_pro_quiz_master` LIMIT 0,1');
		
			$names = $this->_wpdb->get_col_info('name');
		
			if(!in_array('btn_restart_quiz_hidden', $names)) {
				$this->_wpdb->query('
					ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
						ADD  `btn_restart_quiz_hidden` TINYINT( 1 ) NOT NULL AFTER  `title_hidden`
				');
			}
		
			if(!in_array('btn_view_question_hidden', $names)) {
				$this->_wpdb->query('
					ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
						ADD  `btn_view_question_hidden` TINYINT( 1 ) NOT NULL AFTER  `btn_restart_quiz_hidden` 
				');
			}
		}
	}
	
	private function upgradeDbV9() {
		
		$this->upgradeFixDbV8();
		
		$this->_wpdb->query('
			TRUNCATE `'.$this->_wpdb->prefix.'wp_pro_quiz_lock`
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` 
				ADD  `user_id` BIGINT UNSIGNED NOT NULL AFTER  `lock_ip` ,
				ADD  `lock_type` TINYINT UNSIGNED NOT NULL AFTER  `user_id`
		');
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_lock` 
				DROP PRIMARY KEY ,
				ADD PRIMARY KEY (  `quiz_id` ,  `lock_ip` ,  `user_id` ,  `lock_type` )
		');
				
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` 
				ADD  `quiz_run_once` TINYINT( 1 ) NOT NULL ,
				ADD  `quiz_run_once_type` TINYINT NOT NULL ,
				ADD  `quiz_run_once_cookie` TINYINT( 1 ) NOT NULL ,
				ADD  `quiz_run_once_time` INT UNSIGNED NOT NULL 
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic` (
				  `quiz_id` int(11) NOT NULL,
				  `question_id` int(11) NOT NULL,
				  `user_id` bigint(20) unsigned NOT NULL,
				  `correct_count` int(10) unsigned NOT NULL,
				  `incorrect_count` int(10) unsigned NOT NULL,
				  `hint_count` int(10) unsigned NOT NULL,
				  PRIMARY KEY (`quiz_id`,`question_id`,`user_id`)
			)  DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			INSERT INTO `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic` (quiz_id, question_id, user_id, correct_count, incorrect_count, hint_count)
				SELECT
					question.quiz_id, id, 0, question.correct_count, question.incorrect_count, tip_count
				FROM 
					`'.$this->_wpdb->prefix.'wp_pro_quiz_question` as question
				WHERE
					question.correct_count > 0 OR question.incorrect_count > 0 OR tip_count > 0
		');
				
		return 10;
	}
	
	private function upgradeDbV10() {
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD  `question_on_single_page` TINYINT( 1 ) NOT NULL ,
				ADD  `numbered_answer` TINYINT( 1 ) NOT NULL 
		');
				
		return 11;
	}
	
	private function upgradeDbV11() {
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_question` 
				ADD  `points_per_answer` TINYINT( 1 ) NOT NULL ,
				ADD  `points_answer` INT UNSIGNED NOT NULL , 
				ADD  `show_points_in_box` TINYINT( 1 ) NOT NULL 
		');
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic`
				ADD  `correct_answer_count` INT UNSIGNED NOT NULL
		');
		
		$this->_wpdb->query('UPDATE `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic` SET `correct_answer_count` = `correct_count`');
		
		$this->_wpdb->query('UPDATE `'.$this->_wpdb->prefix.'wp_pro_quiz_question` SET `points_answer` = `points`');
		
		return 12;
	}
	
	private function upgradeDbV12() {
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD  `hide_answer_message_box` TINYINT( 1 ) NOT NULL ,
				ADD  `disabled_answer_mark` TINYINT( 1 ) NOT NULL ,
				ADD  `show_max_question` TINYINT( 1 ) NOT NULL ,
				ADD  `show_max_question_value` INT UNSIGNED NOT NULL ,
				ADD  `show_max_question_percent` TINYINT( 1 ) NOT NULL
		');
		
		return 13;
	}
	
	private function upgradeDbV13() {
	
		//WordPress SVN Bug
				
		$this->_wpdb->query('SELECT * FROM `'.$this->_wpdb->prefix.'wp_pro_quiz_master` LIMIT 0,1');
	
		$names = $this->_wpdb->get_col_info('name');
	
		if(!in_array('hide_answer_message_box', $names)) {
			$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` ADD  `hide_answer_message_box` TINYINT( 1 ) NOT NULL');
		}
	
		if(!in_array('disabled_answer_mark', $names)) {
			$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` ADD  `disabled_answer_mark` TINYINT( 1 ) NOT NULL');
		}
	
		if(!in_array('show_max_question', $names)) {
			$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` ADD  `show_max_question` TINYINT( 1 ) NOT NULL');
		}
		
		if(!in_array('show_max_question_value', $names)) {
			$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` ADD  `show_max_question_value` INT UNSIGNED NOT NULL');
		}
		
		if(!in_array('show_max_question_percent', $names)) {
			$this->_wpdb->query('ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master` ADD  `show_max_question_percent` TINYINT( 1 ) NOT NULL');
		}
	
		return 14;
	}
	
	private function upgradeDbV14() {
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_question`
				CHANGE  `sort`  `sort` SMALLINT UNSIGNED NOT NULL 
		');
		
		return 15;
	}
	
	private function upgradeDbV15() {
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_question` 
				ADD	`answer_points_activated` tinyint(1) NOT NULL, 
 				ADD	`answer_data` longtext NOT NULL
		');
		
		$this->_wpdb->query('
			ALTER TABLE `'.$this->_wpdb->prefix.'wp_pro_quiz_statistic`
				ADD `points` int(10) unsigned NOT NULL 
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD `toplist_activated` tinyint(1) NOT NULL, 
  				ADD `toplist_data` text NOT NULL, 
  				ADD `show_average_result` tinyint(1) NOT NULL,  
  				ADD `prerequisite` tinyint(1) NOT NULL 
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_prerequisite` (
			 	`prerequisite_quiz_id` int(11) NOT NULL, 
			 	`quiz_id` int(11) NOT NULL, 
			  	PRIMARY KEY (`prerequisite_quiz_id`,`quiz_id`) 
			)  DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_toplist` (
				  `toplist_id` int(11) NOT NULL AUTO_INCREMENT,
				  `quiz_id` int(11) NOT NULL,
				  `date` int(10) unsigned NOT NULL,
				  `user_id` bigint(20) unsigned NOT NULL,
				  `name` varchar(30) NOT NULL,
				  `email` varchar(200) NOT NULL,
				  `points` int(10) unsigned NOT NULL,
				  `result` float unsigned NOT NULL,
				  `ip` varchar(100) NOT NULL,
				  PRIMARY KEY (`toplist_id`,`quiz_id`)
			)   DEFAULT CHARSET=utf8;
		');
		
		
		$results = $this->_wpdb->get_results('SELECT id, answer_type, answer_json, points_per_answer, points_answer  FROM `'.$this->_wpdb->prefix.'wp_pro_quiz_question`', ARRAY_A);
		
		foreach($results as $row) {
			
			$data = json_decode($row['answer_json'], true);
			$newData = array();
			
			if($data === null) {
				continue;
			}
			
			if($row['answer_type'] == 'single' || $row['answer_type'] == 'multiple') {
				foreach($data['classic_answer']['answer'] as $k => $v) {
					$x = new WpProQuiz_Model_AnswerTypes();

					$x->setAnswer($v);
					
					if(isset($data['classic_answer']['correct']) && in_array($k, $data['classic_answer']['correct'])) {
						$x->setCorrect(true);
						
						if($row['points_per_answer']) {
							$x->setPoints($row['points_answer']);
						}
						
					} else {
						$x->setCorrect(false);
						
						if($row['points_per_answer']) {
							$x->setPoints(0);
						}
					}
					
					if(isset($data['classic_answer']['html']) && in_array($k, $data['classic_answer']['html'])) {
						$x->setHtml(true);
					} else {
						$x->setHtml(false);
					}
					
					$newData[] = $x;
					
				}
			} elseif($row['answer_type'] == 'cloze_answer') {
				$x = new WpProQuiz_Model_AnswerTypes();
				
				$x->setAnswer($data['answer_cloze']['text']);
				
				$newData[] = $x;
			} elseif($row['answer_type'] == 'matrix_sort_answer') {
				foreach($data['answer_matrix_sort']['answer'] as $k => $v) {
					$x = new WpProQuiz_Model_AnswerTypes();
					
					$x->setAnswer($v);
					$x->setSortString($data['answer_matrix_sort']['sort_string'][$k]);
					
					if($row['points_per_answer']) {
						$x->setPoints($row['points_answer']);
					}
					
					if(isset($data['answer_matrix_sort']['answer_html']) && in_array($k, $data['answer_matrix_sort']['answer_html'])) {
						$x->setHtml(true);
					} else {
						$x->setHtml(false);
					}
					
					if(isset($data['answer_matrix_sort']['sort_string_html']) && in_array($k, $data['answer_matrix_sort']['sort_string_html'])) {
						$x->setSortStringHtml(true);
					} else {
						$x->setSortStringHtml(false);
					}
					
					$newData[] = $x;
					
				}
			} elseif($row['answer_type'] == 'free_answer') {
				$x = new WpProQuiz_Model_AnswerTypes();
				
				$x->setAnswer($data['free_answer']['correct']);
				
				$newData[] = $x;
			} elseif($row['answer_type'] == 'sort_answer') {
				foreach($data['answer_sort']['answer'] as $k => $v) {
					$x = new WpProQuiz_Model_AnswerTypes();
						
					$x->setAnswer($v);
					
					if($row['points_per_answer']) {
						$x->setPoints($row['points_answer']);
					}
					
					if(isset($data['answer_sort']['html']) && in_array($k, $data['answer_sort']['html'])) {
						$x->setHtml(true);
					} else {
						$x->setHtml(false);
					}
					
					$newData[] = $x;
				}
			}
			
			
			$this->_wpdb->update(
				$this->_wpdb->prefix.'wp_pro_quiz_question', 
				array(
					'answer_data' => serialize($newData)
				), 
				array(
					'id' => $row['id']
				)
			);
			
		}
		
		$this->_wpdb->query(
			'UPDATE '.$this->_wpdb->prefix.'wp_pro_quiz_question
			SET
				answer_points_activated = points_per_answer
			WHERE
				answer_type <> \'free_answer\''
		);
		
		
		//Statistics
		$this->_wpdb->query(
			'UPDATE 
				'.$this->_wpdb->prefix.'wp_pro_quiz_statistic AS s
			SET
				s.points = ( SELECT q.points_answer FROM '.$this->_wpdb->prefix.'wp_pro_quiz_question AS q WHERE q.id = s.question_id ) * s.correct_answer_count
			WHERE
				s.correct_answer_count > 0'
		);
		
		
		return 16;
	}
	
	private function upgradeDbV16() {
		$this->_wpdb->query('
			ALTER TABLE '.$this->_wpdb->prefix.'wp_pro_quiz_question
				DROP `correct_count`,
				DROP `incorrect_count`,
				DROP `tip_count`,
				DROP `answer_json`,
				DROP `points_per_answer`,
				DROP `points_answer`;
		');
		
		$this->_wpdb->query('
			ALTER TABLE '.$this->_wpdb->prefix.'wp_pro_quiz_statistic
				DROP `correct_answer_count`;
		');

		return 17;
	}
	
	private function upgradeDbV17() {
		
		$this->_wpdb->query('
			CREATE TABLE IF NOT EXISTS `'.$this->_wpdb->prefix.'wp_pro_quiz_category` (
			  `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `category_name` varchar(200) NOT NULL,
			  PRIMARY KEY (`category_id`)
			)   DEFAULT CHARSET=utf8;
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				ADD  `quiz_modus` TINYINT UNSIGNED NOT NULL ,
				ADD  `show_review_question` TINYINT( 1 ) NOT NULL ,
				ADD  `quiz_summary_hide` TINYINT( 1 ) NOT NULL ,
				ADD  `skip_question_disabled` TINYINT( 1 ) NOT NULL ,
				ADD  `email_notification` TINYINT UNSIGNED NOT NULL 
		');
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_question`
				ADD  `category_id` INT UNSIGNED NOT NULL ,
				ADD INDEX (  `category_id` )
		');
		
		$this->_wpdb->update($this->_wpdb->prefix.'wp_pro_quiz_master', 
				array(
					'quiz_modus' => WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE,
					'back_button' => 0,
					'check_answer' => 0), 
				array('question_on_single_page' => 1));
		
		$this->_wpdb->update($this->_wpdb->prefix.'wp_pro_quiz_master', 
				array(
					'quiz_modus' => WpProQuiz_Model_Quiz::QUIZ_MODUS_CHECK,
					'back_button' => 0),
				array('check_answer' => 1));
		
		$this->_wpdb->update($this->_wpdb->prefix.'wp_pro_quiz_master', 
				array('quiz_modus' => WpProQuiz_Model_Quiz::QUIZ_MODUS_BACK_BUTTON), 
				array('back_button' => 1));
		
		$this->_wpdb->query('
			ALTER TABLE  `'.$this->_wpdb->prefix.'wp_pro_quiz_master`
				 DROP `check_answer`, 
				 DROP `back_button`, 
				 DROP `question_on_single_page` 
		');
		
		return 18;
	}
}