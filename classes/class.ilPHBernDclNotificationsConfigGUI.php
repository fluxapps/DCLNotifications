<?php
require_once ('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/classes/class.ilPHBernDclNotificationsPlugin.php');
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfig.php');
require_once('./Customizing/global/plugins/Services/EventHandling/EventHook/PHBernDclNotifications/Config/class.srPHBernDclNotificationsConfigFormGUI.php');
require_once('./Services/UIComponent/Button/classes/class.ilSubmitButton.php');

/**
 * Class ilPHBernDclNotificationsConfigGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilPHBernDclNotificationsConfigGUI extends ilPluginConfigGUI
{

    /**
     * @var ilPHBernDclNotificationsPlugin
     */
    protected $pl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;


    public function __construct()
    {
        global $ilCtrl, $tpl;
        $this->pl = ilPHBernDclNotificationsPlugin::getInstance();
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
    }


    /**
     * @param $cmd
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case 'configure':
            case 'save':
                $this->$cmd();
                break;
        }
    }

    /**
     * Configure screen
     */
    public function configure()
    {
        global $ilToolbar;

        /** @var $ilToolbar ilToolbarGUI */
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

        $form = new srPHBernDclNotificationsConfigFormGUI($this);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save config
     */
    public function save()
    {
        $form = new srPHBernDclNotificationsConfigFormGUI($this);
        if ($form->saveObject()) {
            ilUtil::sendSuccess('Saved Config', true);
            $this->ctrl->redirect($this, 'configure');
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}