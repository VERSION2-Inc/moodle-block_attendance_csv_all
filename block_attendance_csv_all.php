<?php

class block_attendance_csv_all extends block_base {
      function init() {
          $this->title = get_string('blockname', 'block_attendance_csv_all');
          $this->version = 2012041700;
          $this->release = '1.0';
      }

      function get_content() {
        global $CFG, $USER;

        $course = $this->page->course;

        require_once($CFG->dirroot.'/course/lib.php');

        $context   = context_course::instance($course->id);
        $isediting = has_capability('moodle/course:manageactivities', $context);

        if (!$isediting) {
            // these users can not change any settings
            $this->content = '';
            return '';
        }

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;

        $currenttime = time() - 3600*24*31;

        $dayselector = html_writer::select_time('days', 'startday', $currenttime);
        $monthselector = html_writer::select_time('months', 'startmonth', $currenttime);
        $yearselector = html_writer::select_time('years', 'startyear', $currenttime);

        $startdatetimeoutput = $dayselector . $monthselector . $yearselector;


        $currenttime = time() + 3600*24*31;

        $dayselector = html_writer::select_time('days', 'endday', $currenttime);
        $monthselector = html_writer::select_time('months', 'endmonth', $currenttime);
        $yearselector = html_writer::select_time('years', 'endyear', $currenttime);

        $enddatetimeoutput = $dayselector . $monthselector . $yearselector;


        $save   = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'startcsv', 'value'=>get_string('startcsv', 'block_attendance_csv_all')));

        $output = '';

        $output .= html_writer::start_tag('form', array('method'=>'post', 'action'=>$CFG->wwwroot.'/blocks/attendance_csv_all/get_csv.php'));
        $output .= html_writer::tag('div', get_string('startdate', 'block_attendance_csv_all'));
        $output .= html_writer::tag('div', $startdatetimeoutput);
        $output .= html_writer::tag('div', get_string('enddate', 'block_attendance_csv_all'));
        $output .= html_writer::tag('div', $enddatetimeoutput);
        $output .= html_writer::tag('fieldset', $save, array('class'=>'buttonsbar'));
        $output .= html_writer::end_tag('form');


        $this->content->text  = $output;

        return $this->content;
    }


    public function instance_can_be_docked() {
        return true;
    }
}


