<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


class MagTest {

    /**
     * insert a new test category
     *
     * @param mixed $magtestid
     * @param mixed $name
     * @param mixed $descriptionformat
     * @param mixed $description
     * @param mixed $result
     * @param mixed $sortorder
     * @param mixed $symbol
     */
    public static function addCategory($magtestid, $magtest_rec) {
        global $DB;

        $lastorder = $DB->get_field('magtest_category', 'MAX(sortorder)', array('magtestid' => $magtestid));
        $magtest_rec->magtestid = $magtestid ;
        $magtest_rec->sortorder = ++$lastorder;

        $newid = $DB->insert_record('magtest_category', $magtest_rec);
        return $newid ;
    }

   /**
    * delete a test category givven the category id;
    *
    * @param mixed $catid
    */
   public static function deleteCategory($catid) {
       global $DB;  
       $DB->delete_records('magtest_category',array('id'=>$catid));
   }

    /**
     * update test category
     *
     * @param mixed $catid
     * @param mixed $name
     * @param mixed $descriptionformat
     * @param mixed $description
     * @param mixed $result
     * @param mixed $sortorder
     * @param mixed $symbol
     */
    public static function updateCategory($catid,$name,$descriptionformat,$description,$result,$sortorder,$symbol) {
        global $DB;

        $magtest_rec = new stdClass();
        $magtest_rec->id =$catid ;
        $magtest_rec->magtestid =$magtestid ;
        $magtest_rec->name = $name ;
        $magtest_rec->description = $description;
        $magtest_rec->descriptionformat = $descriptionformat;
        $magtest_rec->result = $result;
        $magtest_rec->sortorder = $sortorder;
        $magtest_rec->symbol = $symbol;
        
        $newid = $DB->update_record('magtest_category',$magtest_rec);
        return $newid ;
    }

}
