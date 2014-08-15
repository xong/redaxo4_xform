<?php

/**
 * XForm
 * @author jan.kristinus[at]redaxo[dot]org Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

class rex_xform_manager_table
{

    var $values = array();

    function __construct($values = array())
    {
        if (!is_array($values) || count($values) == 0) {
            return false;
        }
        $this->values = $values;
        return true;
    }


    // -------------------------------------------------------------------------

    static function checkTableName($table)
    {
        preg_match("/([a-z])+([0-9a-z\_])*/", $table, $matches);
        if (count($matches) > 0 && current($matches) == $table) {
            return true;
        }
        return false;
    }


    // -------------------------------------------- xform custom function

    static function xform_checkTableName($label = '', $table = '', $params = '')
    {
        return self::checkTableName($table);
    }

    static function xform_existTableName($l = '', $v = '', $params = '')
    {
        global $REX;
        $q = 'select * from ' . $REX['TABLE_PREFIX'] . 'xform_table where table_name="' . $v . '" LIMIT 1';
        $c = rex_sql::factory();
        // $c->debugsql = 1;
        $c->setQuery($q);
        if ($c->getRows() > 0) {
            return true;
        }
        return false;
    }


    // -------------------------------------------------------------------------

    static function getMaximumTablePrio()
    {
        global $REX;
        $sql = 'select max(prio) as prio from ' . $REX['TABLE_PREFIX'] . 'xform_table';
        $gf = rex_sql::factory();
        // $gf->debugsql = 1;
        $gf->setQuery($sql);
        return $gf->getValue('prio');
    }

    static function getMaximumPrio($table_name)
    {
        global $REX;
        $sql = 'select max(prio) as prio from ' . $REX['TABLE_PREFIX'] . 'xform_field where table_name="' . $table_name . '"';
        $gf = rex_sql::factory();
        // $gf->debugsql = 1;
        $gf->setQuery($sql);
        return $gf->getValue('prio');

    }

    static function hasId($table_name)
    {
        global $REX;
        // $sql = 'show columns from '.$table_name;
        $sql = 'SELECT COLUMN_NAME, EXTRA, COLUMN_KEY, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . $table_name . '" and COLUMN_NAME="id" and EXTRA = "auto_increment" and COLUMN_KEY="PRI" and TABLE_SCHEMA="' . mysql_real_escape_string($REX['DB']['1']['NAME']) . '"';
        $gf = rex_sql::factory();
        // $gf->debugsql = 1;
        $gf->setQuery($sql);

        if ($gf->getRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    static function getXFormFields($table_name, $filter = array())
    {
        global $REX;
        $add_sql = '';
        foreach ($filter as $k => $v) {
            $add_sql = 'AND `' . mysql_real_escape_string($k) . '`="' . mysql_real_escape_string($v) . '"';
        }

        $sql = 'select * from ' . $REX['TABLE_PREFIX'] . 'xform_field where table_name="' . $table_name . '" ' . $add_sql . ' order by prio';
        $gf = rex_sql::factory();
        // $gf->debugsql = 1;
        $gf->setQuery($sql);
        $ga = $gf->getArray();

        $c = array();
        foreach ($ga as $v) {
            $c[$v['name']] = $v;
        }
        return $c;

    }

    static function getXFormFieldsByType($table_name, $type_id = 'value')
    {
        return self::getXFormFields($table_name, array('type_id' => $type_id));

    }

    static function getFields($table_name)
    {
        global $REX;
        $sql = 'show columns from ' . $table_name;
        $sql = 'SELECT COLUMN_NAME, EXTRA, COLUMN_KEY, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = "' . $table_name . '" and TABLE_SCHEMA="' . mysql_real_escape_string($REX['DB']['1']['NAME']) . '"';

        $gf = rex_sql::factory();
        // $gf->debugsql = 1;
        $gf->setQuery($sql);
        $ga = $gf->getArray();

        $c = array();
        foreach ($ga as $v) {
            $c[$v['COLUMN_NAME']] = $v;
        }
        unset($c['id']);
        return $c;

    }

    static function getMissingFields($table_name)
    {
        $xfields = self::getXFormFieldsByType($table_name);
        $rfields = self::getFields($table_name);

        $c = array();
        foreach ($rfields as $k => $v) {
            if (!array_key_exists($k, $xfields)) {
                $c[$k] = $k;
            }
        }
        return $c;

    }


}
