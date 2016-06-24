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

<h3>Stats for import <strong>{$import_hash}</strong></h3>
<table>
  <tr><th>Status</th><th>Count</th></tr>
  <tr><td>OK</td><td>{$ok}</td></tr>
  <tr><td>Failed</td><td>{$failed}</td></tr>
</table>

<h3>Errors</h3>
<table>
  <tr>
    <th>Row</th>
    <th>Reference</th>
    <th>API Error</th>
  </tr>
  {foreach from=$errors item=val}
    <tr>
      <td>{$val.row}</td>
      <td>{$val.reference}</td>
      <td>{$val.api_error|truncate:100:'..':true:false}</td>
    </tr>
  {/foreach}
</table>

<p><a href="{crmURL p='civicrm/sepa/import'}" class="button">Start again</a></p>
