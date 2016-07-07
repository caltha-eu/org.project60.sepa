{*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2015 SYSTOPIA                       |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}


{if $bank_account_number} {* only for SEPA PPs *}

<div class="crm-section sepa-section no-label">
  <br/>
  <div class="header-dark">{ts domain="org.project60.sepa"}Direct Debit Payment{/ts}</div>
  <table class="sepa-confirm-text-account-details display" id="sepa-confirm-text-account-details">
    <tr id="sepa-thankyou-amount">
      <td>{ts domain="org.project60.sepa"}Amount{/ts}</td>
      <td class="content">{$amount|crmMoney:$currencyID}</td>
    </tr>
    <tr id="sepa-thankyou-reference">
      <td>{ts domain="org.project60.sepa"}Mandate Reference{/ts}</td>
      <td class="content">{$mandate_reference}</td>
    </tr>
    <tr id="sepa-thankyou-creditor">
      <td>{ts domain="org.project60.sepa"}Creditor ID{/ts}</td>
      <td class="content">{$creditor_id}</td>
    </tr>
    <tr id="sepa-thankyou-iban">
      <td>{ts domain="org.project60.sepa"}IBAN{/ts}</td>
      <td class="content">{$bank_account_number}</td>
    </tr>
    <tr id="sepa-thankyou-bic">
      <td>{ts domain="org.project60.sepa"}BIC{/ts}</td>
      <td class="content">{$bank_identification_number}</td>
    </tr>
    {if $is_recur}
      <tr id="sepa-thankyou-collectionday">
        <td>{ts domain="org.project60.sepa"}Collection Day{/ts}</td>
        <td class="content">{$collection_day}.</td>
      </tr>
      <tr id="sepa-thankyou-frequency">
        <td>{ts domain="org.project60.sepa"}Collection Frequency{/ts}</td>
        <td class="content">{$frequency_words}</td>
      </tr>
      <tr id="sepa-thankyou-date">
        <td>{ts domain="org.project60.sepa"}First Collection Date{/ts}</td>
        <td class="content">{$collection_date|crmDate}</td>
      </tr>
    {else}
      <tr id="sepa-thankyou-date">
        <td>{ts domain="org.project60.sepa"}Earliest Collection Date{/ts}</td>
        <td class="content">{$collection_date|crmDate}</td>
      </tr>
    {/if}
  </table>
  <br/>
</div>
{/if}

<script type="text/javascript">
// hide credit card info
{if $bank_account_number} {* only for SEPA PPs *}
cj(".credit_card-group").html("");
cj("div.billing_name_address-group").html("");
{/if}

// modify amount display group
cj("div.event_fees-group").append(cj("div.sepa-section"));

// remove "print" button - this doesn't work here
cj("#printer-friendly").hide();

</script>
