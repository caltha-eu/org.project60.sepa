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

<p>TODO Log view...</p>

<h3>Stats</h3>
<ul>
  {foreach from=$stats item=st}
    <li>{$st.status} : {$st.n}</li>
  {/foreach}
</ul>

<h3>Errors</h3>
<ol>
  {foreach from=$failed item=val}
    <li>{$val.reference} : {$val.api_error|truncate:100:'..':true:false}</li>
  {/foreach}
</ol>

<p><a href="{crmURL p='civicrm/sepa/import'}" class="button">Start again</a></p>
