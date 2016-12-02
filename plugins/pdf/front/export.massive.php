<?php
/**
 * @version $Id: export.massive.php 433 2016-02-25 19:02:58Z yllen $
 -------------------------------------------------------------------------
 LICENSE

 This file is part of PDF plugin for GLPI.

 PDF is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 PDF is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   pdf
 @authors   Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2016 PDF plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/pdf
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/

include ("../../../inc/includes.php");

Plugin::load('pdf', true);

$type = $_SESSION["plugin_pdf"]["type"];
$item = new $type();

$tab_id = unserialize($_SESSION["plugin_pdf"]["tab_id"]);
unset($_SESSION["plugin_pdf"]["tab_id"]);

$query = "SELECT `tabref`
          FROM `glpi_plugin_pdf_preferences`
          WHERE `users_ID` = '".$_SESSION['glpiID']."'
                AND `itemtype` = '$type'";
$result = $DB->query($query);

$tab = array();

while ($data = $DB->fetch_array($result)) {
   if ($data["tabref"] == 'landscape') {
      $pag = 1;
   } else {
      $tab[]= $data["tabref"];
   }
}
   if (empty($tab)) {
      $tab[] = $type.'$main';
   }

if (isset($PLUGIN_HOOKS['plugin_pdf'][$type])) {

   $itempdf = new $PLUGIN_HOOKS['plugin_pdf'][$type]($item);
   $itempdf->generatePDF($tab_id, $tab, (isset($pag) ? $pag : 0));
} else {
   die("Missing hook");
}