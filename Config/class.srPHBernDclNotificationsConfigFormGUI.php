<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/class.ilDclContentImporterPlugin.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Helper/class.srDclContentImporterMultiLineInputGUI.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/classes/class.ilPHBernDclNotificationsPlugin.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfigFormGUI.php');

/**
 * Class srPHBernDclNotificationsConfigFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srPHBernDclNotificationsConfigFormGUI extends ilPropertyFormGUI
{

    /**
     * @var srPHBernArbeitenarchivConfigGUI
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
        $this->pl = ilPHBernDclNotificationsPlugin::getInstance();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle('PHBern Dcl-Notifications');
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
        $multiinput = new srDclContentImporterMultiLineInputGUI("DataCollections", srPHBernDclNotificationsConfig::F_DCL_CONFIG);
        $multiinput->setInfo("DataCollection-Ref-ID, DataCollection-Table-ID, Mail Field ID, Language Base Key, Send Mail Field ID, Send Mail Field Value");
        $multiinput->setTemplateDir(ilDclContentImporterPlugin::getInstance()->getDirectory());

        $ref_id_item = new ilTextInputGUI('Datacollection Ref-ID', srPHBernDclNotificationsConfig::F_DCL_REF_ID);
        $multiinput->addInput($ref_id_item);

        $table_id_item = new ilTextInputGUI('Datacollection Table-ID', srPHBernDclNotificationsConfig::F_DCL_TABLE_ID);
        $multiinput->addInput($table_id_item);

        $mail_field = new ilTextInputGUI('Mail Field ID', srPHBernDclNotificationsConfig::F_MAIL_FIELD_ID);
        $multiinput->addInput($mail_field);

        $base_lang_key_field = new ilTextInputGUI('Base Lang Key Field', srPHBernDclNotificationsConfig::F_BASE_LANG_KEY);
        $multiinput->addInput($base_lang_key_field);

        $send_mail_field = new ilTextInputGUI('Send Mail Field ID', srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_ID);
        $multiinput->addInput($send_mail_field);

        $send_mail_field_value = new ilTextInputGUI('Send Mail Field Value', srPHBernDclNotificationsConfig::F_SEND_MAIL_CHECK_FIELD_VALUE);
        $multiinput->addInput($send_mail_field_value);

        $this->addItem($multiinput);

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
            $array[$key] = srPHBernDclNotificationsConfig::getConfigValue($key);
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

            srPHBernDclNotificationsConfig::set($key, $this->getInput($key));
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