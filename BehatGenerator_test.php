<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');
require_once($CFG->dirroot .'/blocks/quickmail/tests/behat/ferpa.QM.BehatGenerator.php');

class BehatTestcase extends advanced_testcase {

    public function testConfigStudentsCanUse(){
        $config1 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config1->allowStudents());

        $config2 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config2->allowStudents());

        $config3 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config3->allowStudents());

        $config4 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config4->allowStudents());

        $config5 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config5->allowStudents());

    }

    public function testScenario_clickOnComposeEmail(){

        $teacher = new User('t1', User::TEACHER);
        $student = new User('s1', User::STUDENT);

        $config1 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));
        $this->assertTrue($config1->allowStudents());


        $scenario1 = new ferpaScenario(array('config' => $config1));

        $this->assertTrue($config1->userRoleAllowed($teacher));
        //$bool1 = $scenario1->clickOnComposeEmail($teacher);
        //$this->assertTrue($bool1);

        $this->assertTrue($config1->userRoleAllowed($student));
        //$bool1 = $scenario1->clickOnComposeEmail($student);
        //$this->assertTrue($bool1);


        $config4 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_YES,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_NO,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config4->allowStudents());

        $scenario1 = new ferpaScenario(array('config' => $config4));

        $this->assertTrue($config4->userRoleAllowed($teacher));

        $this->assertFalse($config4->userRoleAllowed($student));
    }

    public function testScenario_userCanUse(){
        $student = new User('s1', User::STUDENT);
        $this->assertEquals(User::STUDENT, $student->role);

        $config1 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config1->allowStudents());
        $this->assertFalse($config1->userRoleAllowed($student));


        $config2 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NO,
            "allowStudentsCourse" => QMConfig::ALLOW_STUDENTS_COURSE_YES,
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertTrue($config2->allowStudents());
        $this->assertTrue($config2->userRoleAllowed($student));


        $config3 = new QMConfig(array(
            "allowStudentsGlobal" => QMConfig::ALLOW_STUDENTS_GLOBAL_NVR,
            "allowStudentsCourse" => '-',
            "groupsGlobal"        => QMConfig::GRP_GLOBAL_IGNORE,
            "groupsCourse"        => QMConfig::GRP_COURSE_NONE,
        ));

        $this->assertFalse($config3->allowStudents());
        $this->assertFalse($config3->userRoleAllowed($student));

    }

}