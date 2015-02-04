<?php

interface Stringy {
    function __toString();
}

trait Steps {
    use OutputUtils;

    public function loginAs($username, $prefix = "And", $level = 2){
        $t = $this->t($level);
        return sprintf(
                "%s%s I log in as \"%s\"%s",
                $t, $prefix, $username, $this->n(), $t, $this->n());
    }

    public function follow($link, $prefix = "And"){
        $t = $this->t(2);
        return sprintf("%s%s I follow \"%s\"%s", $t, $prefix, $link, $this->n());
    }

    public function logOut() {
        $t = $this->t(2);
        $n = $this->n(2);
        return sprintf("%sAnd I log out%s", $t, $n);
    }

    public function theFollowingExist($what, array $fields, $prefix = "And"){
        $n = $this->n();
        $str = sprintf("%s%s the following \"%s\" exist:%s", $this->t(1), $prefix, $what, $n);

        $t = $this->t(2);
        foreach($fields as $cells){
            $str .= sprintf("%s|%s|%s", $t, implode('|', $cells), $n);
        }
        return $str;
    }

    public function iPress($what, $prefix = 'And', $level = 2){
        return sprintf('%s%s I press "%s"%s', $this->t($level), $prefix, $what, $this->n());
    }

    public function saveChanges($level = 2, $prefix = 'And'){
        return $this->iPress('Save changes', $prefix, $level);
    }

    public function iClickOn($whatId, $whatType, $whereId, $whereType, $prefix = 'And', $level = 2){
        return sprintf('%s%s I click on "%s" "%s" in the "%s" "%s"%s', $this->t($level), $prefix, $whatId, $whatType, $whereId, $whereType, $this->n());
    }

    public function turnEditingOn($prefix = 'And', $level = 2){
        return sprintf("%s%s I turn editing mode on%s", $this->t($level), $prefix, $this->n());
    }

    public function shouldSeeWhere($what, $where, $whereType, $not = false, $prefix = 'And', $level = 2){
        $not = $not ? ' not' : '';
        return sprintf('%s%s I should%s see "%s" in the "%s" "%s"%s', $this->t($level), $prefix, $not, $what, $where, $whereType, $this->n());
    }

    public function shouldSee($what, $not = false, $prefix = 'And', $level = 2){
        $not = $not ? ' not' : '';
        return sprintf('%s%s I should%s see "%s"%s', $this->t($level), $prefix, $not, $what, $this->n());
    }

    public function addBlock($block, $prefix = 'And', $level = 2){
        return sprintf('%s%s I add the "%s" block%s', $this->t($level), $prefix, $block, $this->n());
    }

    public function setFields($fields, $admin = false, $prefix = 'And', $level = 2){
        $command = $admin ? "I set the following administration settings values:" : "I set the following fields to these values:";
        $str = sprintf('%s%s %s%s', $this->t($level), $prefix, $command, $this->n());
        foreach($fields as $f){
            $str.= sprintf('%s|%s|%s', $this->t($level+1), implode('|', $f), $this->n());
        }
        return $str;
    }

    public function comment($str = '', $level = 0){
        return sprintf("%s# %s%s", $this->t($level), $str, $this->n());
    }
}


class ObjectBase {
    public function __construct($params = array()) {
        if(is_object($params)){
            $params = (array)$params;
        }
        $fields = array_keys(get_object_vars($this));

        foreach($params as $k => $v){
            if(in_array($k, $fields)){
                $this->$k = $v;
            }
        }
    }
}

class Feature extends ObjectBase {
    use UserAccess, OutputUtils;
    public $file;
    protected $tags = array();
    protected $title;
    protected $headerComment ='';

    public $users, $groups, $courses;

    public $background;
    public $scenarios;

    public function __construct($params = array()) {
        if(array_key_exists('tags', $params)){
            unset($params['tags']);
        }
        parent::__construct($params);
    }

    public function appendComment($comment){
        $this->headerComment .= $this->n(2).$comment;
    }

    public function addTag($tag){
        $tag = '@'.trim($tag);
        if(!in_array($tag, $this->tags)){
            $this->tags[] = $tag;
        }
    }

    public function tags(){
        return implode(' ', $this->tags).$this->n();
    }

    public function addScenario(Scenario $s){
        if(empty($this->scenarios)){
            $this->scenarios = array();
        }
        $s->feature = $this;
        $this->scenarios[] = $s;
    }

