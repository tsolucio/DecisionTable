<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'data/CRMEntity.php';
require_once 'data/Tracker.php';

class cbDecisionTable extends CRMEntity {
	public $db;
	public $log;

	public $table_name = 'vtiger_cbdecisiontable';
	public $table_index= 'cbdecisiontableid';
	public $column_fields = array();

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;
	public $HasDirectImageField = false;
	public $moduleIcon = array('library' => 'standard', 'containerClass' => 'slds-icon_container slds-icon-standard-account', 'class' => 'slds-icon', 'icon'=>'decision');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = array('vtiger_cbdecisiontablecf', 'cbdecisiontableid');
	// related_tables variable should define the association (relation) between dependent tables
	// FORMAT: related_tablename => array(related_tablename_column[, base_tablename, base_tablename_column[, related_module]] )
	// Here base_tablename_column should establish relation with related_tablename_column
	// NOTE: If base_tablename and base_tablename_column are not specified, it will default to modules (table_name, related_tablename_column)
	// Uncomment the line below to support custom field columns on related lists
	// public $related_tables = array('vtiger_cbdecisiontablecf' => array('cbdecisiontableid', 'vtiger_cbdecisiontable', 'cbdecisiontableid', 'cbdecisiontable'));

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = array('vtiger_crmentity', 'vtiger_cbdecisiontable', 'vtiger_cbdecisiontablecf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_cbdecisiontable'   => 'cbdecisiontableid',
		'vtiger_cbdecisiontablecf' => 'cbdecisiontableid',
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbdtname'=> array('cbdecisiontable' => 'cbdtname'),
		'cbdtgroup'=> array('cbdecisiontable' => 'cbdtgroup'),
		'sequence'=> array('cbdecisiontable' => 'sequence'),
		'condstr1'=> array('cbdecisiontable' => 'condstr1'),
		'condstr2'=> array('cbdecisiontable' => 'condstr2'),
		'condnum1'=> array('cbdecisiontable' => 'condnum1'),
		'condnum2'=> array('cbdecisiontable' => 'condnum2'),
		'valstr1'=> array('cbdecisiontable' => 'valstr1'),
		'valnum1'=> array('cbdecisiontable' => 'valnum1'),
	);
	public $list_fields_name = array(
		/* Format: Field Label => fieldname */
		'cbdtname'=> 'cbdtname',
		'cbdtgroup'=> 'cbdtgroup',
		'sequence'=> 'sequence',
		'condstr1'=> 'condstr1',
		'condstr2'=> 'condstr2',
		'condnum1'=> 'condnum1',
		'condnum2'=> 'condnum2',
		'valstr1'=> 'valstr1',
		'valnum1'=> 'valnum1',
	);

	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'cbdtname';

	// For Popup listview and UI type support
	public $search_fields = array(
		/* Format: Field Label => array(tablename => columnname) */
		// tablename should not have prefix 'vtiger_'
		'cbdtname'=> array('cbdecisiontable' => 'cbdtname'),
		'cbdtgroup'=> array('cbdecisiontable' => 'cbdtgroup'),
		'sequence'=> array('cbdecisiontable' => 'sequence'),
		'condstr1'=> array('cbdecisiontable' => 'condstr1'),
		'condstr2'=> array('cbdecisiontable' => 'condstr2'),
		'condnum1'=> array('cbdecisiontable' => 'condnum1'),
		'condnum2'=> array('cbdecisiontable' => 'condnum2'),
		'valstr1'=> array('cbdecisiontable' => 'valstr1'),
		'valnum1'=> array('cbdecisiontable' => 'valnum1'),
	);
	public $search_fields_name = array(
		/* Format: Field Label => fieldname */
		'cbdtname'=> 'cbdtname',
		'cbdtgroup'=> 'cbdtgroup',
		'sequence'=> 'sequence',
		'condstr1'=> 'condstr1',
		'condstr2'=> 'condstr2',
		'condnum1'=> 'condnum1',
		'condnum2'=> 'condnum2',
		'valstr1'=> 'valstr1',
		'valnum1'=> 'valnum1',
	);

	// For Popup window record selection
	public $popup_fields = array('cbdtname');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	public $sortby_fields = array();

	// For Alphabetical search
	public $def_basicsearch_col = 'cbdtname';

	// Column value to use on detail view record text display
	public $def_detailview_recname = 'cbdtname';

	// Required Information for enabling Import feature
	public $required_fields = array('cbdtname'=>1);

	// Callback function list during Importing
	public $special_functions = array('set_import_assigned_user');

	public $default_order_by = 'cbdtname';
	public $default_sort_order='ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = array('createdtime', 'modifiedtime', 'cbdtname');

	public function save_module($module) {
		if ($this->HasDirectImageField) {
			$this->insertIntoAttachment($this->id, $module);
		}
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type) {
		if ($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
			$this->setModuleSeqNumber('configure', $modulename, 'DT-', '0000001');
		} elseif ($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} elseif ($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} elseif ($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} elseif ($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} elseif ($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// public function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//public function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }
}
?>
