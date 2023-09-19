<?php

namespace Compago\Model;

abstract class Contract
{
	//const ITEM_CLASS = PropertyBag::class;
	protected static $dir;
	protected static $table;
	protected static $id_field = 'id';
	protected static $timestamps = false;
	protected static $migrated = false;
	protected static $default_order = false;
	public function __construct()
	{
		if (!static::$migrated) {
			if (empty(static::$dir)) {
				throw new \Exception('$dir property of Model must be defined.');
			}
			if (empty(static::$table)) {
				throw new \Exception('$table property of Model must be defined.');
			}
			$fn = static::$dir . DIRECTORY_SEPARATOR . static::$table . '.db.json';
			if (file_exists($fn)) {
				//perform migration
			}
			static::$migrated = true;
		}
	}
	public static function fetch($id)
	{
		if ($id === null){
			if (defined('static::ITEM_CLASS') && class_exists(static::ITEM_CLASS)) {
				$cname = static::ITEM_CLASS;
				return new $cname();
			}
		}
		$id = (int)$id;
		$id_field = static::$id_field;
		$q = db()->select('*', static::$table, "`{$id_field}`=$id");

		if (defined('static::ITEM_CLASS') && class_exists(static::ITEM_CLASS)) {
			$cname = static::ITEM_CLASS;
			if ($ro = $q->fetch_assoc()) {
				return new $cname($ro);
			}
			return new $cname();
		} else {
			if ($ro = $q->fetch_object()) {
				return $ro;
			}
		}
		return null;
	}
	public static function find($filter, $limit = null)
	{
		$id_field = static::$id_field;
		$wh = [];
		if ($filter instanceof \Compago\Database\WhereClause){
			$wh = $filter->toString();
			$filter = [];
		} elseif (isset($filter['where'])){
			if (is_object($filter['where'])){
				$wh[] = (string)$filter['where'];
				unset($filter['where']);
			}
		} elseif (is_array($filter)){
			foreach ($filter as $k => $v) {
				if (is_array($v)){
					$filter[$k] = implode(',',$v);
				}
			}
		}
		
		$filter = db()->escape($filter);
		if (static::$default_order) {
			$ob = static::$default_order;
		} else {
			$ob = '';
		}
		
		foreach ($filter as $k => $v) {
			if ($k ===$id_field  && strpos($v,',')){
				$wh[] = "`{$k}` IN ($v)";
				continue;
			}
			$wh[] = "`{$k}`='$v'";
		}

		$q = db()->select('*', static::$table, $wh, $ob, $limit);
		$result = [];
		if (defined('static::ITEM_CLASS') && class_exists(static::ITEM_CLASS)) {
			$cname = static::ITEM_CLASS;
			while ($ro = $q->fetch_assoc()) {
				$result[] = new $cname($ro);
			}
		} else {
			while ($ro = $q->fetch_object()) {
				$result[] = $ro;
			}
		}
		return $result;
	}

	public static function first($filter = [])
	{
		$result = static::find($filter, 1);
		if (count($result)) {
			return $result[0];
		}
		if (defined('static::ITEM_CLASS') && class_exists(static::ITEM_CLASS)) {
			$cname = static::ITEM_CLASS;
			return new $cname();
		}
		return null;
	}
	private static function getTimestamps()
	{
		$timestamps = static::$timestamps;
		if ($timestamps === true) {
			return ['created_at', 'updated_at'];
		}
		if (is_array($timestamps)) {
			return $timestamps;
		}
		return [];
	}
	public static function create($data)
	{
		$db = db();
		$data = $db->escape($data);
		$keys = [];
		$set = [];
		foreach ($data as $k => $v) {
			$set[] = "`{$k}`='$v'";
			$keys[] = $k;
		}
		$timestamps = array_diff(static::getTimestamps(), $keys, ['updated_at','dt_updated']);
		foreach ($timestamps as $k) {
			$set[] = "`{$k}`=NOW()";
		}
		$db->insert(static::$table, $set);
		return $db->insert_id();
	}
	public static function createOrUpdate($data)
	{
		$db = db();
		$data = $db->escape($data);
		$keys = [];
		$set = [];
		foreach ($data as $k => $v) {
			$set[] = "`{$k}`='$v'";
			$keys[] = $k;
		}
		$timestamps = array_diff(static::getTimestamps(), $keys, ['updated_at','dt_updated']);
		foreach ($timestamps as $k) {
			$set[] = "`{$k}`=NOW()";
		}
		$up = $set;
		$timestamps = array_diff(static::getTimestamps(), $keys, ['created_at','dt_created']);
		foreach ($timestamps as $k) {
			$up[] = "`{$k}`=NOW()";
		}
		$db->insert(static::$table, $set, $up);
		return $db->insert_id();
	}
	public static function update($filter, $data, $limit = null)
	{
		$filter = db()->escape($filter);
		$data = db()->escape($data);
		$timestamps = static::getTimestamps();
		$wh = [];
		$keys = [];
		foreach ($filter as $k => $v) {
			$wh[] = "`{$k}`='$v'";
		}
		$set = [];
		foreach ($data as $k => $v) {
			$set[] = "`{$k}`='$v'";
			$keys[] = $k;
		}
		$timestamps = array_diff($timestamps, $keys, ['created_at','dt_created']);
		foreach ($timestamps as $k) {
			$set[] = "`{$k}`=NOW()";
		}
		db()->update(static::$table, $set, $wh, $limit);
	}
	public static function delete($filter, $limit = null)
	{
		$filter = db()->escape($filter);
		$wh = [];
		foreach ($filter as $k => $v) {
			if ($v === null){
				$wh[] = "`{$k}` IS NULL";
			} elseif (is_array($v)){
				$temp = implode(',',$v);
				$wh[] = "(`{$k}` IN ($temp))";
			} else {
				$wh[] = "`{$k}`='$v'";
			}
		}
		db()->delete(static::$table, $wh, $limit);
	}
}
