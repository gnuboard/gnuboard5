<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\SearchParameters;
use Exception;


class BoardService
{
    public array $board;
    public string $write_table;

    public function __construct(array $board)
    {
        global $g5;

        $this->board = $board;
        $this->write_table = $g5['write_prefix'] . $board['bo_table'];
    }

    /**
     * 게시판 글 총 레코드 수 조회
     */
    public function fetchTotalWritesRecords(array $query_params)
    {
        $where_search = $this->getWhereBySearch($query_params, $search_values);

        $query = "SELECT count(*) FROM {$this->write_table} WHERE {$where_search}";

        $stmt = Db::getInstance()->run($query, $search_values);
        return $stmt->fetchColumn();
    }

    /**
     * 게시판 글 목록 조회
     */
    public function fetchWrites()
    {

    }

    public function getWhereBySearch(array $query_params, &$params = [])
    {
        $query_params = new SearchParameters($query_params);

        $category = $query_params->sca;
        $keyword = $query_params->stx;
        $field_string = $query_params->sfl;
        $where_operator = $query_params->sod;

        // Initialize query parts array
        $query_parts = [];

        // Add category condition if provided
        if ($category) {
            $query_parts[] = "ca_name = :ca_name";
            $params[':ca_name'] = $category;
        }

        // Return early if search text is empty and not '0'
        if (empty($keyword) && $keyword !== '0') {
            return $category ? implode(' AND ', $query_parts) : '0';
        }

        // Split search text and fields
        $terms = explode(" ", $keyword);
        $tmp = explode(",", $field_string);
        $fields = array_map('trim', explode("||", $tmp[0]));
        $is_write = isset($tmp[1]) ? trim($tmp[1]) : "";

        $search_clauses = [];

        foreach ($terms as $i => $term) {
            insert_popular($fields, $term);

            $field_clauses = [];
            foreach ($fields as $field) {
                // SQL Injection prevention by whitelisting
                $field = preg_match("/^[\w\,\|]+$/", $field) ? strtolower($field) : "wr_subject";
                $param_key = ":{$field}_{$i}";
                switch ($field) {
                    case 'mb_id':
                    case 'wr_name':
                        $field_clauses[] = "$field = $param_key";
                        $params[$param_key] = $term;
                        break;
                    case 'wr_hit':
                    case 'wr_good':
                    case 'wr_nogood':
                        $field_clauses[] = "$field >= $param_key";
                        $params[$param_key] = $term;
                        break;
                    case 'wr_num':
                        $field_clauses[] = "$field = $param_key";
                        $params[$param_key] = -1 * $term;
                        break;
                    case 'wr_ip':
                    case 'wr_password':
                        $field_clauses[] = "1=0";
                        break;
                    default:
                        if (preg_match("/[a-zA-Z]/", $term)) {
                            $field_clauses[] = "INSTR(LOWER($field), LOWER($param_key))";
                        } else {
                            $field_clauses[] = "INSTR($field, $param_key)";
                        }
                        $params[$param_key] = $term;
                        break;
                }
            }
            $search_clauses[] = '(' . implode(' OR ', $field_clauses) . ')';
        }

        $query_parts[] = '(' . implode(" $where_operator ", $search_clauses) . ')';

        if ($is_write === '1') {
            $query_parts[] = "wr_is_comment = '0'";
        } elseif ($is_write === '0') {
            $query_parts[] = "wr_is_comment = '1'";
        }

        return implode(' AND ', $query_parts);
    }
}