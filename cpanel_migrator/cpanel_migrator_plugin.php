<?php
/**
 * Migrates all the data from cPanel Extended to cPanel core.
 *
 * @package Blesta
 * @subpackage Blesta.plugins
 * @author CyanDark, Inc <support@cyandark.com>
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @link http://www.cyandark.com/ CyanDark
 */
class CpanelMigratorPlugin extends Plugin
{
    /**
     * @var int The plugin id
     */
    private $pluginid = null;

    /**
     * @var string The plugin version
     */
    private static $version = '1.0.0';

    /**
     * @var array The plugin authors
     */
    private static $authors = [['name' => 'CyanDark, Inc.', 'url' => 'https://cyandark.com']];

    /**
     * Plugin constructor.
     */
    public function __construct()
    {
        // Load language file
        Language::loadLang('cpanel_migrator', null, dirname(__FILE__) . DS . 'language' . DS);
    }

    /**
     * Fetches the plugin name.
     *
     * @return string The plugin name
     */
    public function getName()
    {
        return Language::_('CpanelMigrator.name', true);
    }

    /**
     * Fetches the plugin description.
     *
     * @return string The plugin description
     */
    public function getDescription()
    {
        return Language::_('CpanelMigrator.description', true);
    }

    /**
     * Fetches the plugin version.
     *
     * @return string The plugin version
     */
    public function getVersion()
    {
        return self::$version;
    }

    /**
     * Fetches the plugin authors.
     *
     * @return array The plugin authors
     */
    public function getAuthors()
    {
        return self::$authors;
    }

    /**
     * Performs any necessary bootstraping actions.
     *
     * @param int $plugin_id The ID of the plugin being installed
     */
    public function install($plugin_id)
    {
        // Nothing to do
    }

    /**
     * Performs any necessary cleanup actions.
     *
     * @param int $plugin_id The ID of the plugin being uninstalled
     * @param bool $last_instance True if $plugin_id is the last instance across all companies for this plugin, false otherwise
     */
    public function uninstall($plugin_id, $last_instance)
    {
        // Nothing to do
    }

    /**
     * Returns all actions to be configured for this widget
     * (invoked after install() or upgrade(), overwrites all existing actions).
     *
     * @return array A numerically indexed array containing:
     *  - action The action to register for
     *  - uri The URI to be invoked for the given action
     *  - name The name to represent the action (can be language definition)
     *  - options An array of key/value pair options for the given action
     */
    public function getActions()
    {
        return [];
    }
}