    public function addCourse(Course $c){
        if(empty($this->courses)){
            $this->courses = array();
        }
        if(!in_array($c->shortname, $this->courses)){
            $this->courses[$c->shortname] = $c;
        }
    }

    public function title(){
        return sprintf("%s %s%s", "Feature:", $this->title, $this->n());
    }

    public function string() {
        $str = "";
        $str = $this->tags()
                .$this->title().$this->n(2)
                .$this->headerComment.$this->n(2)
                .$this->background;

        foreach($this->scenarios as $s){
            $str .= $s->string();
        }
        return $str;
    }

    public function toFile(){
        if(($handle = fopen($this->file, 'w')) !== false){
            fwrite($handle, $this->string());
            fclose($handle);
        }
    }

    public function addUserToGroup(User $user, Group $group){
//        var_dump($this->users[$user->username]->username, $this->users[$user->username]->groups);
        if(!array_key_exists($user->username, $this->groups[$group->name]->members)){
            $this->groups[$group->name]->members[$user->username] = $user;
        }
        if(!in_array($group->name, $this->users[$user->username]->groups)){
            $this->users[$user->username]->groups[] = $group->name;
        }
//        var_dump($this->users[$user->username]->username, $this->users[$user->username]->groups);
    }

    public function initGroupsUsers(array $grouspUsers = array()){
        if(!empty($this->groups) || !empty($this->users)){
            throw new Exception("Cannot alter group membership.");
        }
        $this->users = $this->groups = array();

        if(empty($grouspUsers)){
            $groupsUsers = array(
                'group1' => array(
                    't1' => Role::TEACHER,
                    't4' => Role::EDITINGTEACHER,
                    's1' => Role::STUDENT,
                    's4' => Role::STUDENT
                    ),
                'group2' => array(
                    't2' => Role::TEACHER,
                    't4' => Role::EDITINGTEACHER,
                    's2' => Role::STUDENT,
                    's4' => Role::STUDENT,
                    ),
                'group3' => array(
                    't3' => Role::TEACHER,
                    't4' => Role::TEACHER,
                    's3' => Role::STUDENT
                    ),
                'Not in a group' => array(
                    't5' => Role::TEACHER,
                    's5' => Role::STUDENT)
            );
        }

        $u = function($username, $role) {
            return new User($username, $role);
        };
        $g = function($name, $members){
            return new Group($name, $members);
        };
        foreach($groupsUsers as $gname => $users){
            $group = $g($gname, array());
            if(!empty($this->groups[$group->name])){
                throw new Exception("Possibly trying to create two groups with the same name");
            }
            $group->feature = $this;
            $this->groups[$group->name] = $group;

            foreach($users as $name => $role){
                if(!array_key_exists($name, $this->users)){
                    $user = $u($name, $role);
                    $user->feature = $this;
                    $this->users[$user->username] = $user;
                }else{
                    $user = $this->users[$name];
                }
                $this->addUserToGroup($user, $group);
            }
        }
    }
}

abstract class Background extends ObjectBase implements Stringy {
    use UserAccess, Steps;
    protected $feature;

    public function __construct(Feature $f, $params = array()){
        // prevent incoming params
        if(!empty($params)){
            $params = array();
        }

        parent::__construct($params);
        $this->feature = $f;
        $f->background = $this;
    }

    public function __toString() {
        return $this->header().$this->courses().$this->users().$this->enrolments().$this->groups().$this->groupMembership();
    }
    public function header(){
        return sprintf("Background:%s", $this->n());
    }

    public function courses(){
        $prefix = 'Given';
        $fields = array(array("fullname", "shortname", "category"));
        $what = 'courses';

        foreach($this->feature->courses as $course) {
            $fields[] = array($course->name, $course->shortname, 0);
        }
        return $this->theFollowingExist($what, $fields, $prefix);
    }

    public function users(){
        $what = 'users';
        $fields = array(array('username', 'firstname', 'lastname'));

        foreach($this->feature->users as $name => $user){
            $fields[] = array($name, $user->firstname, $user->lastname);
        }
        return $this->theFollowingExist($what, $fields);
    }

    public function enrolments(){
        $what = 'course enrolments';
        $fields = array(array('user', 'course', 'role'));

        foreach($this->feature->users as $user){
            foreach($this->feature->courses as $c){
                $fields[] = array($user->username, $c->shortname, $user->roleName());
            }
        }
        return $this->theFollowingExist($what, $fields);
    }

