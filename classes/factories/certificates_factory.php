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
 * This is the class that implements the pattern Factory to create the
 * categories used by Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\factories;

use block_attestoodle\utils\singleton;
use block_attestoodle\utils\db_accessor;
use block_attestoodle\certificate;

defined('MOODLE_INTERNAL') || die;

class certificates_factory extends singleton {
    /** @var categories_factory Instance of the categories_factory singleton */
    protected static $instance;

    private $filestorage;

    /**
     * Constructor method
     */
    protected function __construct() {
        parent::__construct();
        $this->filestorage = \get_file_storage();
    }

    public function retrieve_certificate(learner $learner, training $training, \DateTime $begindate, \DateTime $enddate) {
        $usercontext = \context_user::instance($learner->get_id());

        $filename = "certificate_{$learner->get_firstname()}{$learner->get_lastname()}_";
        $filename .= $begindate->format("Ymd") . "_" . $enddate->format("Ymd");
        $filename .= ".pdf";

        $fileinfo = array(
            'contextid' => $usercontext->id,
            'component' => 'block_attestoodle',
            'filearea' => 'certificates',
            'filepath' => '/',
            'itemid' => 0,
            'filename' => $filename);

        return $this->filestorage->get_file(
                $fileinfo['contextid'],
                $fileinfo['component'],
                $fileinfo['filearea'],
                $fileinfo['itemid'],
                $fileinfo['filepath'],
                $fileinfo['filename']);
    }

    public function certificate_exists($learner, $training, $begindate, $enddate) {
        return $this->retrieve_certificate($learner, $training, $begindate, $enddate) ? true : false;
    }

    public function generate_certificate() {

    }

    private function delete_file() {

    }

    private function create_file(certificate $certificate) {
        $fileinfos = $certificate->get_file_infos();

        $file = $fs->create_file_from_string($fileinfo, $pdfstring);

        // Prepare file URL.
        $url = moodle_url::make_pluginfile_url(
                        $file->get_contextid(), $file->get_component(), $file->get_filearea(), null, $file->get_filepath(), $file->get_filename());
    }
}

