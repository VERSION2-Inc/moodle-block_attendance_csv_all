<?php

include_once "../../config.php";
require_once "$CFG->libdir/excellib.class.php";

$startday   = optional_param('startday', NULL, PARAM_INT);
$startmonth = optional_param('startmonth', NULL, PARAM_INT);
$startyear  = optional_param('startyear', NULL, PARAM_INT);
$endday     = optional_param('endday', NULL, PARAM_INT);
$endmonth   = optional_param('endmonth', NULL, PARAM_INT);
$endyear    = optional_param('endyear', NULL, PARAM_INT);


$timefrom   = mktime(0, 0, 0, $startmonth, $startday, $startyear);
$timeto     = mktime(0, 0, 0, $endmonth, $endday, $endyear);


$filename = "report.xls";
$workbook = new MoodleExcelWorkbook("-");
$workbook->send($filename);

$myxls =& $workbook->add_worksheet('Attendances');

$formatbc =& $workbook->add_format();
$formatbc->set_bold(1);


$allcourses = get_courses();

$lastr = 0;

foreach ($allcourses as $course) {
    $courseid = $course->id;
    $data = get_all_instances_in_course('attforblock', $course, NULL, true);
    
    $context = get_context_instance(CONTEXT_COURSE, $courseid);
    $query = 'SELECT u.id as id, firstname, lastname, email FROM {role_assignments} as a, {user} as u WHERE a.contextid=' . $context->id . ' AND a.roleid=5 AND a.userid=u.id ORDER BY u.firstname ASC'; //
    
    $students = $DB->get_records_sql($query); 

    $lastc = 0;

    if (count($data) > 0) {
      $myxls->write($lastr, $lastc, get_string('courseid', 'block_attendance_csv_all'), $formatbc); $lastc++;
      $myxls->write($lastr, $lastc, get_string('coursename', 'block_attendance_csv_all'), $formatbc); $lastc++;
      $myxls->write($lastr, $lastc, get_string('firstname', 'block_attendance_csv_all'), $formatbc); $lastc++;
      
      $statused = array();
      
      foreach ($data as $att) {
        if ($insts = $DB->get_records_sql("SELECT * FROM {attendance_sessions} WHERE attendanceid = ? AND sessdate >= ? AND sessdate < ? ORDER BY sessdate ASC", array($att->id, $timefrom, $timeto))) {
          $ci = 0;
          foreach($insts as $instance) {
            $ci++;
            if ($ci < 10) 
              $name = get_string('class', 'block_attendance_csv_all')."0".$ci;
            else
              $name = get_string('class', 'block_attendance_csv_all').$ci;
              
            $myxls->write($lastr, $lastc, $name, $formatbc); $lastc++;
            
            if (empty($statused[$instance->attendanceid])) {
              $s = $DB->get_record_sql("SELECT id FROM {attendance_statuses} WHERE attendanceid = ? AND description LIKE '%Absent%' LIMIT 1", array($instance->attendanceid));
              $statused[$instance->attendanceid] = $s->id;
            }
          }
          
          
          
          foreach($students as $student) {
            $lastr++;
            $lastc = 0;
            
            $myxls->write($lastr, $lastc, $course->idnumber); $lastc++;
            $myxls->write($lastr, $lastc, $course->shortname); $lastc++;
            $myxls->write($lastr, $lastc, $student->firstname); $lastc++;

            foreach($insts as $instance) {
              if ($att = $DB->get_record('attendance_log', array('sessionid' => $instance->id, 'studentid' => $student->id, 'statusid' => $statused[$instance->attendanceid]))) {
                $myxls->write($lastr, $lastc, strftime("%Y/%m/%d", $instance->sessdate)); $lastc++;
              }else {
                $myxls->write($lastr, $lastc, ""); $lastc++;
              }
            }
          }
          
          $lastr++;
        }
      }
      
      $lastr++;
    }
}

$workbook->close();
