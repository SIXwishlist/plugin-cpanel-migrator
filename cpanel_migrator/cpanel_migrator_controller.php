<?php
/**
 * Main plugin controller.
 *
 * @package Blesta
 * @subpackage Blesta.plugins
 * @author CyanDark, Inc <support@cyandark.com>
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @link http://www.cyandark.com/ CyanDark
 */
class CpanelMigratorController extends AppController
{
    /**
     * Set the default view to all plugin controllers.
     */
    public function preAction()
    {
        // Set structure view
        $this->structure->setDefaultView(APPDIR);

        // Parent pre-action
        parent::preAction();

        // Load language
        Language::loadLang('cpanel_migrator', null, PLUGINDIR . 'cpanel_migrator' . DS . 'language' . DS);

        // Override default view directory
        $this->view->view = 'default';
        $this->original_view = $this->structure->view;
        $this->structure->view = 'default';
    }
}