    public function groups(){
        $what = 'groups';
        $fields = array(array('name','course', 'idnumber'));

        foreach($this->feature->groups as $g){
            if($g->name === 'Not in a group'){
                continue;
            }
            foreach($this->feature->courses as $c){
                $fields[] = array($g->name, $c->shortname, $g->name);
            }
        }
        return $this->theFollowingExist($what, $fields);
    }

    public function groupMembership(){
        $what = "group members";
        $fields = array(array('user', 'group'));
        foreach($this->feature->users as $user){
            foreach($user->groups as $g){
                if($g === 'Not in a group'){
                    continue;
                }
                $fields[] = array($user->username, $g);
            }
        }
        return $this->theFollowingExist($what, $fields);
    }
}

abstract class Scenario extends ObjectBase implements Stringy {
    use Steps;
    private static $counter = 0;
    public $config;
    public $feature;
    abstract function headerComment();

    public function header(){
        return sprintf("Scenario: %d%s", self::$counter++, $this->n());
    }

    public function configuration(){
        return sprintf("%s%s",$this->config, $this->n());
    }

    public function __toString() {
        return '';
    }

    public function string(){
        return $this->headerComment().$this->header()
                .$this->configuration()
                .$this->steps();
    }
}

abstract class Config extends ObjectBase implements Stringy {
    use Steps;
    protected $settings = array();

    public function settingValue($key, $value = null){
        if(!array_key_exists($key, $this->settings)){
            throw new Exception(sprintf("Setting does not exist for key '%s'", $key));
        }

        if(empty($value)){
            return $this->settings[$key]->value();
        }else{
            $this->settings[$key]->value($value);
        }
    }
}

class Setting  extends ObjectBase {

    /**
     * Label for this setting, as presented in the UI
     * @var string
     */
    public $label;

    /**
     * Key for this setting. Should be unique across all settings.
     * Used internally.
     * @var string
     */
    public $key;

    /**
     * Array of SettingOption objects that this setting provides.
     * @var SettingOption[]
     */
    protected $options;

    /**
     * A unique label for the setting.
     * In cases where an admin and a course-level setting use the same name,
     * they can be differentiated with this field.
     * Optional field
     * @see Setting::getUniqueLabel()
     * @var string
     */
    protected $uniquelabel;

    /**
     * Hash map provides reverse lookup of option keys by their labels.
     * Has the form array(optionlabel => optionkey, option1label => option1key);
     * The key can then be used to access the actual option object, ehld in the
     * $options array.
     * @see Setting::$options
     * @var string[]
     */
    protected $byLabel = array();

    /**
     * Holds the key to the currently-selected option for this setting.
     * Accessed/set via the value() getter/setter
     * @see Setting::value()
     * @var string
     */
    private $value;

    /**
     * Adds the input option to the list of setting options.
     * Additionanlly, this fn populates the reverse-lookup array byLabel;
     * @see Setting::$options
     * @see Setting::$byLabel
     * @see Setting::optionByLabel()
     * @see Setting::optionByKey()
     * @param SettingOption $option
     */
    public function addOption(SettingOption $option){
        $this->options[$option->key] = $option;
        $this->byLabel[$option->label] = $option->key;
    }

    /**
     * Given an option label string, return the actual option
     * object from the options list.
     * @see Setting::$options
     * @see Setting::$byLabel
     * @param string $optionLabel
     * @return SettingOption
     */
    public function optionByLabel($optionLabel){
        return $this->options[$this->byLabel[$optionLabel]];
    }

    /**
     * Given an option key, return the actual option object from the options list
     * @see Setting::$options
     * @param string $key
     * @return SettingOption
     */
    public function optionByKey($key){
        return $this->options[$key];
    }

    /**
     * Gets or sets the current value of this setting by storing the
     * selected option's key in the $$this->value member variable.
     *
     * Getter: If no option key is given as input, the currently-selected
     * option key is returned from $this->value. If no value is set, and no
     * input option key is provided, this fn returns '-', which signifies
     * a setting that has no value within Moodle.
     *
     * Setter: If a valid option key is given, it is stored in $this->value.

     * @see Setting::$value
     * @param string $key
     * @return string
     * @throws Exception $key must be valid in the sense that it exists as a key
     * in the $this->options map
     */
    public function value($key = null){
        if(empty($key)){
            if(empty($this->value)){
                return '-';
            }
            return $this->value;
        }else{
            if(!array_key_exists($key, $this->options)){
                throw new Exception(sprintf("Invalid key '%s' for setting '%s'", $key, $this->label));
            }
            $this->value = $key;
        }
    }

