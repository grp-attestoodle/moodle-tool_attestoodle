# ATTESTOODLE

This plugin is used to generate periodical training certificates for students on a massive scale, based on learning milestones completion. Parler de la periodicité ?

An "Attestoodle training" models a training plan: a set of courses in the same Moodle category or sub-categories (an Attestoodle training is necessarily associated with a Moodle category).
An "Attestoodle milestone" is a Moodle activity (or sequence of activites) set with an activity completion. The state of activity completion is considered by Attestoodle as a milestone validation (completed or not). 
In the administration, training managers have to set a time for each milestone which represents an average completion time for that part of the course (sequence of activities) included in the milestone.  Once the milestone is validated, Attestoodle considers this part of the course is completed, so the corresponding time is credited to the student. The milestone should only be seen as a validation step.

When certificates are generated, times of completed milestones are aggregated for each student, for the selected period, and printed on their certificate.

Compatibility : Moodle 3.3, 3.4, ???
Plugin type : admin tool

