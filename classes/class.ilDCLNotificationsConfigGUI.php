<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilDCLNotificationsConfigGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilDCLNotificationsConfigGUI extends ilPluginConfigGUI
{

    /**
     * @var ilDCLNotificationsPlugin
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
        $this->pl = ilDCLNotificationsPlugin::getInstance();
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

        $form = new srDCLNotificationsConfigFormGUI($this);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save config
     */
    public function save()
    {
        $form = new srDCLNotificationsConfigFormGUI($this);
        if ($form->saveObject()) {
            ilUtil::sendSuccess('Saved Config', true);
            $this->ctrl->redirect($this, 'configure');
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}