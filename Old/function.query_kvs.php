<?php

/**
 * Smarty {query_kvs} function plugin
 *
 * Type:     function<br>
 * Name:     query_kvs<br>
 * Purpose:  query database and assign result to the specified variable<br>
 * @author Kernel Team
 * @param array
 * @param Smarty
 * @return array|int
 */
function smarty_function_query_kvs($params, &$smarty)
{
	global $config, $database_tables, $database_selectors;

	if (!isset($params['table']) || $params['table'] == '')
	{
		$smarty->trigger_error("query_list: missing 'table' parameter");
		return;
	}

	include_once ("$config[project_path]/admin/include/setup_db.php");
	include_once ("$config[project_path]/admin/include/functions_base.php");
	include_once ("$config[project_path]/admin/include/functions.php");
	include_once ("$config[project_path]/admin/include/placeholder.php");
	include_once ("$config[project_path]/admin/include/database_tables.php");
	include_once ("$config[project_path]/admin/include/database_selectors.php");

	$table = trim($params['table']);
	$limit = intval($params['limit']);
	$select = trim($params['select']);
	$sort_by = trim($params['sort_by']);

	$where = array();
	$wheregt = array();
	$wherelt = array();
	foreach ($params as $k => $v)
	{
		if (strpos($k, 'where_') === 0)
		{
			$where[substr($k, 6)] = mysql_real_escape_string($v);
		}
		if (strpos($k, 'wheregt_') === 0)
		{
			$wheregt[substr($k, 8)] = mysql_real_escape_string($v);
		}
		if (strpos($k, 'wherelt_') === 0)
		{
			$wherelt[substr($k, 8)] = mysql_real_escape_string($v);
		}
	}

	if (!in_array($table, $database_tables))
	{
		$smarty->trigger_error("query_list: 'table' parameter is out of supported tables");
		return;
	}

	$selector = "*";
	$where_default = "1=1";
	switch ($table)
	{
		case "$config[tables_prefix]videos":
			$selector = $database_selectors['videos'];
			$where_default = $database_selectors['where_videos'];
			break;
		case "$config[tables_prefix]albums":
			$selector = $database_selectors['albums'];
			$where_default = $database_selectors['where_albums'];
			break;
		case "$config[tables_prefix]posts":
			$selector = $database_selectors['posts'];
			$where_default = $database_selectors['where_posts'];
			break;
		case "$config[tables_prefix]playlists":
			$selector = $database_selectors['playlists'];
			$where_default = $database_selectors['where_playlists'];
			break;
		case "$config[tables_prefix]tags":
			$selector = $database_selectors['tags'];
			break;
		case "$config[tables_prefix]categories":
			$selector = $database_selectors['categories'];
			break;
		case "$config[tables_prefix]categories_groups":
			$selector = $database_selectors['categories_groups'];
			break;
		case "$config[tables_prefix]models":
			$selector = $database_selectors['models'];
			break;
		case "$config[tables_prefix]content_sources":
			$selector = $database_selectors['content_sources'];
			break;
		case "$config[tables_prefix]content_sources_groups":
			$selector = $database_selectors['content_sources_groups'];
			break;
		case "$config[tables_prefix]dvds":
			$selector = $database_selectors['dvds'];
			break;
		case "$config[tables_prefix]dvds_groups":
			$selector = $database_selectors['dvds_groups'];
			break;
	}
	if ($params['default_filtering'] == 'false')
	{
		$where_default = "1=1";
	}

	if ($select == "count")
	{
		$selector = "count(*)";
	}

	$query = "SELECT $selector FROM $table WHERE $where_default";
	foreach ($where as $k => $v)
	{
		$query .= " AND $k='$v'";
	}
	foreach ($wheregt as $k => $v)
	{
		$query .= " AND $k>='$v'";
	}
	foreach ($wherelt as $k => $v)
	{
		$query .= " AND $k<='$v'";
	}

	if ($sort_by)
	{
		$query .= " ORDER BY $sort_by";
	}

	if ($select == "single")
	{
		$query .= " LIMIT 1";
	} elseif ($limit > 0)
	{
		$query .= " LIMIT $limit";
	}

	if ($select == "single")
	{
		$data = mr2array_single(sql($query));
	} elseif ($select == "count")
	{
		$data = mr2array_list(sql($query));
		$data = intval($data[0]);
	} else
	{
		$data = mr2array(sql($query));
	}

	if (!empty($params['assign']))
	{
		$smarty->assign($params['assign'], $data);
	} else
	{
		return $data;
	}
}