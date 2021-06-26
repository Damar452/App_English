<?php
// This file is part of the Navigation buttons plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

class block_navbuttons extends block_base {
    public function init() {
        $this->title = get_string('navbuttons', 'block_navbuttons');
    }

    public function applicable_formats() {
        return array('course' => true, 'course-category' => false, 'site' => true);
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $CFG;

        if (!has_capability('moodle/course:manageactivities', $this->context)) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';

        if ($CFG->version < 2012120300) {
            $courseid = get_courseid_from_context($this->context);
        } else {
            if ($coursecontext = $this->context->get_course_context(false)) {
                $courseid = $coursecontext->instanceid;
            }
        }
        if (empty($courseid)) {
            return null;
        }

        $editlink = new moodle_url('/blocks/navbuttons/edit.php', array('course' => $courseid));
        $this->content->text = '<a href="'.$editlink.'">'.get_string('editsettings', 'block_navbuttons').'</a>';

        return $this->content;
    }

    public function instance_create() {
        global $DB, $CFG;

        if ($CFG->version < 2012120300) {
            $courseid = get_courseid_from_context($this->context);
        } else {
            if ($coursecontext = $this->context->get_course_context(false)) {
                $courseid = $coursecontext->instanceid;
            }
        }
        if (empty($courseid)) {
            return;
        }

        // Enable the buttons when the block is added to a course.
        if (!$settings = $DB->get_record('navbuttons', array('course' => $courseid))) {
            $settings = new stdClass;
            $settings->course = $courseid;
            $settings->enabled = 1;
            // All other records as database defaults.
            $DB->insert_record('navbuttons', $settings);
        } else {
            if (!$settings->enabled) {
                $updsettings = new stdClass;
                $updsettings->id = $settings->id;
                $updsettings->enabled = 1;
                $DB->update_record('navbuttons', $updsettings);
            }
        }

        return;
    }

    public function instance_delete() {
        global $DB, $CFG;

        if ($CFG->version < 2012120300) {
            $courseid = get_courseid_from_context($this->context);
        } else {
            if ($coursecontext = $this->context->get_course_context(false)) {
                $courseid = $coursecontext->instanceid;
            }
        }
        if (empty($courseid)) {
            return;
        }

        // Disable the buttons when the block is removed from a course (but leave the record, in case it is enabled later).
        $settings = $DB->get_record('navbuttons', array('course' => $courseid));
        if ($settings) {
            if ($settings->enabled) {
                $updsettings = new stdClass;
                $updsettings->id = $settings->id;
                $updsettings->enabled = 0;
                $DB->update_record('navbuttons', $updsettings);
            }
        }
    }
}
