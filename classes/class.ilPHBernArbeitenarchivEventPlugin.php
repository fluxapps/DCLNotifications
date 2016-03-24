<?php
require_once('./Services/EventHandling/classes/class.ilEventHookPlugin.php');
require_once('./Services/Mail/classes/class.ilMimeMail.php');
require_once('./Services/Link/classes/class.ilLink.php');
/**
 * ilPHBernArbeitenarchivEventPlugin
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 *
 */
class ilPHBernArbeitenarchivEventPlugin extends ilEventHookPlugin {
    const DCL_REF_ID = 70;
    const TABLE_ID = 1;

    const RESPONSIBLE_USERS_FIELD_ID = 17;
    const AUTHOR_USER_FIELD_ID = 11;
    const TITLE_FIELD_ID = 14;

    public function __construct() {
        parent::__construct();
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
            $table_id = $a_parameter['table_id'];

            if ($obj_id && $record && $dcl && $dcl->getRefId() == self::DCL_REF_ID && $table_id == self::TABLE_ID) {
                $responsible = $record->getRecordField(self::RESPONSIBLE_USERS_FIELD_ID)->getValue();

                foreach($responsible as $responsible_key=>$user) {
                    $ilias_user = new ilObjUser($user);
                    $responsible[$responsible_key] = $ilias_user->getEmail();
                }

                $authors = $record->getRecordField(self::RESPONSIBLE_USERS_FIELD_ID)->getValue();
                foreach($authors as $author_key=>$user) {
                    $ilias_user = new ilObjUser($user);
                    $authors[$author_key] = $ilias_user->getFullname();
                }

                $title = $record->getRecordField(self::TITLE_FIELD_ID)->getValue();

                $mail = new ilMimeMail();
                $mail->autoCheck(false);
                $mail->From($ilSetting->get("admin_email"));
                $mail->To($responsible);

                // mail subject
                $subject = $this->txt("added_work_record_subject");

                // mail body
                $record_href = ilLink::_getStaticLink($dcl->getRefId(), $dcl->getType(), true, "_".$record->getId());

                $body = $this->txt("added_work_record_body");
                $body = str_replace("{LINK}", $record_href, $body);
                $body = str_replace("{AUTHORS}", implode(", ", $authors), $body);
                $body = str_replace("{TITLE}", $title, $body);

                $mail->Subject($subject);
                $mail->Body($body);
                $mail->Send();
            }
        }
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
        return "PHBernArbeitenarchivEvent";
    }
}