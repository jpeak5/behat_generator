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
 * Quickmail-specific subclass of the parent behat library.
 *
 * @package    test
 * @copyright  2015 Louisiana State University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

interface Stringy {
    function __toString();
}

/**
 * PHP emulation of a Java enumeration containing the acceptable Mink prefixes.
 */
interface Prefix {

    const GIVEN = 'Given';

    // 'AND' is a reserved word.
    const _AND  = 'And';
    const THEN  = 'Then';
}

/**
 * Fixture elements provided with the Moodle/Behat inetgration.
 * https://docs.moodle.org/dev/Acceptance_testing
 *
 * If you have your own custom generators, you may add to this enum.
 */
interface FixtureElement {
    const CATEGORIES = "categories";
    const COURSES    = "courses";
    const ACTIVITIES = "activities";
    const GROUPS     = "groups";
    const GROUPINGS  = "groupings";
    const USERS      = "users";
    const ENROLMENTS = "course enrolments";
    const ROLES      = "roles";
    const RO_ASSIGNS = "role assignments";
    const PERM_OVERR = "permissions overrides";
    const GROUP_MEMS = "group members";
    const GROUPING_G = "grouping groups";
    const CHORTS     = "cohorts";
}

/**
 * selectors, as defined https://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps
 */
interface Selector {
    const BLOCK = 'block';
    const BUTTON = 'button';
    const CHECKBOX = 'checkbox';
    const CSS_ELEMENT = 'css_element';
    const DIALOGUE = 'dialogue';
    const FIELD = 'field';
    const FIELDSET = 'fieldset';
    const FILE = 'file';
    const FILEMANAGER = 'filemanager';
    const LINK = 'link';
    const LINK_OR_BUTTON = 'link_or_button';
    const OPTGROUP = 'optgroup';
    const OPTION = 'option';
    const RADIO = 'radio';
    const REGION = 'region';
    const SELECT = 'select';
    const TABLE_ROW = 'table_row';
    const TABLE = 'table';
    const XPATH_ELEMENT = 'xpath_element';
}

/**
 * text selectors, as defined https://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps
 */
class TextSelector {
    const BLOCK = 'block';
    const CSS_ELEMENT = 'css_element';
    const DIALOGUE = 'dialogue';
    const FIELDSET = 'fieldset';
    const REGION = 'region';
    const TABLE = 'table';
    const TABLE_ROW = 'table_row';
    const XPATH_ELEMENT = 'xpath_element';
}
/**
 * A collection of utility methods for generating Mink step definitions.
 */
trait Steps {
    use OutputUtils;

    private function resolveOptionalPrefix($prefix = 'And'){
        return null === $prefix ? Prefix::_AND : $prefix;
    }
    /**
     * Generate the "And I log in as '$username'".
     * @param String $username
     * @param Prefix $prefix
     * @param int $indent number of tab stops to indent
     * @return string '<\t\t...><prefix> I log in as <username>'
     */
    public function loginAs($username, $prefix = 'And', $indent = 2){

        $t = $this->t($indent);
        return sprintf(
                "%s%s I log in as \"%s\"%s",
                $t, $prefix, $username, $this->n(), $t, $this->n());
    }

    /**
     *
     * @param type $link
     * @param Prefix $prefix
     * @param int $indent number of tab stops to indent
     * @return string '<\t\t...><prefix> I follow <link>'
     */
    public function follow($link, $prefix = 'And', $indent = 2){

        $t = $this->t($indent);
        return sprintf("%s%s I follow \"%s\"%s", $t, $prefix, $link, $this->n());
    }

    /**
     *
     * @return string "\t\tAnd I log out\n\n"
     */
    public function logOut() {
        $t = $this->t(2);
        $a = Prefix::_AND;
        $n = $this->n(2);

        return sprintf("%s%s I log out%s", $t, $a, $n);
    }

    /**
     *
     * @param FixtureElement $fixtureElement
     * @param array $fields
     * @param Prefix $prefix
     * @return type
     */
    public function theFollowingExist($fixtureElement, array $fields, $prefix = 'And'){

        $n = $this->n();
        $str = sprintf("%s%s the following \"%s\" exist:%s", $this->t(1), $prefix, $fixtureElement, $n);

        $t = $this->t(2);
        foreach($fields as $cells){
            $str .= sprintf("%s|%s|%s", $t, implode('|', $cells), $n);
        }
        return $str;
    }

    /**
     * Mink step to press a button
     * @param string $what
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return '[$prefix] I press "[$what]"'
     */
    public function iPress($what, $prefix = 'And', $indent = 2){

        return sprintf('%s%s I press "%s"%s', $this->t($indent), $prefix, $what, $this->n());
    }

    /**
     * Client of Steps::iPress()
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix] I press "Save changes"'
     */
    public function saveChanges($prefix = 'And', $indent = 2){

        return $this->iPress('Save changes', $prefix, $indent);
    }


    /**
     * Click on the element of the specified type which is located inside the second element.
     *
     * @param string $selectorid
     * @param Selector $selector
     * @param string $textselectorid
     * @param TextSelector $textselector
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix] I click on "[selectorid]" "[selector]" in the "textselectorid" "[textselector]"'
     */
    public function iClickOn($selectorid, $selector, $textselectorid, $textselector, $prefix = 'And', $indent = 2){

        return sprintf('%s%s I click on "%s" "%s" in the "%s" "%s"%s', $this->t($indent), $prefix, $selectorid, $selector, $textselectorid, $textselector, $this->n());
    }

    /**
     *
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix] I turn editing mode on'
     */
    public function turnEditingOn($prefix = 'And', $indent = 2){
        return sprintf("%s%s I turn editing mode on%s", $this->t($indent), $prefix, $this->n());
    }

