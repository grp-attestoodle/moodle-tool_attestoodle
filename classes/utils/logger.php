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
 * This file manage the logs into the Attestoodle tables
 *
 * @package    tool_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_attestoodle\utils;


/**
 * This is the singleton class that manage the logs into the Attestoodle tables
 *
 * @copyright  2018 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logger extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /**
     * Method that inserts a launch record into DB and return the newly created
     * id of the launch
     *
     * @param string $begindate The begin date of the generation launch
     * @param string $enddate The end date of the generation launch
     * @return integer|null The newly created launch ID or null if an error occured
     */
    public static function log_launch($begindate, $enddate) {
        global $USER;

        $launchdberror = false;
        try {
            $launchid = db_accessor::get_instance()->log_launch(
                    \time(),
                    $begindate,
                    $enddate,
                    $USER->id
            );
        } catch (\Exception $ex) {
            $launchdberror = true;
        }

        return !$launchdberror ? $launchid : null;
    }

    /**
     * Method that inserts a certificate record and values records into DB tables
     *
     * @param integer $launchid The launch ID into Attestoodle table corresponding to
     * the launch of the certificate currently being inserted
     * @param integer $status The status of the file creation on the server after
     * trying to save in into the moodle data repository
     * @param certificate $certificate The certificate to record info for
     * @return boolean True if insertion goes well or false if an error occured
     */
    public static function log_certificate($launchid, $status, $certificate) {
        $logcertiferror = false;
        $logvalueserror = false;

        try {
            $statusstring = null;
            switch ($status) {
                case 0:
                    $statusstring = 'ERROR';
                    break;
                case 1:
                    $statusstring = 'NEW';
                    break;
                case 2:
                    $statusstring = 'OVERWRITTEN';
                    break;
            }

            // Try to record the certificate log.
            $certificatelogid = db_accessor::get_instance()->log_certificate(
                    $certificate->get_file_name(),
                    $statusstring,
                    $certificate->get_training()->get_id(),
                    $certificate->get_learner()->get_id(),
                    $launchid);

            // Try to record the values used to generate the certificate.
            $milestones = $certificate->get_filtered_milestones();
            if (count($milestones) > 0) {
                try {
                    db_accessor::get_instance()->log_values($certificatelogid, $milestones);
                } catch (\Exception $ex) {
                    $logvalueserror = true;
                }
            }
        } catch (\Exception $ex) {
            $logcertiferror = true;
        }

        return !$logcertiferror && !$logvalueserror;
    }
}
