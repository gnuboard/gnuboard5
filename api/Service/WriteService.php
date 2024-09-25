<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\FileResponse;
use API\v1\Model\Response\Write\Thumbnail;
use Exception;

/**
 * 게시물과 댓글관련
 */
class WriteService
{
    public array $board;
    public string $table;

    private PopularSearch $popular_service;
    private MemberImageService $image_service;
    private BoardFileService $file_service;
    private UrlService $url_service;

    public function __construct(
        PopularSearch $popular_service,
        MemberImageService $image_service,
        BoardFileService $file_service,
        UrlService $url_service
    ) {
        $this->file_service = $file_service;
        $this->image_service = $image_service;
        $this->popular_service = $popular_service;
        $this->url_service = $url_service;
    }

    /**
     * 1 개의 게시물에 등록된 썸네일 추출
     *
     * 첨부파일과 본문이미지 둘다 있으면 첨부파일 우선
     * @param array $write
     * @param int $width 썸네일 가로 사이즈
     * @param int $hight 썸네일 세로 사이즈 없으면 가로 사이즈에 맞춤
     * @return array ['src' => '썸네일 경로', 'ori' => '원본 이미지 경로', 'alt' => '이미지 설명'] 실패시 빈 배열
     */
    public function getBoardThumbnail(array $write, $width, $hight = 0)
    {
        $attach_images = $this->file_service->getFilesByType($write['wr_id'], 'image');
        if (isset($attach_images[0]['bf_file'])) {
            //source 경로 추출
            $source_path = G5_DATA_PATH . DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR . $this->board['bo_table'];
            $target_path = $source_path; //동일
            $file_name = basename($attach_images[0]['bf_file']);
            $thumb_name = ThumbnailService::createThumbnail($file_name, $source_path, $target_path, $width, $hight);
            //src - url
            //ori - 원본
            //alt - 설명
            return [
                'src' => $thumb_name ? G5_DATA_URL . '/file/' . $this->board['bo_table'] . '/' . $thumb_name : '',
                'ori' => G5_DATA_URL . '/file/' . $this->board['bo_table'] . '/' . $attach_images[0]->bf_file,
                'alt' => $attach_images[0]['bf_content'] === '' ? $attach_images[0]['bf_source'] : $attach_images[0]['bf_content']
            ];
        }

        $image_tag = ThumbnailService::getFirstImageTag($write['wr_content']);
        //src 추출
        preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $image_tag, $matches);
        $filefullpath = $matches[1] ?? '';
        if (!$filefullpath) {
            return [];
        }

