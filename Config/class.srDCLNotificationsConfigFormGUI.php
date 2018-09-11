<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/class.ilDclContentImporterPlugin.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Helper/class.srDclContentImporterMultiLineInputGUI.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/classes/class.ilDCLNotificationsPlugin.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/Config/class.srDCLNotificationsConfig.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/Config/class.srDCLNotificationsConfigFormGUI.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/DCLNotifications/classes/class.ilPHBernTextAreaInputGUI.php');

/**
 * Class srDCLNotificationsConfigFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srDCLNotificationsConfigFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilDCLNotificationsConfigGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDclContentImporterPlugin
     */
    protected $pl;
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * @param ilDclContentImporterConfigGUI $parent_gui
     */
    public function __construct($parent_gui)
    {
        global $ilCtrl, $lng;
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->pl = ilDCLNotificationsPlugin::getInstance();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle('DataCollection Notifications');
        $this->initForm();
    }


    /**
     * @param $field
     *
     * @return string
     */
    public function txt($field)
    {
        return $this->pl->txt('admin_form_' . $field);
    }


    protected function initForm()
    {
        global $tpl;

        $tpl->addInlineCss("textarea {min-width: 520px !important;}");

        $multiinput = new srDclContentImporterMultiLineInputGUI("DataCollections", srDCLNotificationsConfig::F_DCL_CONFIG);
        $multiinput->setInfo("1) DataCollection-Ref-ID: Ref-ID der betroffenen DataCollection<br />2) DataCollection-Table-ID: Tabellen-ID der DataCollection<br />3) Mail Field ID / E-Mail-Adresse: Entweder Feld der Tabelle mit dem UserSelector (im Dropdown mode) oder feste E-Mail-Adresse<br >4) Language Base Key: Ein Language-Selector für eigene Nachrichten (irgend_ein_key)<br />5) Send Mail Field ID: Feld welches geprüft wird, ob es den Wert 'Send Mail Field Value' hat. Sonst wird nur der Owner eine Mail erhalten.<br />6) Send Mail Field Value: Wert welches das 'Send Mail Field' haben muss, damit eine Mail ausgelöst wird.<br />7) Event: Aktion, bei welcher die Notification ausgelöst werden soll.");
        $multiinput->setTemplateDir(ilDclContentImporterPlugin::getInstance()->getDirectory());

        $ref_id_item = new ilTextInputGUI('Datacollection Ref-ID', srDCLNotificationsConfig::F_DCL_REF_ID);
        $multiinput->addInput($ref_id_item);

        $table_id_item = new ilTextInputGUI('Datacollection Table-ID', srDCLNotificationsConfig::F_DCL_TABLE_ID);
        $multiinput->addInput($table_id_item);

        $mail_field = new ilTextInputGUI('Mail Field ID', srDCLNotificationsConfig::F_MAIL_FIELD_ID);
        $multiinput->addInput($mail_field);

        $base_lang_key_field = new ilTextInputGUI('Base Lang Key Field', srDCLNotificationsConfig::F_BASE_LANG_KEY);
        $multiinput->addInput($base_lang_key_field);

        $send_mail_field = new ilTextInputGUI('Send Mail Field ID', srDCLNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_ID);
        $multiinput->addInput($send_mail_field);

        $send_mail_field_value = new ilTextInputGUI('Send Mail Field Value', srDCLNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_VALUE);
        $multiinput->addInput($send_mail_field_value);

        $event = new ilTextInputGUI('Event', srDCLNotificationsConfig::F_SEND_MAIL_EVENT);
        $multiinput->addInput($event);

        $this->addItem($multiinput);

        $multiinput_email = new srDclContentImporterMultiLineInputGUI("Mail-Text", srDCLNotificationsConfig::F_DCL_MAIL_CONFIG);
        $multiinput_email->setInfo("1) Mail-Text-Key: Key welcher oben als Base Lang Key Hinterlegt wird. <br />2) Mail Ziel (Besitzer / Externer) <br />3) Mail Betreff<br />4) Mail inhalt (es können alle Dcl Spaltentitel in CABS verwendet werden)");
        $multiinput_email->setTemplateDir(ilDclContentImporterPlugin::getInstance()->getDirectory());

        $language_key = new ilTextInputGUI('Mail-Text-Key', srDCLNotificationsConfig::F_DCL_MAIL_KEY);
        $multiinput_email->addInput($language_key);

        $mail_target = new ilSelectInputGUI('Mail-Target', srDCLNotificationsConfig::F_DCL_MAIL_TARGET);
        $mail_target->setOptions(array('owner'=>'Besitzer', 'extern'=>'Externer'));
        $multiinput_email->addInput($mail_target);

        $mail_subject= new ilTextInputGUI('Mail-Subject', srDCLNotificationsConfig::F_DCL_MAIL_SUBJECT);
        $multiinput_email->addInput($mail_subject);

        $mail_body = new ilPHBernTextAreaInputGUI('Mail-Body', srDCLNotificationsConfig::F_DCL_MAIL_BODY);
        $mail_body->setRows(10);
        $mail_body->setCols(50);
        $multiinput_email->addInput($mail_body);

        $this->addItem($multiinput_email);

        $this->addCommandButtons();
    }


    public function fillForm()
    {
        $array = array();
        foreach ($this->getItems() as $item) {
            $this->getValuesForItem($item, $array);
        }
        $this->setValuesByArray($array);
    }


    /**
     * @param ilFormPropertyGUI $item
     * @param                   $array
     *
     * @internal param $key
     */
    private function getValuesForItem($item, &$array)
    {
        if (self::checkItem($item)) {
            $key = $item->getPostVar();
            $array[$key] = srDCLNotificationsConfig::getConfigValue($key);
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->getValuesForItem($subitem, $array);
                }
            }
        }
    }


    /**
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        foreach ($this->getItems() as $item) {
            $this->saveValueForItem($item);
        }

        return true;
    }


    /**
     * @param  ilFormPropertyGUI $item
     */
    private function saveValueForItem($item)
    {
        if (self::checkItem($item)) {
            $key = $item->getPostVar();

            srDCLNotificationsConfig::set($key, $this->getInput($key));
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->saveValueForItem($subitem);
                }
            }
        }
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkForSubItem($item)
    {
        return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI and !$item instanceof ilEMailInputGUI;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkItem($item)
    {
        return !$item instanceof ilFormSectionHeaderGUI;
    }


    protected function addCommandButtons()
    {
        $this->addCommandButton('save', $this->lng->txt('save'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }
}