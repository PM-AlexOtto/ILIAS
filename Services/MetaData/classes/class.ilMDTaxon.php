<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Meta Data class (element taxon)
*
* @package ilias-core
* @version $Id$
*/
include_once 'class.ilMDBase.php';

class ilMDTaxon extends ilMDBase
{
	var $parent_obj = null;

	function ilMDTaxon(&$parent_obj,$a_id = null)
	{
		$this->parent_obj =& $parent_obj;

		parent::ilMDBase($this->parent_obj->getRBACId(),
						 $this->parent_obj->getObjId(),
						 $this->parent_obj->getObjType(),
						 'meta_taxon',
						 $a_id);

		$this->setParentType($this->parent_obj->getMetaType());
		$this->setParentId($this->parent_obj->getMetaId());

		if($a_id)
		{
			$this->read();
		}
	}

	// SET/GET
	function setTaxon($a_taxon)
	{
		$this->taxon = $a_taxon;
	}
	function getTaxon()
	{
		return $this->taxon;
	}
	function setTaxonLanguage(&$lng_obj)
	{
		if(is_object($lng_obj))
		{
			$this->taxon_language = $lng_obj;
		}
	}
	function &getTaxonLanguage()
	{
		return is_object($this->taxon_language) ? $this->taxon_language : false;
	}
	function getTaxonLanguageCode()
	{
		return is_object($this->taxon_language) ? $this->taxon_language->getLanguageCode() : false;
	}
	function setTaxonId($a_taxon_id)
	{
		$this->taxon_id = $a_taxon_id;
	}
	function getTaxonId()
	{
		return $this->taxon_id;
	}
	

	function save()
	{
		if($this->db->autoExecute('il_meta_taxon',
								  $this->__getFields(),
								  DB_AUTOQUERY_INSERT))
		{
			$this->setMetaId($this->db->getLastInsertId());

			return $this->getMetaId();
		}
		return false;
	}

	function update()
	{
		if($this->getMetaId())
		{
			if($this->db->autoExecute('il_meta_taxon',
									  $this->__getFields(),
									  DB_AUTOQUERY_UPDATE,
									  "meta_taxon_id = '".$this->getMetaId()."'"))
			{
				return true;
			}
		}
		return false;
	}

	function delete()
	{
		if($this->getMetaId())
		{
			$query = "DELETE FROM il_meta_taxon ".
				"WHERE meta_taxon_id = '".$this->getMetaId()."'";
			
			$this->db->query($query);
			
			return true;
		}
		return false;
	}
			

	function __getFields()
	{
		return array('rbac_id'	=> $this->getRBACId(),
					 'obj_id'	=> $this->getObjId(),
					 'obj_type'	=> ilUtil::prepareDBString($this->getObjType()),
					 'parent_type' => $this->getParentType(),
					 'parent_id' => $this->getParentId(),
					 'taxon'	=> ilUtil::prepareDBString($this->getTaxon()),
					 'taxon_language' => ilUtil::prepareDBString($this->getTaxonLanguageCode()),
					 'taxon_id'	=> ilUtil::prepareDBString($this->getTaxonId()));
	}

	function read()
	{
		include_once 'Services/MetaData/classes/class.ilMDLanguage.php';

		if($this->getMetaId())
		{
			$query = "SELECT * FROM il_meta_taxon ".
				"WHERE meta_taxon_id = '".$this->getMetaId()."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->setTaxon($row->taxon);
				$this->taxon_language = new ilMDLanguage($row->taxon_language);
				$this->setTaxonId($row->taxon_id);
			}
		}
		return true;
	}
				
	/*
	 * XML Export of all meta data
	 * @param object (xml writer) see class.ilMD2XML.php
	 * 
	 */
	function toXML(&$writer)
	{
		$writer->xmlElement('Taxon',array('Language' => $this->getTaxonLanguageCode(),
										  'Id'		 => $this->getTaxonId()),$this->getTaxon());
	}


	// STATIC
	function _getIds($a_rbac_id,$a_obj_id,$a_parent_id)
	{
		global $ilDB;

		$query = "SELECT meta_taxon_id FROM il_meta_taxon ".
			"WHERE rbac_id = '".$a_rbac_id."' ".
			"AND obj_id = '".$a_obj_id."' ".
			"AND parent_id = '".$a_parent_id."' ORDER BY meta_taxon_id";


		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = $row->meta_taxon_id;
		}
		return $ids ? $ids : array();
	}
}
?>