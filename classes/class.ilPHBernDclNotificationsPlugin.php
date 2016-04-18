<?php
require_once('./Services/EventHandling/classes/class.ilEventHookPlugin.php');
require_once('./Services/Mail/classes/class.ilMimeMail.php');
require_once('./Services/Link/classes/class.ilLink.php');
require_once ('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');
/**
 * ilPHBernDclNotificationsPlugin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 *
 */
class ilPHBernDclNotificationsPlugin extends ilEventHookPlugin {

    /**
     * @var ilPHBernDclNotificationsPlugin
     */
    protected static $instance;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return ilPHBernDclNotificationsPlugin
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Handle the event
     *
     * @param    string        component, e.g. "Services/User"
     * @param    event        event, e.g. "afterUpdate"
     * @param    array        array of event specific parameters
     */
    public function handleEvent($a_component, $a_event, $a_parameter) {
        global $ilUser, $ilSetting;
        // Generate certificate if course is completed
        if ($a_component == 'Modules/DataCollection' && $a_event == 'createRecord') {
            $obj_id = $a_parameter['obj_id'];

            /**
             * @var ilDclBaseRecordModel $record
             */
            $record = $a_parameter["object"];

            /**
             * @var ilObjDataCollection $dcl
             */
            $dcl = $a_parameter['dcl'];

            /**
             * @var ilDclTable $table
             */
            $dcl_table_id = $a_parameter['table_id'];

            if ($obj_id && $record && $dcl) {
                $collections = srPHBernDclNotificationsConfig::getConfigValue(srPHBernDclNotificationsConfig::F_DCL_CONFIG);


                foreach($collections as $collection) {
                    $ref_id = $collection[srPHBernDclNotificationsConfig::F_DCL_REF_ID];
                    $table_id = $collection[srPHBernDclNotificationsConfig::F_DCL_TABLE_ID];

                    $mail_field = $collection[srPHBernDclNotificationsConfig::F_MAIL_FIELD_ID];
                    $base_lang_key = $collection[srPHBernDclNotificationsConfig::F_BASE_LANG_KEY];
                    $send_mail_check_field_id = $collection[srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_ID];
                    $send_mail_check_field_value = $collection[srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_VALUE];

                    if($dcl->getRefId() == $ref_id && $dcl_table_id == $table_id) {
                        $raw_responsible = $record->getRecordField($mail_field)->getValue();

                        $doz = array();
                        foreach($raw_responsible as $responsible_key=>$user) {
                            $ilias_user = new ilObjUser($user);
                            $doz[$responsible_key] = $ilias_user->getEmail();
                        }

                        $responsible = null;
                        if($record->getRecordField($send_mail_check_field_id)->getValue() == $send_mail_check_field_value) {
                            $responsible = $doz;
                        }

                        $fields = $dcl->getTableById($dcl_table_id)->getFields();
                        $replacements = array();
                        foreach($fields as $field) {
                            // skip standard-fields for now
                            if($field->isStandardField()) {
                                continue;
                            }

                            $record_field = $record->getRecordField($field->getId());
                            $value = htmlspecialchars(strip_tags(ilDclCache::getRecordRepresentation($record_field)->getHTML()));

                            $replacements['{' . strtoupper($field->getTitle()). '}'] = $value;
                        }
                        // we use a _ as prefix so it cannot be overwritten by other fields
                        $replacements['{_LINK}'] = ilLink::_getStaticLink($dcl->getRefId(), $dcl->getType(), true, "_" . $record->getId());

                        $send_mails = array('doz', 'student');
                        $send_to = array($responsible, $ilUser->getEmail());
                        foreach($send_mails as $send_key=>$send_mail_lang_key) {
                            if($send_to[$send_key] == null) {
                                continue;
                            }

                            $mail = new ilMimeMail();
                            $mail->autoCheck(false);
                            $mail->From($ilSetting->get("admin_email"));
                            $mail->To($send_to[$send_key]);

                            // mail subject
                            $subject = $this->getLanguageText($base_lang_key."_subject_".$send_mail_lang_key);

                            // mail body
                            $body = $this->getLanguageText($base_lang_key."_body_".$send_mail_lang_key);

                            foreach($replacements as $replacement_key=>$replacement) {
                                $body = str_replace($replacement_key, $replacement, $body);
                            }

                            $mail->Subject($subject);
                            $mail->Body($body);
                            $mail->Send();
                        }
                    }
                }
            }
        }
     }

    /**
     * @param string $a_keyword
     * @return string
     */
    protected function getLanguageText($a_keyword)
    {
        return str_replace('\n', "\n", $this->txt($a_keyword));
    }

    /**
     * Get Plugin Name. Must be same as in class name il<Name>Plugin
     * and must correspond to plugins subdirectory name.
     *
     * Must be overwritten in plugin class of plugin
     * (and should be made final)
     *
     * @return    string    Plugin Name
     */
    function getPluginName()
    {
        return "PHBernDclNotifications";
    }
}