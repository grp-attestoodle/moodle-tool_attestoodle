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
 * Renderable page that computes infos to give to the template
 */

namespace block_attestoodle\output;

use renderable;
use templatable;
use stdClass;

class renderer_with_template_page implements renderable, templatable {
    /** @var string $sometext Some text to show how to pass data to a template. */
    var $sometext = null;
    var $training = null;

    public function __construct($sometext, $training) {
        $this->sometext = $sometext;
        $this->training = $training;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output) {
        $data = new stdClass();
        $data->sometext = $this->sometext;
        $data->trainingname = $this->training->get_name();
        return $data;
    }
}
