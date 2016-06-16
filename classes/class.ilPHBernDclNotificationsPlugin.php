<?php
require_once('./Services/EventHandling/classes/class.ilEventHookPlugin.php');
require_once('./Services/Mail/classes/class.ilMail.php');
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
            $obj_id = $a_parameter['record_id'];

            /**
             * @var ilDclBaseRecordModel $record
             */
            $record = $a_parameter["record"];

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

				// iterate over all configuration entries
                foreach($collections as $collection) {
	                // extract all the config-fields per entry
                    $ref_id = $collection[srPHBernDclNotificationsConfig::F_DCL_REF_ID];
                    $table_id = $collection[srPHBernDclNotificationsConfig::F_DCL_TABLE_ID];

                    $mail_field = $collection[srPHBernDclNotificationsConfig::F_MAIL_FIELD_ID];
	                $base_lang_key = $collection[srPHBernDclNotificationsConfig::F_BASE_LANG_KEY];
	                $send_mail_check_field_id = $collection[srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_ID];
	                $send_mail_check_field_value = $collection[srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_VALUE];
	                
	                // check if current event is part of the current configuration
                    if($dcl->getRefId() == $ref_id && $dcl_table_id == $table_id) {
	                    $mail_texts = srPHBernDclNotificationsConfig::getConfigValue(srPHBernDclNotificationsConfig::F_DCL_MAIL_CONFIG);
	                    $mail_text_targets = array();
	                    foreach($mail_texts as $mail_text_entry) {
							if($mail_text_entry[srPHBernDclNotificationsConfig::F_DCL_MAIL_KEY] == $base_lang_key) {
								$mail_text_targets[$mail_text_entry[srPHBernDclNotificationsConfig::F_DCL_MAIL_TARGET]] = array(
									srPHBernDclNotificationsConfig::F_DCL_MAIL_SUBJECT => $mail_text_entry[srPHBernDclNotificationsConfig::F_DCL_MAIL_SUBJECT],
									srPHBernDclNotificationsConfig::F_DCL_MAIL_BODY => $mail_text_entry[srPHBernDclNotificationsConfig::F_DCL_MAIL_BODY],
								);
							}
	                    }

                        $raw_responsible = $record->getRecordField($mail_field)->getValue();

                        $doz = array();
                        foreach($raw_responsible as $responsible_key=>$user) {
                            $ilias_user = new ilObjUser($user);
                            $doz[$responsible_key] = $ilias_user->getLogin();
                        }

	                    // check send mail condition => if value equals the set value, the doz will receive a mail too
	                    // TODO: make it more generic
                        $responsible = null;
                        if($send_mail_check_field_id == "" || $record->getRecordField($send_mail_check_field_id)->getValue() == $send_mail_check_field_value) {
                            $responsible = $doz;
                        }

	                    // get all fields as markers
                        $fields = $dcl->getTableById($dcl_table_id)->getFields();
                        $replacements = array();
                        foreach($fields as $field) {
                            // skip standard-fields for now
                            if($field->isStandardField()) {
                                continue;
                            }

                            $record_field = $record->getRecordField($field->getId());
                            $value = htmlspecialchars(strip_tags(ilDclCache::getRecordRepresentation($record_field)->getHTML()));

                            $replacements['{' . mb_strtoupper($field->getTitle()). '}'] = $value;
                        }

                        // we use a _ as prefix so it cannot be overwritten by other fields
                        $replacements['{_LINK}'] = ilLink::_getStaticLink($dcl->getRefId(), $dcl->getType(), true, "_" . $record->getId());

	                    // there are mails for dozent and student (depends on the email condition)
                        $send_mails = array('extern', 'owner');
                        $all_email_targets = array($responsible, $ilUser->getLogin());

                        foreach($send_mails as $send_key=>$send_mail_target) {
                            if($all_email_targets[$send_key] == null || !isset($mail_text_targets[$send_mail_target])) {
                                continue;
                            }

                            // mail subject
                            $subject = $this->getLanguageText($mail_text_targets[$send_mail_target][srPHBernDclNotificationsConfig::F_DCL_MAIL_SUBJECT]);

                            // mail body
                            $body = $this->getLanguageText($mail_text_targets[$send_mail_target][srPHBernDclNotificationsConfig::F_DCL_MAIL_BODY]);

                            foreach($replacements as $replacement_key=>$replacement) {
                                $body = str_replace($replacement_key, $replacement, $body);
                            }

	                        // convert mail-addresses and body text into the required form
	                        $to_users = (is_array($all_email_targets[$send_key]))? implode("; ", $all_email_targets[$send_key]) : $all_email_targets[$send_key];
	                        $plain_text = strip_tags($body);

                            $mail_obj = new ilMail(ANONYMOUS_USER_ID);
                            $mail_obj->appendInstallationSignature(true);
                            $mail_obj->sendMail($to_users, "", "", $subject, $plain_text, array(), array( "normal" ));
                        }
                    }
                }
            }
        }
     }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function getLanguageText($text)
    {
        return str_replace('\n', "\n", $text);
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