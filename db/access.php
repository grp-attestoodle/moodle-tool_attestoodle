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
 * Attestoodle block caps.
 *
 * @package    block_attestoodle
 * @copyright  Guillaume GIRARD <dev.guillaume.girard@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        /*'block/attestoodle:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,

            'captype' => 'write',
            'contextlevel' => CONTEXT_SYSTEM,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        )*/
        'block/attestoodle:managetrainings' => array(
                'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,

                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:displaytrainings' => array(
                'riskbitmask' => RISK_PERSONAL,

                'captype' => 'read',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:trainingdetails' => array(
                'riskbitmask' => RISK_PERSONAL,

                'captype' => 'read',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:managetraining' => array(
                'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,

                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:displaylearnerslist' => array(
                'riskbitmask' => RISK_PERSONAL,

                'captype' => 'read',
                'contextlevel' => CONTEXT_COURSECAT,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:learnerdetails' => array(
                'riskbitmask' => RISK_PERSONAL,

                'captype' => 'read',
                'contextlevel' => CONTEXT_COURSECAT,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
        'block/attestoodle:downloadcertificate' => array(
                'riskbitmask' => RISK_CONFIG | RISK_DATALOSS | RISK_PERSONAL,

                'captype' => 'read write',
                'contextlevel' => CONTEXT_COURSECAT,
                'archetypes' => array(
                        'manager' => CAP_ALLOW
                )
        ),
);
