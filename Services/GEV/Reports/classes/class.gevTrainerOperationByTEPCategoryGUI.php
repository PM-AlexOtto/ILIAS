<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "Trainer operation by TEP Category"
* for Generali
* @author 	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
* Based on
* Report "AttendanceByEmployees"
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*	Define title, table_cols and row_template.
*	Implement fetchData to retrieve the data you want
*
*	Add special _process_xls_XXX and _process_table_XXX methods
*	to modify certain entries after retrieving data.
*	Those methods must return a proper string.
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

const MIN_ROW = "3991";

class gevTrainerOperationByTEPCategoryGUI extends catBasicReportGUI{

	protected $internal_sorting_fields = array("fullname");
	protected static $important_tep_categories	= array("Training");
	public function __construct() {
		global $ilDB;
		parent::__construct();
		$min_row_condition = "ht.row_id > ".MIN_ROW;



		$this->title = catTitleGUI::create()
						->title("gev_report_trainer_operation_by_tep_category_title")
						->subTitle("gev_report_trainer_operation_by_tep_category_desc")
						->image("GEV_img/ico-head-edubio.png");

		$this->table = catReportTable::create();
		$this->table->column("fullname", "name");
		$categories = self::getCategories();
		//self::createTemplateFile($categories);

		$i = 1;
		foreach($categories as $category) {
			$this->table->column("cat$i", $category, true);
			$this->table->column("cath$i", "Std.", true);
			$i++;
		}


		$this->table->template("tpl.gev_trainer_operation_by_template_category_row.html", 
								"Services/GEV/Reports");

		$this->order = catReportOrder::create($this->table)
						->defaultOrder("fullname", "ASC");
		
		$this->query = catReportQuery::create()
						->distinct()
						->select("hu.user_id")
						->select_raw("CONCAT(hu.lastname, ', ', hu.firstname) as fullname");
		$i = 1;
		foreach($categories as $category) {
			$this->query->select_raw($this->daysPerTEPCategory($category, "cat$i"));
			$this->query->select_raw($this->hoursPerTEPCategory($category, "cath$i"));
			$i++;
		}
		$this->query->from("hist_tep ht")
					->join("hist_user hu")
						->on("ht.user_id = hu.user_id")
					->join("hist_tep_individ_days htid")
						->on("individual_days = id")
					->left_join("hist_course hc")
						->on("context_id = crs_id AND ht.category  = ".$ilDB->quote("Training","text"))
					->group_by("fullname")
					->compile();

		$this->filter = catFilter::create()
						->multiselect( "edu_program"
									 , $this->lng->txt("gev_edu_program")
									 , "hc.edu_program"
									 , gevCourseUtils::getEduProgramsFromHisto()
									 , array()
									 )
						->multiselect( "template_title"
									 , $this->lng->txt("crs_title")
									 , "hc.template_title"
									 , gevCourseUtils::getTemplateTitleFromHisto()
									 , array()
									 )
						->multiselect( "type"
									 , $this->lng->txt("gev_course_type")
									 , "type"
									 , gevCourseUtils::getLearningTypesFromHisto()
									 , array()
									 )
						->dateperiod( "period"
									 , $this->lng->txt("gev_period")
									 , $this->lng->txt("gev_until")
									 , "ht.begin_date"
									 , "ht.end_date"
									 , date("Y")."-01-01"
									 , date("Y")."-12-31"
									 , false
									 , " OR ht.hist_historic IS NULL"
									 )
						->multiselect( "org_unit"
									 , $this->lng->txt("gev_report_filter_crs_region")
									 , "ht.orgu_title"
									 , $this->getOrgusFromTep()
									 , array()
									 )
						->multiselect( "venue"
									 , $this->lng->txt("gev_venue")
									 , "ht.location"
									 , gevOrgUnitUtils::getVenueNames()
									 , array()
									 )
						->static_condition("(hc.hist_historic = 0 OR hc.hist_historic IS NULL)")
						->static_condition("ht.hist_historic = 0")
						->static_condition($min_row_condition) 
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile();

	}

	protected function transformResultRow($rec) {

		//date
		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
			){
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;
		
		return $this->replaceEmpty($rec);
	}
	
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}

	protected static function getCategories() {
		global $ilDB;
		$sql = "SELECT title FROM tep_type";
		$rec = $ilDB->query($sql);
		$columns = array();
		while($res = $ilDB->fetchAssoc($rec)) {
			$columns[] = $res["title"];
		}
		foreach(array_reverse(self::$important_tep_categories) as $category) {
			$key = array_search($category, $columns);
			unset($columns[$key]);
			array_unshift($columns,$category);
		}
		return $columns;
	}

	protected static function createTemplateFile(array $categories) {
		$i=1;
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_operation_by_template_category_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td>{VAL_FULLNAME}</td>';
		foreach($categories as $category) {
			$tpl .= "\n".'<td align = "right">{VAL_CAT'."$i".'}</td>';
			$tpl .= "\n".'<td align = "right">{VAL_CATH'."$i".'}</td>';
			$i++;
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);

	}

	protected function getOrgusFromTep() {
		global $ilDB;
		$orgu_s = array();
		$sql = "SELECT DISTINCT orgu_title as ot FROM hist_tep WHERE orgu_title != '-empty-'";
		$res = $ilDB->query($sql);
		while( $rec = $ilDB->fetchAssoc($res)) {
			$orgu_s[] = $rec["ot"];
		}
		return $orgu_s;
	}

	protected function daysPerTEPCategory($category,$name) {
		global $ilDB;
		$sql = "SUM(IF(category = ".$ilDB->quote($category,"text").",1,0)) as ".$name;
		return $sql;
	}

	protected function hoursPerTEPCategory($category, $name) {
		global $ilDB;
		$sql = 
		"SUM(IF(category = ".$ilDB->quote($category,"text").",
			CEIL( TIME_TO_SEC( TIMEDIFF( end_time, start_time ) )* weight /720000) *2,0)) as ".$name;
		return $sql;
	}
}

?>
