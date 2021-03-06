<?php
/*
 * @version $Id: profile.class.php 211 2016-05-30 16:39:42Z yllen $
 -------------------------------------------------------------------------
 LICENSE

 This file is part of Archires plugin for GLPI.

 Archires is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Archires is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Archires. If not, see <http://www.gnu.org/licenses/>.

 @package   archires
 @author    Nelly Mahu-Lasson, Xavier Caillaud
 @copyright Copyright (c) 2016 Archires plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/archires
 @since     version 2.2
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginArchiresProfile extends Profile {

   static $rightname = "profile";




   //if profile deleted
   static function purgeProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }


   function getFromDBByProfile($profiles_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `profiles_id` = '" . $profiles_id . "' ";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
      }
      return false;
   }


   static function createFirstAccess($ID) {

      self::addDefaultProfileInfos($ID,array('plugin_archires' => ALLSTANDARDRIGHT), true);
   }


   //profiles modification
   function showForProfile(Profile $prof){


      $canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));

      if ($canedit) {
         echo "<form method='post' action='".$prof->getFormURL()."'>";
      }

      $rights = array(array('itemtype'  => 'PluginArchiresArchires',
                            'label'     => __('Generate graphs', 'archires'),
                            'field'     => 'plugin_archires'));

      $prof->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                      'default_class' => 'tab_bg_2',
                                                       'title'         => __('General')));

      echo "<table class='tab_cadre_fixehov'>";
      $effective_rights = ProfileRight::getProfileRights($prof->getField('id'), array('plugin_archires'));
      echo Html::hidden('id', array('value' => $prof->getField('id')));
      echo "</table>";

      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $prof->getField('id')));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('id')
             && ($item->getField('interface') != 'helpdesk')) {
            return PluginArchiresArchires::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         $prof = new self();
         $ID   = $item->getField('id');
         self::addDefaultProfileInfos($item->getID(),
                                      array('plugin_archires' => 0));
         $prof->showForProfile($item);
      }
      return true;
   }


      static function addDefaultProfileInfos($profiles_id, $rights) {

      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

}
