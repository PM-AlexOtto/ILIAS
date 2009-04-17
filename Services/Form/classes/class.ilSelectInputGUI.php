<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
* This class represents a selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilSelectInputGUI extends ilSubEnabledFormPropertyGUI
{
	protected $options;
	protected $value;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("select");
	}

	/**
	* Set Options.
	*
	* @param	array	$a_options	Options. Array ("value" => "option_text")
	*/
	function setOptions($a_options)
	{
		$this->options = $a_options;
	}

	/**
	* Get Options.
	*
	* @return	array	Options. Array ("value" => "option_text")
	*/
	function getOptions()
	{
		return $this->options;
	}

	/**
	* Set Value.
	*
	* @param	string	$a_value	Value
	*/
	function setValue($a_value)
	{
		$this->value = $a_value;
	}

	/**
	* Get Value.
	*
	* @return	string	Value
	*/
	function getValue()
	{
		return $this->value;
	}
	
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	function setValueByArray($a_values)
	{
		$this->setValue($a_values[$this->getPostVar()]);
	}

	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	function checkInput()
	{
		global $lng;
		
		$_POST[$this->getPostVar()] = 
			ilUtil::stripSlashes($_POST[$this->getPostVar()]);
		if ($this->getRequired() && trim($_POST[$this->getPostVar()]) == "")
		{
			$this->setAlert($lng->txt("msg_input_is_required"));

			return false;
		}
		return $this->checkSubItemsInput();
	}

	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	function insert(&$a_tpl)
	{
		// determin value to select. Due to accessibility reasons we
		// should always select a value (per default the first one)
		$first = true;
		foreach($this->getOptions() as $option_value => $option_text)
		{
			if ($first)
			{
				$sel_value = $option_value;
			}
			$first = false;
			if ($option_value == $this->getValue())
			{
				$sel_value = $option_value;
			}
		}
		foreach($this->getOptions() as $option_value => $option_text)
		{
			$a_tpl->setCurrentBlock("prop_select_option");
			$a_tpl->setVariable("VAL_SELECT_OPTION", $option_value);
			if ($option_value == $sel_value)
			{
				$a_tpl->setVariable("CHK_SEL_OPTION",
					'selected="selected"');
			}
			$a_tpl->setVariable("TXT_SELECT_OPTION", $option_text);
			$a_tpl->parseCurrentBlock();
		}
		$a_tpl->setCurrentBlock("prop_select");
		$a_tpl->setVariable("POST_VAR", $this->getPostVar());
		if ($this->getDisabled())
		{
			$a_tpl->setVariable("DISABLED",
				" disabled=\"disabled\"");
		}
		$a_tpl->parseCurrentBlock();
	}

}
