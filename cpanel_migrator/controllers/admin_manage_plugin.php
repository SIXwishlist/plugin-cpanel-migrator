<?php
/**
 * Manage the plugin settings from the admin side.
 *
 * @package Blesta
 * @subpackage Blesta.plugins
 * @author CyanDark, Inc <support@cyandark.com>
 * @copyright Copyright (c) 2012-2017 CyanDark, Inc. All Rights Reserved.
 * @link http://www.cyandark.com/ CyanDark
 */
class AdminManagePlugin extends AppController
{
    /**
     * Performs necessary initialization.
     */
    private function init()
    {
        // Require login
        $this->parent->requireLogin();

        // Load language
        Language::loadLang('cpanel_migrator', null, PLUGINDIR . 'cpanel_migrator' . DS . 'language' . DS);

        // Load components
        Loader::loadComponents($this, ['Record']);

        // Load models
        $this->uses(['ModuleManager']);

        // Set company and plugin id
        $this->company_id = Configure::get('Blesta.company_id');
        $this->plugin_id = (isset($this->get[0]) ? $this->get[0] : null);

        // Set the page title and view
        $this->parent->structure->set('page_title', Language::_('CpanelMigrator.manage_plugin', true));
        $this->view->setView(null, 'CpanelMigrator.default');
    }

    /**
     * Returns the view to be rendered when managing this plugin.
     */
    public function index()
    {
        // Initialize section
        $this->init();

        // Set vars array
        $vars = $this->post;

        // Set mapping variable
        $mapping = [];

        // Fetch fields from the package
        if (!empty($this->post)) {
            if (isset($this->post['migration'])) {
                // Check if the cPanel module is already installed, if not, we install it
                if (!$this->ModuleManager->isInstalled('cpanel', $this->company_id)) {
                    $this->ModuleManager->add([
                        'class' => 'cpanel',
                        'company_id' => $this->company_id
                    ]);
                }

                // Execute only if the cPanel Extended module is installed
                if ($this->ModuleManager->isInstalled('cpanelextended', $this->company_id)) {
                    // Get cPanel module
                    $core_module = $this->Record->select()->from('modules')->where('class', '=', 'cpanel')->fetch();

                    // Get cPanel Extended module
                    $extended_module = $this->Record->select()->from('modules')->where('class', '=', 'cpanelextended')->fetch();

                    // Migrate module server groups
                    $module_groups = $this->Record->select()->from('module_groups')->where('module_id', '=', $extended_module->id)->fetchAll();

                    foreach ($module_groups as $group) {
                        $this->Record->insert('module_groups', [
                            'module_id' => $core_module->id,
                            'add_order' => $group->add_order,
                            'name' => $group->name,
                            'force_limits' => $group->force_limits
                        ]);
                        $mapping['module_groups'][$group->id] = $this->Record->lastInsertId();

                        // Update the module row groups
                        $this->Record->where('module_group_id', '=', $group->id)->update('module_row_groups', [
                            'module_group_id' => $mapping['module_groups'][$group->id]
                        ]);
                    }

                    // Migrate module server rows
                    $module_rows = $this->Record->select()->from('module_rows')->where('module_id', '=', $extended_module->id)->fetchAll();

                    foreach ($module_rows as $row) {
                        $this->Record->insert('module_rows', [
                            'module_id' => $core_module->id
                        ]);
                        $mapping['module_rows'][$row->id] = $this->Record->lastInsertId();

                        // Update the module row meta
                        $this->Record->where('module_row_id', '=', $row->id)->update('module_row_meta', [
                            'module_row_id' => $mapping['module_rows'][$row->id]
                        ]);

                        // Delete unused fields
                        $this->Record->from('module_row_meta')->where('module_row_id', '=', $mapping['module_rows'][$row->id])->where('key', '=', 'pagetoimage_key')->delete();
                        $this->Record->from('module_row_meta')->where('module_row_id', '=', $mapping['module_rows'][$row->id])->where('key', '=', 'password')->delete();
                        $this->Record->from('module_row_meta')->where('module_row_id', '=', $mapping['module_rows'][$row->id])->where('key', '=', 'port_number')->delete();
                    }

                    // Migrate module packages
                    $packages = $this->Record->select()->from('packages')->where('module_id', '=', $extended_module->id)->fetchAll();

                    foreach ($packages as $package) {
                        $this->Record->where('id', '=', $package->id)->update('packages', [
                            'module_id' => $core_module->id,
                            'module_row' => isset($package->module_row) ? $mapping['module_rows'][$package->module_row] : null,
                            'module_group' => isset($package->module_group) ? $mapping['module_groups'][$package->module_group] : null,
                        ]);

                        // Delete unused fields
                        $this->Record->from('package_meta')->where('package_id', '=', $package->id)->where('key', '!=', 'package')->where('key', '!=', 'type')->delete();

                        // Map pricing id to package id
                        $package_pricing = $this->Record->select()->from('package_pricing')->where('package_id', '=', $package->id)->fetchAll();

                        foreach ($package_pricing as $pricing) {
                            $mapping['package_pricing'][$pricing->pricing_id] = $pricing->pricing_id;
                        }
                    }

                    // Migrate module services
                    foreach ($mapping['package_pricing'] as $pricing_id) {
                        $services = $this->Record->select()->from('services')->where('pricing_id', '=', $pricing_id)->fetchAll();

                        foreach ($services as $service) {
                            $this->Record->where('id', '=', $service->id)->update('services', [
                                'module_row_id' => $mapping['module_rows'][$service->module_row_id]
                            ]);
                        }
                    }

                    // Uninstall cPanel Extended
                    $this->ModuleManager->delete($extended_module->id);

                    $this->parent->setMessage('success', Language::_('CpanelMigrator.manage_plugin.settings.alert.success_migration', true), false, null, false);
                } else {
                    $this->parent->setMessage('error', Language::_('CpanelMigrator.manage_plugin.settings.alert.module_not_installed', true), false, null, false);
                }
            }
        }

        return $this->partial('admin_manage_plugin', compact('vars'));
    }
}