        //alt 추출
        preg_match('/alt="([^"]+)"/', $image_tag, $alt);
        $alt = $alt[1] ?? '';
        $ym_dir = date('Ym');
        $save_path = G5_DATA_PATH . DIRECTORY_SEPARATOR . G5_EDITOR_DIR . DIRECTORY_SEPARATOR . $ym_dir;
        $filename = basename($filefullpath);
        $source_path = \dirname(\str_replace(G5_DATA_URL, G5_DATA_PATH, $filefullpath));
        $thumb_name = ThumbnailService::createThumbnail($filename, $source_path, $save_path, $width, $hight);
        return [
            'src' => $thumb_name ? G5_DATA_URL . '/' . G5_EDITOR_DIR . '/' . $ym_dir . '/' . $thumb_name : '',
            'ori' => $filefullpath,
            'alt' => $alt
        ];
    }

    public function getNotice()
    {
        $notices = $this->fetchNoticeWrites();
        return $this->processWrites($this->board, $notices);
    }

    /**
     * 게시글 목록 가져오기
     * @param array $board
     * @param $search_params
     * @param $page_params
     * @return array
     */
    public function getWrites(array $board, $search_params, $page_params)
    {
        $data = $this->fetchWrites($search_params, $page_params);
        return $this->processWrites($board, $data);
    }

    /**
     * @param array $board 게시판 정보
     * @param array $input_data 게시글, 게시글 목록
     * @return array
     */
    private function processWrites($board, $input_data): array
    {
        $use_show_content = (int)$board['bo_use_list_content'] === 1;
        $use_show_file = (int)$board['bo_use_list_file'] === 1;
        $result = [];
        foreach ($input_data as $write) {
            $write['mb_icon_path'] = $this->image_service->getMemberImagePath($write['mb_id'], 'icon');
            $write['mb_image_path'] = $this->image_service->getMemberImagePath($write['mb_id'], 'image');
            $write['wr_email'] = EncryptionService::encrypt($write['wr_email']);
            $write['wr_ip'] = preg_replace('/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/', G5_IP_DISPLAY, $write['wr_ip']);
            $write['wr_password'] = '';

            // 게시판설정에서 내용보기 안할 때
            if (!$use_show_content) {
                $write['wr_content'] = '';
            }

            // 비밀글
            if (strpos($write['wr_option'], 'secret') !== false) {
                $empty_write = array_map(function () {
                    return '';
                }, $write);
                $write = array_merge($empty_write, [
                    'wr_id' => $write['wr_id'],
                    'wr_num' => $write['wr_num'],
                    'wr_parent' => $write['wr_parent'],
                    'wr_reply' => $write['wr_reply'],
                    'wr_option' => $write['wr_option'],
                    'ca_name' => $write['ca_name'],
                    'wr_subject' => $write['wr_subject'],
                    'wr_content' => $write['wr_content'] !== '' ? '비밀글입니다' : '',
                    'mb_id' => $write['mb_id'],
                    'wr_hit' => $write['wr_hit'],
                    'wr_datetime' => $write['wr_datetime'],
                ]);
            } else {
                // 썸네일
                $thumb = $this->getBoardThumbnail($write, $board['bo_gallery_width'], $board['bo_gallery_height']);
                $write['thumbnail'] = new Thumbnail($thumb);

                // 파일 보기
                if ($use_show_file) {
                    $write['images'] = (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'image')))->files ?? [];
                    $write['normal_files'] = (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'file')))->files ?? [];
                }
            }

            $result[] = $write;
        }

        return $result;
    }

    /**
     * 공지 게시글 목록 조회
     * @return array
     */
    public function fetchNoticeWrites(): array
    {
        // 공지 게시글 아이디를 배열로 변환
        $notice_string = trim($this->board['bo_notice']);
        if (empty($notice_string)) {
            return [];
        }

        $notice_ids = array_filter(array_map('trim', explode(',', $notice_string)));

        if (empty($notice_ids)) {
            return [];
        }

        // IN 조건을 사용하여 쿼리 작성
        $placeholders = Db::makeWhereInPlaceHolder($notice_ids);
        $query = "SELECT * FROM {$this->table} WHERE wr_id IN ($placeholders) AND wr_option NOT LIKE '%secret%'";

        return Db::getInstance()->run($query, $notice_ids)->fetchAll();
    }

    /**
     * 게시글 목록 조회
     * @param array $search_params 검색조건
     * @param array $page_params 페이징 정보
     * @return array|false
     */
    public function fetchWrites(array $search_params, array $page_params)
    {
        // 검색 조건 설정
        $search_values = [];
        $sql_where = $this->getWhereBySearch($search_params, $search_values);
        $sql_where .= $this->getWhereSearchPart($search_params, $search_values);

        // 정렬 설정
        list($sst, $sod) = $this->getSortOrder($search_params);
        $sql_order = $sst ? " ORDER BY {$sst} {$sod} " : '';

        $query = "SELECT * FROM {$this->table} WHERE {$sql_where} {$sql_order} LIMIT :offset, :per_page";
        $search_values['offset'] = $page_params['offset'];
        $search_values['per_page'] = $page_params['per_page'];

        return Db::getInstance()->run($query, $search_values)->fetchAll();
    }


    /**
     * 게시글 목록 조회시 총 게시글 수 조회
     * @param array $search_params 검색조건
     * @return int
     */
    public function fetchTotalCount(array $search_params): int
    {
        // 검색 조건 설정
        $search_values = [];
        $sql_where = $this->getWhereBySearch($search_params, $search_values);
        $sql_where .= $this->getWhereSearchPart($search_params, $search_values);

        $query = "SELECT COUNT(*) FROM {$this->table} WHERE {$sql_where}";

        $stmt = Db::getInstance()->run($query, $search_values);
        return $stmt->fetchColumn() ?: 0;
    }

    /**
     * 게시글 정보 조회
     * @param int $wr_id 게시글 아이디
     * @return array|false
     */
    public function fetchWrite(int $wr_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE wr_id = :wr_id";
        return Db::getInstance()->run($query, ['wr_id' => $wr_id])->fetch();
    }

    /**
     * 게시글의 원글/답글/댓글 조회
     * @param int $wr_id 게시글 아이디
     * @return array|false
     */
    public function fetchWritesAndComments(int $wr_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE wr_parent = :wr_id order by wr_id";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $wr_id]);

        return $stmt->fetchAll();
    }

    /**
     * 부모 게시글 정보 조회
     * @param int $wr_num 게시글 번호
     * @return array|false
     */
    public function fetchParentWriteByNumber(int $wr_num)
    {
        $query = "SELECT * FROM {$this->table}
                    WHERE wr_num = :wr_num
                    AND wr_reply = ''
                    AND wr_is_comment = 0
                    LIMIT 1";
        $stmt = Db::getInstance()->run($query, ['wr_num' => $wr_num]);

        return $stmt->fetch();
    }

    /**
     * 게시글의 답글 조회
     * @param array $write 게시글 정보
     * @return array|false
     */
    public function fetchReplyByWrite(array $write)
    {
        $query = "SELECT * FROM {$this->table}
                    WHERE wr_reply LIKE :wr_reply
                    AND wr_id <> :wr_id
                    AND wr_num = :wr_num
                    AND wr_is_comment = 0";

        $stmt = Db::getInstance()->run($query, [
            'wr_reply' => $write['wr_reply'] . '%',
            'wr_id' => $write['wr_id'],
            'wr_num' => $write['wr_num'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 댓글의 대댓글 조회
     * @param array $comment 댓글 정보
     * @return array|false
     */
    public function fetchReplyByComment(array $comment)
    {
        $query = "SELECT * FROM {$this->table}
                    WHERE wr_comment_reply LIKE :wr_comment_reply
                    AND wr_id <> :wr_id
                    AND wr_parent = :wr_parent
                    AND wr_comment = :wr_comment
                    AND wr_is_comment = 1";
        $stmt = Db::getInstance()->run($query, [
            'wr_comment_reply' => $comment['wr_comment_reply'] . '%',
            'wr_id' => $comment['wr_id'],
            'wr_parent' => $comment['wr_parent'],
            'wr_comment' => $comment['wr_comment'],
        ]);

        return $stmt->fetchAll();
    }

    /**
     * 게시글의 댓글 목록 조회
     * @param array $write 게시글 정보
     * @return array|false
     */
    public function fetchCommentsByWrite(array $write)
    {
        $query = "SELECT * FROM {$this->table} WHERE wr_parent = :wr_id AND wr_is_comment = 1";
        $stmt = Db::getInstance()->run($query, ['wr_id' => $write['wr_id']]);

        return $stmt->fetchAll();
    }

    /**
     * 이전 게시글 정보 조회
     * @param array $write 게시글 정보
     * @param array $search_params 검색조건
     * @return array|false
     */
    public function fetchPrevWrite(array $write, array $search_params = [])
    {
        // 검색조건 설정
        $search_where = $this->getWhereBySearch($search_params, $search_values);

        $where = "AND {$search_where} AND wr_num = :wr_num AND wr_reply < :wr_reply";
        $order_by = 'ORDER BY wr_num desc, wr_reply DESC';
        $values = [
            'wr_num' => $write['wr_num'],
            'wr_reply' => $write['wr_reply'] ?? '',
        ];

        $prev = $this->fetchNeighborWrite($where, $order_by, $values);

        if (!$prev) {
            $where = "AND {$search_where} AND wr_num < :wr_num";
            unset($values['wr_reply']);
            $prev = $this->fetchNeighborWrite($where, $order_by, $values);
        }

        return $prev;
    }

    /**
     * 다음 게시글 정보 조회
     * @param array $write 게시글 정보
     * @param array $search_params 검색조건
     * @return array|false
     */
    public function fetchNextWrite(array $write, array $search_params = [])
    {
        // 검색조건 설정
        $search_where = $this->getWhereBySearch($search_params, $search_values);

        $where = "AND {$search_where} AND wr_num = :wr_num AND wr_reply > :wr_reply";
        $order_by = 'ORDER BY wr_num, wr_reply';
        $values = [
            'wr_num' => $write['wr_num'],
            'wr_reply' => $write['wr_reply'] ?? '',
        ];
        $next = $this->fetchNeighborWrite($where, $order_by, $values);

        if (!$next) {
            $where = "AND {$search_where} AND wr_num > :wr_num";
            unset($values['wr_reply']);
            $next = $this->fetchNeighborWrite($where, $order_by, $values);
        }

        return $next;
    }

    /**
     * 이웃 게시글 조회
     */
    protected function fetchNeighborWrite(string $where, string $order_by, array $values)
    {
        $query = "SELECT wr_id, wr_subject, wr_datetime
                    FROM {$this->table}
                    WHERE wr_is_comment = 0
                    {$where}
                    {$order_by}
                    LIMIT 1";

        $stmt = Db::getInstance()->run($query, $values);

        return $stmt->fetch();
    }

    /**
     * 가장 최근 작성된 댓글의 날짜 조회
     * @param array $write 게시글 정보
     * @return array|false
     */
    public function fetchWriteCommentLast(array $write)
    {
        $query = "SELECT MAX(wr_datetime) as wr_last FROM {$this->table} WHERE wr_parent = :wr_parent";
        $stmt = Db::getInstance()->run($query, ['wr_parent' => $write['wr_parent']]);
        return $stmt->fetch();
    }

    /**
     * 게시글 목록의 최소 wr_num 조회
     * @return int
     */
    public function fetchMinimumWriteNumber(): int
    {
        $query = "SELECT MIN(wr_num) AS min_wr_num FROM {$this->table}";
        $stmt = Db::getInstance()->run($query);
        $row = $stmt->fetch();
        return $row['min_wr_num'] ?? 0;
    }

    /**
     * 게시글의 마지막 wr_reply 조회
     * @param array $write 게시글 정보
     * @return string
     */
    public function fetchLastReply(array $write): string
    {
        $reply_len = strlen($write['wr_reply']) + 1;
        $order_func = $this->board['bo_reply_order'] ? 'MAX' : 'MIN';
        $values = [
            'reply_len1' => $reply_len,
            'reply_len2' => $reply_len,
            'wr_num' => $write['wr_num'],
        ];

        $query = "SELECT {$order_func} (SUBSTRING(wr_reply, :reply_len1, 1)) as reply 
                FROM {$this->table} 
                WHERE wr_num = :wr_num 
                AND SUBSTRING(wr_reply, :reply_len2, 1) <> ''";

        if ($write['wr_reply']) {
            $query .= ' AND wr_reply LIKE :wr_reply';
            $values = array_merge($values, ['wr_reply' => $write['wr_reply'] . '%']);
        }
        $stmt = Db::getInstance()->run($query, $values);
        $row = $stmt->fetch();
        return $row['reply'] ?? '';
    }

    /**
     * 게시글 작성
     * @param object $data 게시글 데이터
     * @param array $member 회원 정보
     * @param array $parent_write 부모 게시글 정보
     * @return false|string
     * @throws Exception
     */
    public function createWriteData(object $data, array $member = [], array $parent_write = [])
    {
        $data->wr_num = $this->fetchMinimumWriteNumber() - 1;
        $data->wr_parent = $parent_write['wr_id'] ?? 0;
        $data->setWrSeoTitle($this->url_service->getSeoTtitleRecursive('bbs', $data->wr_subject, $this->table));
        $data->setMbId($member['mb_id'] ?? '');
        $data->setWrDatetime(G5_TIME_YMDHIS);
        $data->setWrLast(G5_TIME_YMDHIS);
        $data->setWrIp($_SERVER['REMOTE_ADDR']);
        if ($parent_write) {
            $data->wr_reply = $this->getReplyCharacter($parent_write);
        }

        //inserted id
        return $this->insertWrite($data->toArray());
    }

    /**
     * 게시글 작성 처리
     * @param array $data 게시글 데이터
     * @return false|string 추가된 게시글 아이디 / 실패시 false
     */
    public function insertWrite(array $data)
    {
        return Db::getInstance()->insert($this->table, $data);
    }

    /**
     * 게시글 수정
     * @param array $write 게시글 정보
     * @param object $data 게시글 데이터
     * @return void
     */
    public function updateWriteData(array $write, object $data): void
    {
        $data = (array)$data;
        $data['wr_seo_title'] = $this->url_service->generateSeoTitle($data['wr_subject']);
        $data['wr_last'] = G5_TIME_YMDHIS;

        $this->updateWrite($write['wr_id'], $data);
    }

    /**
     * 게시글 수정 처리
     * @param int $wr_id 게시글 아이디
     * @param array $data 수정할 데이터
     * @return void
     */
    public function updateWrite(int $wr_id, array $data): void
    {
        Db::getInstance()->update($this->table, $data, ['wr_id' => $wr_id]);
    }

    /**
     * 게시글의 부모 아이디 갱신
     * @param int $wr_id 갱신할 게시글 아이디
     * @param int $parent_id 부모 아이디
     */
    public function updateWriteParentId(int $wr_id, int $parent_id): void
    {
        Db::getInstance()->update(
            $this->table,
            ['wr_parent' => $parent_id],
            ['wr_id' => $wr_id]
        );
    }

    /**
     * 게시글의 댓글들의 카테고리 갱신
     * @param int $wr_id 게시글 아이디
     * @param string $ca_name 카테고리 이름
     * @return void
     */
    public function updateCategoryByParentId(int $wr_id, string $ca_name): void
    {
        Db::getInstance()->update(
            $this->table,
            ['ca_name' => $ca_name],
            ['wr_parent' => $wr_id],
        );
    }

    /**
     * 게시글 추천/비추천 갱신
     * @param int $wr_id 게시글 아이디
     * @param string $good_type 추천/비추천 (good/nogood)
     * @return void
     */
    public function updateWriteGood(int $wr_id, string $good_type): void
    {
        $column = "wr_{$good_type}";
        $query = "UPDATE {$this->table} SET {$column} = {$column} + 1 WHERE wr_id = :wr_id";
        Db::getInstance()->run($query, ['wr_id' => $wr_id]);
    }

    /**
     * 게시글 삭제
     * @param int $wr_id 게시글 아이디
     */
    public function deleteWrite(int $wr_id): void
    {
        Db::getInstance()->delete($this->table, ['wr_id' => $wr_id]);
    }

    /**
     * @param int $wr_parent
     * @return void
     * @todo 다시 확인
     * 부모 아이디로 게시글 삭제
     */
    public function deleteWriteByParentId(int $wr_parent): void
    {
        Db::getInstance()->delete($this->table, ['wr_parent' => $wr_parent]);
    }

    /**
     * 게시글 목록 검색조회 조건 설정
     * - 검색조건에 따라 WHERE 절을 생성한다.
     * @param array $query_params 검색조건
     * @param array $params 바인딩할 파라미터
     * @return string
     */
    protected function getWhereBySearch(array $query_params, &$params = []): string
    {
        $category = $query_params['sca'] ?? '';
        $keyword = $query_params['stx'] ?? '';
        $field_string = $query_params['sfl'] ?? '';
        $where_operator = $query_params['sod'] ?? '';

        $query_parts = [];

        // 카테고리
        if ($category) {
            $query_parts[] = 'ca_name = :ca_name';
            $params[':ca_name'] = $category;
        }

        if (empty($keyword) && $keyword !== '0') {
            $query_parts[] = 'wr_is_comment = 0';
            return implode(' AND ', $query_parts);
        }

        // 검색어 처리
        $terms = explode(' ', $keyword);
        $tmp = explode(',', $field_string);
        $fields = array_map('trim', explode('||', $tmp[0]));
        $is_write = isset($tmp[1]) ? trim($tmp[1]) : '';

        $search_clauses = [];

        foreach ($terms as $i => $term) {
            if (!in_array('mb_id', $fields)) {
                $this->popular_service->addKeyword($term);
            }

            $field_clauses = [];
            foreach ($fields as $field) {
                // SQL Injection prevention by whitelisting
                $field = preg_match('/^[\w\,\|]+$/', $field) ? strtolower($field) : 'wr_subject';
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
                        $field_clauses[] = '1=0';
                        break;
                    default:
                        if (preg_match('/[a-zA-Z]/', $term)) {
                            $field = strtolower($field);
                            $param_key = strtolower($param_key);
                        }
                        $field_clauses[] = "INSTR($field, $param_key)";
                        $params[$param_key] = $term;
                        break;
                }
            }
            $search_clauses[] = '(' . implode(' OR ', $field_clauses) . ')';
        }

        $query_parts[] = '(' . implode(" $where_operator ", $search_clauses) . ')';

        // 기존 코드에서의 개선점 
        // - 댓글 검색옵션이 없으면 게시글만 검색한다.
        if ($is_write === '0') {
            $query_parts[] = "wr_is_comment = '1'";
        } else {
            $query_parts[] = "wr_is_comment = '0'";
        }

        return implode(' AND ', $query_parts);
    }

    /**
     * 검색 단위 설정
     * @param array $search_params 검색조건
     * @param array $params 바인딩할 파라미터
     * @return string
     */
    protected function getWhereSearchPart(array $search_params, &$params = []): string
    {
        if (!$search_params['is_search']) {
            return '';
        }

        $spt = $search_params['spt'];
        $search_part = $search_params['search_part'];

        $params[':min_wr_num'] = $spt;
        $params[':max_wr_num'] = $spt + $search_part;

        return ' AND (wr_num BETWEEN :min_wr_num AND :max_wr_num)';
    }

    /**
     * 정렬 조건 설정
     * @param array $search_params 검색조건
     * @return array [sst, sod] 정렬필드, 정렬방식
     */
    protected function getSortOrder(array $search_params): array
    {
        $sst = $search_params['sst'] ?? null;
        $sod = $search_params['sod'] ?? '';

        if (!$sst) {
            if ($this->board['bo_sort_field']) {
                $sst = $this->board['bo_sort_field'];
            } else {
                $sst = 'wr_num, wr_reply';
                $sod = '';
            }
        } else {
            $board_sort_fields = get_board_sort_fields($this->board, 1);
            if (!$sod && array_key_exists($sst, $board_sort_fields)) {
                $sst = $board_sort_fields[$sst];
            } else {
                $sst = preg_match('/^(wr_datetime|wr_hit|wr_good|wr_nogood)$/i', $sst) ? $sst : '';
            }
        }

        if (!$sst) {
            $sst = 'wr_num, wr_reply';
        }

        return [$sst, $sod];
    }


    /**
     * 검색 단위 > 이전위치 조회
     * @param array $search_params 검색조건
     * @return int|mixed
     */
    public function getPrevSearchPart(array $search_params)
    {
        if (!$search_params['is_search']) {
            return 0;
        }
        $min_spt = $search_params['min_spt'];
        $prev_spt = $search_params['spt'] - $search_params['search_part'];
        if (isset($min_spt) && $prev_spt < $min_spt) {
            return 0;
        }
        return $search_params['spt'] - $search_params['search_part'];
    }

    /**
     *  검색 단위 > 다음위치 조회
     * @param array $search_params 검색조건
     * @return int|mixed
     */
    public function getNextSearchPart(array $search_params)
    {
        if (!$search_params['is_search']) {
            return 0;
        }
        $next_spt = $search_params['spt'] + $search_params['search_part'];
        if ($next_spt > 0) {
            return 0;
        }
        return $search_params['spt'] + $search_params['search_part'];
    }

    /**
     * 답변글 작성시 답변글 wr_reply 생성
     * - Exception 관련 코드를 Permission으로 이동하고 싶었으나 코드 중복이 발생하여 이동하지 않음
     * @param array $write 게시글 정보
     * @return string
     * @throws Exception 답변가능한 갯수를 넘어섰을 때 발생
     */
    public function getReplyCharacter(array $write)
    {
        $last_reply = $this->fetchLastReply($write);

        if ($this->board['bo_reply_order']) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
        } else {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;
        }

        if (!$last_reply) {
            $reply_char = $begin_reply_char;
        } else if ($last_reply == $end_reply_char) {
            throw new Exception('더 이상 답변하실 수 없습니다. 답변은 26개 까지만 가능합니다.');
        } else {
            $reply_char = chr(ord($last_reply) + $reply_number);
        }

        return $write['wr_reply'] . $reply_char;
    }

    // ========================================
    // Getters and Setters
    // ========================================

    public function setBoard(array $board): void
    {
        $this->board = $board;
        $this->setWriteTable($board['bo_table']);
    }

    public function setWriteTable(string $bo_table): void
    {
        global $g5;
        $this->table = $g5['write_prefix'] . $bo_table;
    }
}
