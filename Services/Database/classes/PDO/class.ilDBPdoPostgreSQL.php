<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBPdoPostgreSQL
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoPostgreSQL extends ilDBPdo
{
    public const POSTGRE_STD_PORT = 5432;

    protected int $port = self::POSTGRE_STD_PORT;

    protected string $storage_engine;

    protected ilDBPdoManager $manager;

    public function generateDSN() : void
    {
        if ($this->getPort() === 0) {
            $this->setPort(self::POSTGRE_STD_PORT);
        }
        $this->dsn = 'pgsql:host=' . $this->getHost() . ';port=' . $this->getPort() . ';dbname=' . $this->getDbname() . ';user='
                     . $this->getUsername() . ';password=' . $this->getPassword();
    }


    /**
     * @throws \Exception
     */
    public function connect(bool $return_false_on_error = false) : ?bool
    {
        $this->generateDSN();
        try {
            $this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $this->getAttributes());
            $this->initHelpers();
        } catch (Exception $e) {
            $this->error_code = $e->getCode();
            if ($return_false_on_error) {
                return false;
            }
            throw $e;
        }

        return ($this->pdo->errorCode() === PDO::ERR_NONE);
    }


    /**
     * @return bool[]
     */
    protected function getAdditionalAttributes() : array
    {
        return array(
            PDO::ATTR_EMULATE_PREPARES => true,
        );
    }


    public function initHelpers() : void
    {
        $this->manager = new ilDBPdoManagerPostgres($this->pdo, $this);
        $this->reverse = new ilDBPdoReversePostgres($this->pdo, $this);
        $this->field_definition = new ilDBPdoPostgresFieldDefinition($this);
    }


    /**
     * Primary key identifier
     */
    public function getPrimaryKeyIdentifier() : string
    {
        return "pk";
    }


    public function supportsFulltext() : bool
    {
        return false;
    }


    public function supportsTransactions() : bool
    {
        return true;
    }


    public function constraintName(string $a_table, string $a_constraint) : string
    {
        $a_constraint = str_replace($a_table . '_', '', $a_constraint);

        return $a_table . '_' . $a_constraint;
    }


    public function getIndexName($index_name_base) : string//PHP8Review: Missing Typehint (probably string)
    {
        return parent::getIndexName($index_name_base); // TODO: Change the autogenerated stub
    }


    /**
     * @throws \ilDatabaseException
     */
    public function replace(string $table, array $primary_keys, array $other_columns) : int
    {
        $a_columns = array_merge($primary_keys, $other_columns);
        $fields = array();
        $field_values = array();
        $placeholders = array();
        $types = array();
        $values = array();
        $lobs = false;
        $lob = array();
        $val_field = array();
        $a = array();
        $b = array();
        foreach ($a_columns as $k => $col) {
            if ($col[0] === "blob" || $col[0] === "clob") {
                $lobs = true;
            }
        }
        $abpk = array();
        $aboc = array();
        $delwhere = array();
        foreach ($primary_keys as $k => $col) {
            $delwhere[] = $k . " = " . $this->quote($col[1], $col[0]);
        }
        $this->manipulate("DELETE FROM " . $table . " WHERE " . implode(" AND ", $delwhere));
        return $this->insert($table, $a_columns);
    }


    /**
     *@deprecated Use ilAtomQuery instead
     */
    public function lockTables(array $tables) : void
    {
        $locks = array();
        foreach ($tables as $table) {
            if (!isset($table['sequence']) && $table['sequence']) {
                $lock = 'LOCK TABLE ' . $table['name'];

                switch ($table['type']) {
                    case ilDBConstants::LOCK_READ:
                        $lock .= ' IN SHARE MODE ';
                        break;

                    case ilDBConstants::LOCK_WRITE:
                        $lock .= ' IN EXCLUSIVE MODE ';
                        break;
                }

                $locks[] = $lock;
            }
        }

        // @TODO use and store a unique identifier to allow nested lock/unlocks
        $this->beginTransaction();
        foreach ($locks as $lock) {
            $this->query($lock);
        }
    }


    /**
     * @throws \ilDatabaseException
     * @deprecated Use ilAtomQuery instead
     */
    public function unlockTables() : void
    {
        $this->commit();
    }


    public function getStorageEngine() : string
    {
        return '';
    }


    public function setStorageEngine(string $storage_engine) : void
    {
    }

    /**
     * @throws \ilDatabaseException
     */
    public function nextId(string $table_name) : int
    {
        $sequence_name = $table_name . '_seq';
        $query = "SELECT NEXTVAL('$sequence_name')";
        $result = $this->query($query);
        return $result->fetchObject()->nextval;
    }


    public function dropTable(string $table_name, bool $error_if_not_existing = false) : bool
    {
        try {
            $this->pdo->exec("DROP TABLE $table_name");
        } catch (PDOException $PDOException) {
            if ($error_if_not_existing) {
                throw $PDOException;
            }

            return false;
        }

        return true;
    }


    public function quoteIdentifier(string $identifier, bool $check_option = false) : string
    {
        return '"' . $identifier . '"';
    }


    public function tableExists(string $table_name) : bool
    {
        $tables = $this->listTables();
        return is_array($tables) && in_array($table_name, $tables);
    }

    protected function appendLimit(string $query) : string
    {
        if ($this->limit !== null && $this->offset !== null) {
            $query .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
            $this->limit = null;
            $this->offset = null;

            return $query;
        }

        return $query;
    }


    public function tableColumnExists(string $table_name, string $column_name) : bool
    {
        return in_array($column_name, $this->manager->listTableFields($table_name));
    }


    /**
     * @throws \ilDatabaseException
     */
    public function renameTable(string $old_name, string $new_name) : bool
    {
        // check table name
        try {
            $this->checkTableName($new_name);
        } catch (ilDatabaseException $e) {
            return true;
        }

        if ($this->tableExists($new_name)) {
            return true;
        }
        try {
            $this->manager->alterTable($old_name, ["name" => $new_name ], false);
            if ($this->sequenceExists($old_name)) {
                $this->manager->alterTable($this->getSequenceName($old_name), ["name" => $this->getSequenceName($new_name) ], false);
            }
        } catch (Exception $e) {
            return true;
        }

        return true;
    }


    public function createSequence(string $table_name, int $start = 1) : bool
    {
        if (in_array($table_name, $this->manager->listSequences(), true)) {
            return false;
        }
        try {
            parent::createSequence($table_name, $start);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }


    /**
     * @throws \ilDatabaseException
     */
    public function createTable(
        string $table_name,
        array $fields,
        bool $drop_table = false,
        bool $ignore_erros = false
    ) : bool {
        if ($this->tableExists($table_name)) {
            return true;
        }
        try {
            return parent::createTable($table_name, $fields, $drop_table, $ignore_erros); // TODO: Change the autogenerated stub
        } catch (Exception $e) {
            return true;
        }
    }


    public function addPrimaryKey(string $table_name, array $primary_keys) : bool
    {
        $ilDBAnalyzer = new ilDBAnalyzer($this);
        if ($ilDBAnalyzer->getPrimaryKeyInformation($table_name)) {
            return true;
        }
        try {
            return parent::addPrimaryKey($table_name, $primary_keys); // TODO: Change the autogenerated stub
        } catch (Exception $e) {
            return true;
        }
    }


    public function addIndex(string $table_name, array $fields, string $index_name = '', bool $fulltext = false) : bool
    {
        $indices = $this->manager->listTableIndexes($table_name);
        if (in_array($this->constraintName($table_name, $index_name), $indices)) {
            return true;
        }
        try {
            return parent::addIndex($table_name, $fields, $index_name, $fulltext); // TODO: Change the autogenerated stub
        } catch (Exception $e) {
            return true;
        }
    }


    public function addUniqueConstraint(string $table, array $fields, string $name = "con") : bool
    {
        try {
            return parent::addUniqueConstraint($table, $fields, $name); // TODO: Change the autogenerated stub
        } catch (Exception $e) {
            return true;
        }
    }

    public function dropPrimaryKey(string $table_name) : bool
    {
        return $this->manager->dropConstraint($table_name, "pk", true);
    }
    
    public function migrateTableToEngine(string $table_name, string $engine = ilDBConstants::MYSQL_ENGINE_INNODB) : bool
    {
        return false;
    }
    
    public function migrateTableCollation(
        string $table_name,
        string $collation = ilDBConstants::MYSQL_COLLATION_UTF8MB4
    ) : bool {
        return false;
    }
}