    /**
     *
     * @param string $text the text we're looking for
     * @param string $textselectorid id text for the text selector
     * @param TextSelector $textselector
     * @param bool $not if true, the output will include '...should not see...'
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix] I should [not] see [text] in the [textselectorid] [textselector]'
     */
    public function shouldSeeWhere($text, $textselectorid, $textselector, $not = false, $prefix = 'And', $indent = 2){
        $not = $not ? ' not' : '';
        return sprintf('%s%s I should%s see "%s" in the "%s" "%s"%s', $this->t($indent), $prefix, $not, $text, $textselectorid, $textselector, $this->n());
    }

    /**
     *
     * @param string $text
     * @param bool $not
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix] I should [not] see "[text]"'
     */
    public function shouldSee($text, $not = false, $prefix = 'And', $indent = 2){
        $not = $not ? ' not' : '';
        return sprintf('%s%s I should%s see "%s"%s', $this->t($indent), $prefix, $not, $text, $this->n());
    }

    /**
     *
     * @param string $blockname name of a block to add
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string '[prefix]' I add the [blockname] block'
     */
    public function addBlock($blockname, $prefix = 'And', $indent = 2){

        return sprintf('%s%s I add the "%s" block%s', $this->t($indent), $prefix, $blockname, $this->n());
    }

    /**
     *
     * @param array $fields specifying field (key) values, one per subarray
     * in the following example structure:
     *      array(
     *            array(key1, val1),
     *            array(key2, val2)
     *           )
     *
     * @param bool $admin since the step definition for setting admin settings
     * and for setting arbitrary form fields is so similar, this function
     * can do both. Specify $admin = true for the former, false for the latter.
     *
     * @param Prefix $prefix
     * @param int $indent the number of tabstops to indent
     * @return string one of the following forms:
     *     '[prefix] I set the following administration values:
     *             |key1|val1|
     *             |key2|val2|'
     *
     *     '[prefix] I set the following fields to these values values:
     *             |key1|val1|
     *             |key2|val2|'
     */
    public function setFields($fields, $admin = false, $prefix = 'And', $indent = 2){

        $command = $admin ? "I set the following administration settings values:" : "I set the following fields to these values:";
        $str = sprintf('%s%s %s%s', $this->t($indent), $prefix, $command, $this->n());
        foreach($fields as $f){
            $str.= sprintf('%s|%s|%s', $this->t($indent+1), implode('|', $f), $this->n());
        }
        return $str;
    }

    /**
     * Helper method for building comment strings
     * @param string $str the text of the comment line
     * @param int $indent the number of tabstops to indent
     * @return string '# [str]'
     */
    public function comment($str = '', $indent = 0){
        return sprintf("%s# %s%s", $this->t($indent), $str, $this->n());
    }
}

/**
 * Base class providing mass assignment through the constructor.
 */
abstract class ObjectBase {

    /**
     * Perform mass assignment using the input keys and values.
     * Only values for keys that exist in descendent classes will be assigned.
     * @param object|array $params objects will be cast to array.
     */
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

/**
 * Extendible class modeling a Behat feature file.
 * Includes methods for setting up feature-level content
 * as well as writing the final feature string to file.
 */
class Feature extends ObjectBase {
    use UserAccess, OutputUtils;

    /**
     * @var string filename
     */
    public $file;
    protected $tags = array();
    protected $title;
    protected $headerComment ='';

    public $users, $groups, $courses;

    public $background;
    public $scenarios;

    public function __construct($params = array()) {
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

abstract class Background extends ObjectBase implements Stringy, FixtureElement {
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
        $fields = array(array("fullname", "shortname", "category"));

        foreach($this->feature->courses as $course) {
            $fields[] = array($course->name, $course->shortname, 0);
        }
        return $this->theFollowingExist(self::COURSES, $fields, Prefix::GIVEN);
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

        if(null === $value){
            return $this->settings[$key]->value();
        }else{
            $this->settings[$key]->value($value);
        }
    }

    public static function allConfigs() {
        // filter out empty values
        $settings = array_filter(static::settings());

        $rawconfigs = array(array());

        foreach ($settings as $setting) {
            $append = array();

            foreach($rawconfigs as $conf) {
                foreach($setting['options'] as $option) {
                    $conf[$setting['setting']['key']] = $option->key;
                    $append[] = $conf;
                }
            }

            $rawconfigs = $append;
        }


        $configs = array();
        foreach($rawconfigs as $conf){
            $c = new static;

            foreach($conf as $key => $value){
                $c->settingValue($key, $value);
            }
            $configs[] = $c;
        }
        $result = array_filter($configs, 'static::filter');
//        foreach($result as $r){
//            var_dump($r);
//        }

        return $result;
    }

    protected static function filter(Config $config){

        // Sub-classes may, and should, implement this method.
        return true && static::filter($config);
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
     * option key is returned from $this->value.
     *
     * Setter: If a valid option key is given, it is stored in $this->value.

     * @see Setting::$value
     * @param string $newoptionkey
     * @return string
     * @throws Exception $key must be valid in the sense that it exists as a key
     * in the $this->options map
     */
    public function value($newoptionkey = null){
        if(null === $newoptionkey){
            if(null === $this->value){
                return null;
            }
            return $this->value;
        }else{
            if(!array_key_exists($newoptionkey, $this->options)){
                throw new Exception(sprintf("Invalid key '%s' for setting '%s'", $newoptionkey, $this->label));
            }
            $this->value = $newoptionkey;
        }
    }

    /**
     * Returns the string representation of the currently-selected option.
     * @return string
     * @throws Exception if no value is set
     * @see Setting::__toString();
     * @see Setting::$value
     * @see Setting::$options
     */
    public function valueString(){
        if(null === $this->value){
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
        if($this->value === null){
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