# ATTESTOODLE


[![Build Status](https://api.travis-ci.org/grp-attestoodle/moodle-tool_attestoodle.svg?branch=master)](https://travis-ci.org/grp-attestoodle/moodle-tool_attestoodle)

This plugin is used to generate periodical training certificates for students on a massive scale, based on learning milestones completion.

An "Attestoodle training" models a training plan: a set of courses in the same Moodle category or sub-categories (an Attestoodle training is necessarily associated with a Moodle category).

An "Attestoodle milestone" is a Moodle activity (or sequence of activites) set with an activity completion. The state of activity completion is considered by Attestoodle as a milestone validation (completed or not). 

In the administration, training managers have to set a time for each milestone which represents an average completion time for that part of the course (sequence of activities) included in the milestone.  Once the milestone is validated, Attestoodle considers this part of the course is completed, so the corresponding time is credited to the student. The milestone should only be seen as a validation step.

When certificates are generated, times of completed milestones are aggregated for each student, for the selected period, and printed on their certificate.

Project leaders : Universities of Le Mans and Caen

Compatibility : Moodle 3.8, 3.9, 3.11

Plugin type : admin tool

Site :  
  [Attestoodle](https://attestoodle.univ-lemans.fr/)  
  [sciencesconf](https://attestoodle.sciencesconf.org/)  
  
Others plugins :  
  [Save and Restore Attestoodle](https://github.com/grp-attestoodle/moodle-tool_save_attestoodle)  
  [Web service Attestoodle](https://github.com/grp-attestoodle/moodle-local_wsattestoodle)  
  [Views of training at course level](https://github.com/grp-attestoodle/moodle-block_attestoodle)  
  [Planning of Attestoodle certificates](https://github.com/grp-attestoodle/moodle-tool_taskattestoodle)  
  [Attestoodle history management](https://github.com/grp-attestoodle/moodle-tool_history_attestoodle)
