<?php

namespace Compago\Database\Blueprint;

class Blueprint extends \Compago\Database\SQL\Alter
{
    protected $current;
    public function __construct($table,$EXISTINGDEF) {
        $this->current = $EXISTINGDEF;
        parent::__construct('ALTER');
        $this->table($table);
    }
    public function __toString()
    {
        return $this->getSql();
    }
    public function getSql()
    {
        if (empty($this->current) || !$this->current->hasColumns()){
            $sql = parent::toString('CREATE');
            return $sql;
        }
        $clo = new self(null,null);
        $clo->data = $this->data;
        if ($this->options){
            $clo->options($this->options()->toArray());
        }
        if ($this->parameters){
            $clo->parameters($this->parameters()->toArray());
        }
        if ($this->partition){
            $clo->partition($this->partition()->toArray());
        }
        //__er($this->current);
        
        
        foreach ($this->columns as $FIELD){
            //comparative alter
            if ($FIELD->attribute_type == 'COLUMN'){
                if ($FIELD->mode == 'DROP'){
                    if ($this->current->hasColumn($FIELD->name)){
                        $clo->dropColumn($FIELD->name);
                    }
                }elseif ($this->current->hasColumn($FIELD->name)){
                    $C = $this->current->getColumn($FIELD->name);
                    if (self::compare_fields($C,$FIELD) == false){
                        //__er ("CURENT <> NEW ==",$C->definition() .' <=> '. $FIELD->definition());
                        $NC = $clo->modifyColumn($FIELD->name,$FIELD->toDefinitionArray());
                        
                        //__er('===NewC =>',$NC, 'CurC=>',$C,'AddC=>',$FIELD);
                        //__er( 'CurC.d=>',$C->getDataType(),'AddC.d=>',$FIELD->getDataType());
                    }
                } else {
                    $clo->addColumn($FIELD->name,$FIELD->toDefinitionArray());
                    //__er('===--AddC=>',$FIELD);
                    //__er('nav++', $clo);
                }
            } elseif ($FIELD->attribute_type == 'INDEX'){
                if ($FIELD->mode == 'DROP'){
                    if ($this->current->hasIndex($FIELD->name)){
                        $clo->dropIndex($FIELD->name);
                    }
                } elseif ($this->current->hasIndex($FIELD->name)){
                    $C = $this->current->getIndex($FIELD->name);
                    if (self::compare_index($C,$FIELD) == false){
                        $clo->dropIndex($FIELD->name);
                        $NI = $clo->addIndex($FIELD->name,$FIELD->toArray());
                        //__er('***NewI=>',$NI, 'CurI=>',$C,'AddI=>',$FIELD);
                    }
                } else {
                    $clo->addIndex($FIELD->name,$FIELD->toArray());
                }
            }
        }
        if (!$clo->hasDefinitions()){
            return '';
        }
        return $clo->toString('ALTER');
    }
    private static function compare_index($F1,$F2){
        if ($F1->key_part != $F2->key_part){
            return false;
        }
        if ($F2->index_type && ($F1->index_type != $F2->index_type)){
            return false;
        }
        if ($F1->non_unique != $F2->non_unique){
            return false;
        }
        
        return true;
    }
    private static function compare_fields($F1,$F2){
        /*if ($F1->generated || $F2->generated){
                __er('GENE', $F1,$F2);
            }
        if ($F1->name== 'weight'){
                __er('W', $F1,$F2);
            }*/
        if ($F1->getDataType() != $F2->getDataType()){
            return false;
        }
        if ($F1->is_nullable != $F2->is_nullable){
            return false;
        }
        if ($F1->default != $F2->default){
            return false;
        }
        if ($F1->auto_increment != $F2->auto_increment){
            return false;
        }
        if ($F1->on_update_current_timestamp != $F2->on_update_current_timestamp){
            return false;
        }
        return true;
    }
    public function migrate($NEWDEF){
        //given SCHEMA DEF
        //parse and add changes to this from NEWDIF
        throw new \Exception('Not implemented');
        
    }
    public function add($options =array()) {
        $options = array_merge(['is_nullable'=>true],$options);
        return parent::add($options);
    }
    public function addColumn($name,$colOpts=[]){
        $colOpts = array_merge(['is_nullable'=>true],$colOpts);
        return parent::addColumn($name,$colOpts);
    }
    public function primary($name){
        return $this->addColumn($name,['is_nullable'=>false,'primary'=>true,'data_type'=>'int']);
    }
    public function increment($name){
        return $this->addColumn($name,['is_nullable'=>false,'primary'=>true,'auto_increment'=>true,'data_type'=>'int']);
    }
    public function integer($name){
        return $this->addColumn($name,['data_type'=>'int']);
    }
    public function bool($name){
        return $this->addColumn($name,['data_type'=>'tinyint']);
    }
    public function tinyint($name){
        return $this->addColumn($name,['data_type'=>'tinyint']);
    }
    public function smallint($name){
        return $this->addColumn($name,['data_type'=>'smallint']);
    }
    public function bigint($name){
        return $this->addColumn($name,['data_type'=>'bigint']);
    }
    public function date($name){
        return $this->addColumn($name,['data_type'=>'date']);
    }
    public function datetime($name){
        return $this->addColumn($name,['data_type'=>'datetime']);
    }
    public function decimal($name,$precision,$scale){
        return $this->addColumn($name,['data_type'=>'decimal','precision'=>$precision,'scale'=>$scale]);
    }
    public function float($name,$precision,$scale){
        return $this->addColumn($name,['data_type'=>'float','precision'=>$precision,'scale'=>$scale]);
    }
    public function string($name,$length){
        return $this->addColumn($name,['data_type'=>'varchar','length'=>$length]);
    }
    public function text($name){
        return $this->addColumn($name,['data_type'=>'text']);
    }
    public function blob($name){
        return $this->addColumn($name,['data_type'=>'blob']);
    }
    public function timestamp($name){
        return $this->addColumn($name,['data_type'=>'timestamp']);
    }
    public function timestamps(){
        $r = [];
        $r[] = $this->addColumn('created_at',['data_type'=>'timestamp']);
        $r[] = $this->addColumn('updated_at',['data_type'=>'timestamp']);
        return $r;
    }

}