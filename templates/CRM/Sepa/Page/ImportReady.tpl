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

<p>Import mandates is ready to start</p>
<p>Count: {$count}</p>
<p>Params:</p>
<ul>
  {foreach from=$params item=p}
    <li>{$p}</li>
  {/foreach}
</ul>

<a href="{crmURL p='civicrm/sepa/import-runner'}">Run the queue</a>
