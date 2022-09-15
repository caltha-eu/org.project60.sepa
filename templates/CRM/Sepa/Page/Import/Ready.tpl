{*-------------------------------------------------------+
| Project 60 - SEPA direct debit                         |
| Copyright (C) 2013-2014 SYSTOPIA                       |
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
<div class="crm-block crm-form-block import-ready">
    <div class="crm-section">
        <p>Import mandates is ready to start</p>
        <p>Rows: {$rows}</p>
    </div>
    <div class="crm-section crm-submit-buttons">
        <a href="{crmURL p='civicrm/sepa/import-runner'}" class="button">Run import</a>
        <a href="{crmURL p='civicrm/sepa/import-cancel'}" class="button button-cancel">Cancel import</a>
    </div>
</div>
{literal}
<style>
    .crm-block.import-ready .button.button-cancel {
        border: solid 1px #4d4d69;
        color: #363342 !important;
        background: #fff;
    }
    .crm-block.import-ready .button.button-cancel:hover {
        color: #fff !important;
    }
    .crm-block.import-ready .crm-section{
        padding: 10px
    }
</style>
{/literal}