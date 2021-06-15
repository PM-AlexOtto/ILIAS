<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilServiceReader
*
* Reads reads service information of services.xml files into db
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/
class ilServiceReader extends ilObjDefReader
{
    /**
    * clear the tables
    */
    public function clearTables()
    {
        $this->db->manipulate("DELETE FROM service_class");
    }


    /**
    * start tag handler
    *
    * @param	ressouce	internal xml_parser_handler
    * @param	string		element tag name
    * @param	array		element attributes
    * @access	private
    */
    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case 'service':
                $this->current_service = $this->name;
                $this->current_component = $this->type . "/" . $this->name;
                $this->db->manipulateF(
                    "INSERT INTO il_component (type, name, id) " .
                    "VALUES (%s,%s,%s)",
                    array("text", "text", "text"),
                    array($this->type, $this->name, $a_attribs["id"])
                );
                
                $this->setComponentId($a_attribs['id']);
                break;
                
            case 'baseclass':
                $this->db->manipulateF(
                    "INSERT INTO service_class (service, class, dir) " .
                    "VALUES (%s,%s,%s)",
                    array("text", "text", "text"),
                    array($this->name, $a_attribs["name"], $a_attribs["dir"])
                );
                break;
        }

        // smeyer: first read outer xml
        parent::handlerBeginTag($a_xml_parser, $a_name, $a_attribs);
    }
}
