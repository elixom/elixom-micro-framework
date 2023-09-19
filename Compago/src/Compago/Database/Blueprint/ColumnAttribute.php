<?php

namespace Compago\Database\Blueprint;

class ColumnAttribute extends \Compago\Database\Core\Attribute{
    
    public function toString() {
        return $this->definition();
    }
    public function __construct($data =null){
        if(is_array($data)){
            $data = array_change_key_case($data,CASE_LOWER);
            if(isset($data['key'])){
                $this->setKey($data['key']);
                unset($data['key']);
            }
            if(isset($data['extra'])){
                $this->setExtra($data['extra']);
            }
            $this->setColumnOptions($data);
            if(!empty($data['character_octet_length'])){
                if ($data['character_octet_length'] != $data['character_maximum_length']){
                    $this->options['multibyte'] = true;
                }
            }
            if(!empty($data['character_maximum_length'])){
                $this->options['length'] = (int)$data['character_maximum_length'];
            }
        }
    }
    private function setExtra($data){
        $data = strtolower($data);
        if(strpos($data,'auto_increment')!==false){
            $this->options['auto_increment'] = true;
        }
        if(strpos($data,'on update current_timestamp')!==false){
            $this->options['on_update_current_timestamp'] = true;
        }
        if(strpos($data,'virtual generated')!==false){
            $this->options['generated'] = true;
            $this->options['virtual'] = true;
        }
        if(strpos($data,'stored generated')!==false){
            $this->options['generated'] = true;
            $this->options['stored'] = true;
        }
    }
    private function setKey($data){
        $data = strtoupper($data);
        if($data == 'PRI'){
            $this->options['primary'] = true;
        }
    }
    
    
    public function storedAs($expr){
        $this->options['generated'] = true;
        $this->options['stored'] = true;
        $this->options['generation_expression'] = $expr;
        return $this;
    }
    public function virtualAs($expr){
        $this->options['generated'] = true;
        $this->options['virtual'] = true;
        $this->options['generation_expression'] = $expr;
        return $this;
    }
    public function generatedAs($expr){
        $this->options['generated'] = true;
        $this->options['generation_expression'] = $expr;
        return $this;
    }
    public function useCurrent($data=true){
        $this->options['on_update_current_timestamp'] = $data;
        return $this;
    }
    
}
/*Field - The column name.
Type - The column data type.
Collation - The collation for nonbinary string columns, or NULL for other columns. This value is displayed only if you use the FULL keyword.
Null - Column nullability. The value is YES if NULL values can be stored in the column, NO if not.

Key

Whether the column is indexed:
If Key is empty, the column either is not indexed or is indexed only as a secondary column in a multiple-column, nonunique index.
If Key is PRI, the column is a PRIMARY KEY or is one of the columns in a multiple-column PRIMARY KEY.
If Key is UNI, the column is the first column of a UNIQUE index. (A UNIQUE index permits multiple NULL values, but you can tell whether the column permits NULL by checking the Null field.)
If Key is MUL, the column is the first column of a nonunique index in which multiple occurrences of a given value are permitted within the column.

If more than one of the Key values applies to a given column of a table, Key displays the one with the highest priority, in the order PRI, UNI, MUL.

A UNIQUE index may be displayed as PRI if it cannot contain NULL values and there is no PRIMARY KEY in the table. A UNIQUE index may display as MUL if several columns form a composite UNIQUE index; although the combination of the columns is unique, each column can still hold multiple occurrences of a given value.

Default-The default value for the column. This is NULL if the column has an explicit default of NULL, or if the column definition includes no DEFAULT clause.

Extra -Any additional information that is available about a given column. The value is nonempty in these cases:
    auto_increment for columns that have the AUTO_INCREMENT attribute
    on update CURRENT_TIMESTAMP for TIMESTAMP or DATETIME columns that have the ON UPDATE CURRENT_TIMESTAMP attribute
    VIRTUAL GENERATED or STORED GENERATED for generated columns
Privileges- The privileges you have for the column. This value is displayed only if you use the FULL keyword.
Comment - Any comment included in the column definition. This value is displayed only if you use the FULL keyword.
*/
