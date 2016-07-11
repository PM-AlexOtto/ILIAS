<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('class.ilDBPdoMySQLInnoDB.php');

/**
 * Class ilDBPdoMySQLInnoDB
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoMySQLGalera extends ilDBPdoMySQLInnoDB implements ilDBInterface {

	/**
	 * @return bool
	 */
	public function supportsTransactions() {
		return true;
	}
}