    /**
     * Returns the string representation of the currently-selected option.
     * @return string
     * @throws Exception if no value is set
     * @todo handle the exception case in a better way, ie always initialize value '-'.
     * @see Setting::__toString();
     * @see Setting::$value
     * @see Setting::$options
     */
    public function valueString(){
        if(empty($this->value)){
            throw new Exception(sprintf("No value set for %s\n", $this->label));
        }
        return (string)$this->options[$this->value];
    }

    /**
     * This method should be used when the caller needs a unique label for
     * the setting. In some cases, an admin-level and a course-level will
     * share the same name/label; then, we need a way to differentiate.
     *
     * If no unique label has been provided, this fn simply returns $this->label.
     * @see Setting::$uniquelabel
     * @return string
     */
    public function getUniqueLabel(){
        return !empty($this->uniquelabel) ? $this->uniquelabel : $this->label;
    }

    /**
     * Magic method returning a string representation of the Setting object.
     * @return string
     */
    public function __toString(){
        if(empty($this->value)){
            $optionlabel = 'not set';
        }else{
            $optionlabel = $this->options[$this->value]->label;
        }
        return sprintf("%s: %s", $this->getUniqueLabel(), $optionlabel);
    }
}

/**
 * Simple data structure modeling one of a settings options.
 * @see Setting
 */
class SettingOption extends ObjectBase {

    /**
     * unique key for the option
     * @var string | int
     */
    public $key;

    /**
     * String label for the option as
     * @var string
     */
    public $label;

    /**
     * Magic method returning a string representation of the object.
     * @return string
     */
    public function __toString(){
        return $this->label;
    }
}

/**
 * Models a Moodle User
 */
class User {

    public $username, $groups = array(), $role, $firstname, $lastname, $feature;

    /**
     *
     * @param string $name
     * @param string $r
     */
    public function __construct($name, $r) {
        $this->username = $this->firstname = $name;
        $this->role     = $r;
        $this->lastname = $this->roleName();
    }

    public function groups(){
        $groups = array();
        foreach($this->groups as $gname){
            $groups[$gname] = $this->feature->groups[$gname];
        }

        if($this->role === Role::EDITINGTEACHER){
            $groups = $this->feature->groups;
        }
        return $groups;
    }

    public function roleName(){
        return Role::name($this->role);
    }

    public function isTeacher(){
        return $this->role == Role::EDITINGTEACHER || $this->role == Role::TEACHER;
    }

    /**
     * @TODO find out if this is used anymore
     *
     * @return type
     */
    public function __toString() {
        $str = "$this->firstname $this->lastname";

        if(!empty($this->groups)){
            $grpStr = implode(',', array_keys($this->groups()));
            $str .= " ($grpStr)";
        }

        return $str;
    }
}

class Group {

    public $name, $members, $feature;

    public function __construct($name, array $members) {

        $this->name = $name;
        if(!empty($members)){
            $this->members = array();
            foreach($members as $m){
                $type = get_class($m);
                if($type !== 'User'){
                    throw new Exception("Objects passed in the members array must be of type 'User'\nGot $type");
                }
                $this->members[$m->username] = $m;
            }
        }else{
            $this->members = array();
        }
    }
}

trait UserAccess {
    public function u($name){
        $user = !empty($this->feature->users[$name]) ? $this->feature->users[$name] : false;
        return $user;
    }
}

trait OutputUtils {

    private function concat($seq, $count){
        $string = "";
        if($count >= 1){
            foreach(range(1,$count) as $c){
                $string .= $seq;
            }
        }
        return $string;
    }

    /**
     * Return a string consisting of n newlines (\n)
     * @param int $count
     * @return string n newlines as string
     */
    public function n($count = 1){
        return $this->concat("\n", $count);
    }

    /**
     * Return a string consisting of n tabs (\t),
     * which are actually given as spaces...
     * @param int $count
     * @return string n tabs as string
     */
    public function t($count){
        return $this->concat("    ", $count);
    }
}

class Course extends ObjectBase {
    public $name, $shortname;
}

class Role {
    const EDITINGTEACHER = 1;
    const TEACHER = 3;
    const STUDENT = 0;

    static $map = array(self::EDITINGTEACHER => 'editingteacher', self::TEACHER => 'teacher', self::STUDENT => 'student');

    public static function name($key){
        return self::$map[$key];
    }

    public static function value($name){
        $flip = array_flip(self::$map);
        return $flip[$name];
    }
}