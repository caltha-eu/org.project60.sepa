<?php
/*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2014 TTTP                           |
| Author: X+                                             |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


/**
 * File for the CiviCRM APIv3 sepa_mandate functions
 *
 * @package CiviCRM_SEPA
 *
 */


/**
 * Add an SepaCreditor for a contact
 *
 * Allowed @params array keys are:
 *
 * @example SepaCreditorCreate.php Standard Create Example
 *
 * @return array API result array
 * {@getfields sepa_mandate_create}
 * @access public
 */
function civicrm_api3_sepa_mandate_create($params) {
  _civicrm_api3_sepa_mandate_adddefaultcreditor($params);
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Adjust Metadata for Create action
 * 
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_sepa_mandate_create_spec(&$params) {
//  $params['reference']['api.required'] = 1; generated by the BAO
  $params['entity_id']['api.required'] = 1;
  $params['entity_table']['api.required'] = 1;
  $params['type']['api.required'] = 1;
  $params['is_enabled']['api.default'] = false;
  $params['status']['api.default'] = "INIT";

}


/**
 * Creates a mandate object along with its "contract", 
 * i.e. the payment details as recorded in an
 * associated contribution or recurring contribution 
 * 
 * @author endres -at- systopia.de 
 *
 * @return array API result array
 */
function civicrm_api3_sepa_mandate_createfull($params) {
    // create the "contract" first: a contribution
    // TODO: sanity checks
    _civicrm_api3_sepa_mandate_adddefaultcreditor($params);
    $create_contribution = $params; // copy array
    $create_contribution['version'] = 3;
    if (isset($create_contribution['contribution_contact_id'])) {
    	// in case someone wants another contact for the contribution than for the mandate...
    	$create_contribution['contact_id'] = $create_contribution['contribution_contact_id'];
    }
	if (empty($create_contribution['currency'])) 
		$create_contribution['currency'] = 'EUR'; // set default currency
	if (empty($create_contribution['contribution_status_id'])) 
		$create_contribution['contribution_status_id'] = (int) CRM_Core_OptionGroup::getValue('contribution_status', 'Pending', 'name');

    if ($params['type']=='RCUR') {
    	$contribution_entity = 'ContributionRecur';
	    $contribution_table  = 'civicrm_contribution_recur';
      	$create_contribution['payment_instrument_id'] = 
      		(int) CRM_Core_OptionGroup::getValue('payment_instrument', 'RCUR', 'name');
      	if (empty($create_contribution['status'])) 
      		$create_contribution['status'] = 'FRST'; // set default status
      	if (empty($create_contribution['is_pay_later'])) 
      		$create_contribution['is_pay_later'] = 1; // set default pay_later

    } elseif ($params['type']=='OOFF') {
	 	$contribution_entity = 'Contribution';
	    $contribution_table  = 'civicrm_contribution';
      	$create_contribution['payment_instrument_id'] = 
      		(int) CRM_Core_OptionGroup::getValue('payment_instrument', 'OOFF', 'name');
      	if (empty($create_contribution['status'])) 
      		$create_contribution['status'] = 'OOFF'; // set default status
      	if (empty($create_contribution['total_amount'])) 
      		$create_contribution['total_amount'] = $create_contribution['amount']; // copy from amount

    } else {
    	return civicrm_api3_create_error('Unknown mandata type: '.$params['type']);
    }

    // create the contribution
    $contribution = civicrm_api($contribution_entity, "create", $create_contribution);
    if (!empty($contribution['is_error'])) {
    	return $contribution;
    }

    // create the mandate object itself
    // TODO: sanity checks
    $create_mandate = $create_contribution; // copy array
    $create_mandate['version'] = 3;
    $create_mandate['entity_table'] = $contribution_table;
    $create_mandate['entity_id'] = $contribution['id'];
    $mandate = civicrm_api("SepaMandate", "create", $create_mandate);
    if (!empty($mandate['is_error'])) {
    	// this didn't work, so we also have to roll back the created contribution
    	$delete = civicrm_api($contribution_entity, "delete", array('id'=>$contribution['id'], 'version'=>3));
    	if (!empty($delete['is_error'])) {
    		error_log("org.project60.sepa: createfull couldn't roll back created contribution: ".$delete['error_message']);
    	}
    }
	return $mandate;
}


/**
 * Deletes an existing Mandate
 *
 * @param  array  $params
 *
 * @return boolean | error  true if successfull, error otherwise
 * {@getfields sepa_mandate_delete}
 * @access public
 */
function civicrm_api3_sepa_mandate_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Retrieve one or more sepa_mandates
 *
 * @param  array input parameters
 *
 *
 * @example SepaCreditorGet.php Standard Get Example
 *
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array api result array
 * {@getfields sepa_mandate_get}
 * @access public
 */
function civicrm_api3_sepa_mandate_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * will add the default creditor_id if no creditor_id is given, and the default creditor is valid
 */
function _civicrm_api3_sepa_mandate_adddefaultcreditor(&$params) {
  if (empty($params['creditor_id'])) {
    $default_creditor = CRM_Sepa_Logic_Settings::defaultCreditor();
    if ($default_creditor != NULL) {
      $params['creditor_id'] = $default_creditor->id;
    }
  }
}


/**
 * this function is used by the FindMandate UI (angular based)
 */
function civicrm_api3_sepa_mandate_getlist($params) {

    $results = civicrm_api3('SepaMandate', 'get', array(
        'sequential' => 1,
        'status' => $params['status'],
    ));

    /** contacts & contributions details */
    $contact_ids = array();
    $contributions_ids = array();
    if ($results['values'] > 0) {
        foreach ($results['values'] as $mandate) {
            $contact_ids[$mandate['contact_id']] = $mandate['contact_id'];
            $contributions_ids[$mandate['entity_table']][$mandate['entity_id']] = $mandate['entity_id'];
        }
    }

    $result_contact = civicrm_api3('Contact', 'get', array(
        'id' => $contact_ids,
        'return' => "id,display_name,email",
    ));

    if ($results['values'] > 0) {
        foreach ($results['values'] as $key => $mandate) {
            $results['values'][$key]['contact'] = $result_contact['values'][$mandate['contact_id']];
        }
    }

    return civicrm_api3_create_success($results['values']);

}
