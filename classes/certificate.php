<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * This is the class describing a certificate in Attestoodle
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package    tool_attestoodle
 */

namespace tool_attestoodle;


use tool_attestoodle\utils\db_accessor;
use tool_attestoodle\gabarit\attestation_pdf;
/**
 * compute data for publish certificate.
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class certificate {
    /** @var learner Learner for whom the certificate is */
    private $learner;
    /** @var training Learner for which the certificate is */
    private $training;
    /** @var \DateTime Begining date for the certificate infos */
    private $begindate;
    /** @var \DateTime End date for the certificate infos */
    private $enddate;

    /**
     * Constructor of the class
     *
     * @param learner $learner The learner of the certificate
     * @param training $training The training of the certificate
     * @param \DateTime $begindate The begin date of the certificate
     * @param \DateTime $enddate The end date of the certificate
     */
    public function __construct($learner, $training, $begindate, $enddate) {
        $this->learner = $learner;
        $this->training = $training;
        $this->begindate = $begindate;
        $this->enddate = $enddate;
    }

    /**
     * Methods that return the file info within moodledata, parsed in
     * the array that moodle needs to retrieve/create the actual file.
     *
     * @return array The array containing the infos well formed
     */
    public function get_file_infos() {
        $usercontext = \context_user::instance($this->learner->get_id());

        // Prepare file record object.
        $fileinfos = array(
                'contextid' => $usercontext->id,
                'component' => 'tool_attestoodle',
                'filearea' => 'certificates',
                'filepath' => '/',
                'itemid' => 0,
                'filename' => $this->get_file_name()
        );

        return $fileinfos;
    }

    /**
     * Methods that generate the actual file name within moodledata,
     * depending on the learner, training and period
     *
     * @return string The file name formated as
     * "certificate_[learner last name][learner first name]_[begin date]_[end date]_[training name].pdf"
     * where the begin and end dates format is YYYYMMDD
     */
    public function get_file_name() {
        $filename = "certificate_{$this->learner->get_lastname()}{$this->learner->get_firstname()}_";
        $filename .= "{$this->begindate->format("Ymd")}_{$this->enddate->format("Ymd")}_";
        $filename .= $this->training->get_name();
        $filename .= ".pdf";

        return $filename;
    }

    /**
     * Methods that parses all the variable informations needed in the actual
     * certificate file such as learner name, period, etc.
     *
     * @return \stdClass A standard class object containing the following infos:
     * $obj->learnername = The learner full name
     * $obj->trainingname = The training name
     * $obj->totalminutes = The total amount of validated milestones in minutes
     * $obj->period = The period (begin and end date) in a readable format
     * $obj->activities = An array of key => value where the keys are the courses id
     * where at least one milestone has been validated, and the value is another
     * key => value array. This second array contain two fixed infos: the course
     * name and the course total validated milestones (in minutes). This last
     * property may be a void array.
     */
    public function get_pdf_informations() {
        $trainingname = $this->training->get_name();
        $totalminutes = 0;

        $filteredmilestones = $this->get_filtered_milestones();

        // Retrieve activities informations in an array structure.
        $activitiesstructured = array();
        $index = 0;
        foreach ($filteredmilestones as $fva) {
            // Retrieve activity.
            $activity = $fva->get_activity();

            // Increment total minutes for the training.
            $totalminutes += $activity->get_milestone();

            // Retrieve current activity informations.
            $course = $activity->get_course();
            $courseid = $course->get_id();
            $coursename = $course->get_name();
            $cmid = $activity->get_id();
            $nomind = "act" . $index;
            $activitiesstructured[$nomind]["coursename"] = $coursename;
            $activitiesstructured[$nomind]["totalminutes"] = $activity->get_milestone();
            $activitiesstructured[$nomind]["moduleid"] = $activity->get_idmodule();
            $activitiesstructured[$nomind]["name"] = $activity->get_name();
            $activitiesstructured[$nomind]["description"] = $activity->get_description();
            $trad = get_string ('modulename', $activity->get_type());
            $activitiesstructured[$nomind]["type"] = $trad;
            $activitiesstructured[$nomind]["cmid"] = $cmid;
            $activitiesstructured[$nomind]["courseid"] = $courseid;
            $index++;
        }
        // Retrieve global informations.
        $datformat = get_string('dateformat', 'tool_attestoodle');
        $datebeg = $this->begindate->format($datformat);
        $dateend = $this->enddate->format($datformat);

        $period = get_string('fromdate', 'tool_attestoodle', $datebeg) . " " .
            get_string('todate', 'tool_attestoodle', $dateend);

        $certificateinfos = new \stdClass();
        $certificateinfos->learnername = $this->learner->get_fullname();
        $certificateinfos->learnerid = $this->learner->get_id();
        // Add cumul of validate time since begin !!
        $searchenddate = clone $this->enddate;
        $searchenddate->modify('+1 day');
        $certificateinfos->cumulminutes = $this->learner->get_total_milestones(
                                                            $this->training->get_categoryid(),
                                                            null,
                                                            $searchenddate);
        $certificateinfos->trainingname = $trainingname;
        $certificateinfos->totalminutes = $totalminutes;
        $certificateinfos->period = $period;
        $certificateinfos->activities = $activitiesstructured;
        return $certificateinfos;
    }

    /**
     * Method that returns the activities validated by the learner for the
     * training currently being computes, within the period and all
     *
     * @return activity[] The activities with milestones validated by the learner
     */
    public function get_filtered_milestones() {
        $begindate = clone $this->begindate;
        $searchenddate = clone $this->enddate;
        $searchenddate->modify('+1 day');
        $categoryid = $this->training->get_categoryid();

        $validatedmilestones = $this->learner->get_validated_activities_with_marker($begindate, $searchenddate);

        // Filtering activities based on the training.
        $filteredmilestones = array_filter($validatedmilestones, function($va) use($categoryid) {
            $act = $va->get_activity();
            if ($act->get_course()->get_training()->get_categoryid() == $categoryid) {
                return true;
            } else {
                return false;
            }
        });
        return $filteredmilestones;
    }

    /**
     * Methods that tries to retrieve the actual certificate file in moodledata
     * corresponding to the current certificate object.
     *
     * @return \stored_file|bool stored_file instance if exists, false if not
     */
    public function retrieve_file() {
        $fs = get_file_storage();
        $fileinfos = $this->get_file_infos();

        $file = $fs->get_file(
                $fileinfos['contextid'],
                $fileinfos['component'],
                $fileinfos['filearea'],
                $fileinfos['itemid'],
                $fileinfos['filepath'],
                $fileinfos['filename']
        );

        return $file;
    }

    /**
     * Methods that checks if the actual certificate file exists within moodledata
     *
     * @return boolean False if the file exists, true if not. Or maybe vice versa, I'm not sure...
     */
    public function file_exists() {
        return $this->retrieve_file() ? true : false;
    }

    /**
     * Methods that generate the actual file URL using moodle make_pluginfile_url helper
     *
     * @return string The actual file URL on the server
     */
    public function get_existing_file_url() {
        $file = $this->retrieve_file();

        $url = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename());

        return $url;
    }

    /**
     * Method that creates the certificate file on the server
     *
     * @return int The status of the file creation:
     *  0 = An error occured while attempting to create the file
     *  1 = The file has been created normally
     *  2 = A certificate with the same informations has been found
     * on the server: the old file has been replaced by the new one
     */
    public function create_file_on_server() {
        $status = 1;
        $fs = get_file_storage();

        try {
            $fileinfos = $this->get_file_infos();

            if ($this->file_exists()) {
                $oldfile = $this->retrieve_file();
                $oldfile->delete();
                $status = 2;
            }

            $template = db_accessor::get_instance()->get_user_template(
                $this->learner->get_id(), $this->training->get_id());

            $doc = new attestation_pdf();
            if (!isset($template->id)) {
                $doc->set_trainingid($this->training->get_id());
            } else {
                $doc->set_idtemplate($template->templateid);
                $doc->set_grpcriteria1($template->grpcriteria1);
                $doc->set_grpcriteria2($template->grpcriteria2);
            }
            $doc->set_infos($this->get_pdf_informations());
            $pdf = $doc->generate_pdf_object();

            $pdfstring = $pdf->Output('', 'S');

            $file = $fs->create_file_from_string($fileinfos, $pdfstring);

            if (!$file) {
                $status = 0;
            }
        } catch (\Exception $e) {
            $status = 0;
        }

        return $status;
    }

    /**
     * Returns the training of the certificate.
     *
     * @return training The training of the certificate.
     */
    public function get_training() {
        return $this->training;
    }

    /**
     * Returns the learner of the certificate.
     *
     * @return learner The learner of the certificate.
     */
    public function get_learner() {
        return $this->learner;
    }
}
