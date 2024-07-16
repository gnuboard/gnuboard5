<?php

namespace API\Service;

use API\Database\Db;
use API\Service\BoardService;
use API\Service\GroupService;
use Exception;

class BoardPermission
{
    public array $config;
    public array $board;

    private GroupService $group_service;
    private BoardService $board_service;

    private const ERROR_NO_LIST_PERMISSION = '글 목록을 볼 권한이 없습니다.';
    private const ERROR_NO_READ_PERMISSION = '글을 읽을 권한이 없습니다.';
    private const ERROR_NO_WRITE_PERMISSION = '글을 작성할 권한이 없습니다.';
    private const ERROR_NO_WRITE_NOTICE_PERMISSION = '공지글을 작성할 권한이 없습니다.';
    private const ERROR_NO_NOTICE_REPLY_PERMISSION = '공지에는 답변 할 수 없습니다.';
    private const ERROR_NO_REPLY_PERMISSION = '글을 답변할 권한이 없습니다.';
    private const ERROR_NO_REPLY_WRITE = '답변할 글이 존재하지 않습니다.';
    private const ERROR_REPLY_DEPTH = '더 이상 답변하실 수 없습니다. 답변은 10단계 까지만 가능합니다.';
    private const ERROR_INSUFFICIENT_POINTS = '보유하신 포인트(%s)가 없거나 모자라서 글읽기(%s)가 불가합니다.';
    private const ERROR_NO_GUEST_ACCESS = '비회원은 이 게시판에 접근할 권한이 없습니다.';
    private const ERROR_NO_GROUP_ACCESS = '게시판에 접근할 권한이 없습니다.';
    private const ERROR_CERT_REQUIRED = '이 게시판은 본인인증을 진행하신 회원만 접근 가능합니다.';
    private const ERROR_CERT_CHANGED = '본인인증 정보가 변경되었습니다. 다시 인증을 진행해주세요.';
    private const ERROR_ADULT_REQUIRED = '이 게시판은 본인확인으로 성인인증 된 회원님만 글읽기가 가능합니다.';
    private const ERROR_SECRET_WRITE = '비밀글은 조회할 수 없습니다.';

    public function __construct(array $config, array $board)
    {
        $this->config = $config;
        $this->board = $board;
        $this->group_service = new GroupService();
        $this->board_service = new BoardService($board);
    }

    /**
     * 글 목록 조회 권한 체크
     */
    public function checkAccessWrites(array $member): void
    {
        $board_level = (int)$this->board['bo_list_level'];
        if (!$this->checkLevel($board_level, $member)) {
            $this->throwException(self::ERROR_NO_LIST_PERMISSION);
        }

        $this->checkAccessByCert($member);
    }

    /**
     * 글 읽기 권한 체크
     */
    public function checkAccessWrite(array $member, array $write): void
    {
        $this->checkAccessBoardGroup($member['mb_id']);

        $board_level = (int)$this->board['bo_read_level'];
        if (!$this->checkLevel($board_level, $member)) {
            $this->throwException(self::ERROR_NO_READ_PERMISSION);
        }

        $this->checkAccessByCert($member);
        $this->checkAccessSecretWrite($member, $write);
        $this->checkAccessByPoint('read', $member, $write);
    }

    /**
     * 공지 게시글 작성권한 체크
     */
    public function checkAccessCreateNotice(array $member): void
    {
        if (!$this->isBoardManager($member['mb_id'])) {
            $this->throwException(self::ERROR_NO_WRITE_NOTICE_PERMISSION);
        }
    }

    /**
     * 글 작성 권한 체크
     */
    public function checkAccessCreateWrite(array $member): void
    {
        $this->checkAccessBoardGroup($member['mb_id']);

        $board_level = (int)$this->board['bo_write_level'];
        if (!$this->checkLevel($board_level, $member)) {
            $this->throwException(self::ERROR_NO_WRITE_PERMISSION);
        }
    }

    /**
     * 글 답변 권한 체크
     */
    public function checkAccessCreateReply(array $member, array $parent_write): void
    {
        $this->checkAccessBoardGroup($member['mb_id']);

        if (empty($parent_write)) {
            $this->throwException(self::ERROR_NO_REPLY_WRITE);
        }

        $board_level = (int)$this->board['bo_reply_level'];
        if (!$this->checkLevel($board_level, $member)) {
            $this->throwException(self::ERROR_NO_REPLY_PERMISSION);
        }

        $this->checkAccessNoticeReply($parent_write['wr_id']);
        $this->checkReplyDepth($parent_write);
        // TODO: 답변 갯수 체크 (26개 A-Z) 추가
        // TODO: 게시글 연속 등록 방지 추가
    }

    /**
     * 공지 답변 금지 체크
     */
    public function checkAccessNoticeReply(int $parent_id): void
    {
        $notice_ids = explode(",", $this->board['bo_notice']);
        if (in_array($parent_id, $notice_ids)) {
            $this->throwException(self::ERROR_NO_NOTICE_REPLY_PERMISSION);
        }
    }

    /**
     * 답변 깊이 체크
     */
    public function checkReplyDepth(array $reply_array): void
    {
        if (strlen($reply_array['wr_reply']) == 10) {
            $this->throwException(self::ERROR_REPLY_DEPTH);
        }
    }

    /**
     * 
     * 글읽기 포인트 체크
     * - 그누보드5에선 세션을 사용했지만 세션을 사용하지 않으므로 테이블의 내역을 체크한다.
     * 
     * TODO: 범용적으로 사용될 수 있도록 수정이 필요하다.
     */
    public function checkAccessByPoint(string $type, array $member, array $write): void
    {
        global $g5;

        $board_point = (int)$this->board['bo_' . $type . '_point'];
        $board_level = (int)$this->board['bo_' . $type . '_level'];
        $board_subject = (G5_IS_MOBILE && $this->board['bo_mobile_subject']) ? $this->board['bo_mobile_subject'] : $this->board['bo_subject'];
        $mb_id = $member['mb_id'];
        $mb_point = $member['mb_point'];
        $wr_id = $write['wr_id'];

        if (!$this->config['cf_use_point'] || !$board_point) {
            return;
        }
        if ($this->isBoardManager($mb_id) || $this->isOwner($write, $mb_id)) {
            return;
        }
        if ($board_level == 1 && $write['wr_ip'] == $_SERVER['REMOTE_ADDR']) {
            return;
        }

        // TODO: 포인트 관련 처리 위치로 이동 필요
        $query = "SELECT * FROM {$g5['point_table']} WHERE mb_id = :mb_id AND po_rel_table = :po_rel_table AND po_rel_id = :po_rel_id AND po_rel_action = :po_rel_action";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'po_rel_table' => $this->board['bo_table'],
            'po_rel_id' => $wr_id,
            'po_rel_action' => '읽기'
        ]);
        $point = $stmt->fetch();
        if ($point) {
            return;
        }

        if (!$this->checkPoint($board_point, $mb_point)) {
            $this->throwException(sprintf(self::ERROR_INSUFFICIENT_POINTS, $mb_point, $board_point));
        }

        insert_point($mb_id, $board_point, $board_subject . ' ' . $wr_id . ' 글읽기', $this->board['bo_table'], $wr_id, '읽기');
    }

    /**
     * 게시판 접근권한 체크
     */
    public function checkAccessBoardGroup(string $mb_id): void
    {
        $group = $this->group_service->fetchGroup($this->board['gr_id']);

        if (!isset($group['gr_use_access']) || !$group['gr_use_access']) {
            return;
        }

        if (!$mb_id) {
            $this->throwException(self::ERROR_NO_GUEST_ACCESS);
        }

        if (!$this->isBoardManager($mb_id, $group)) {
            $group_member = $this->group_service->fetchGroupMember($group['gr_id'], $mb_id);
            if (empty($group_member)) {
                $this->throwException(self::ERROR_NO_GROUP_ACCESS);
            }
        }
    }

    /**
     * 본인인증 체크
     */
    public function checkAccessByCert(array $member): void
    {
        if ($this->board['bo_use_cert'] == '' || !$this->config['cf_cert_use'] || $this->isSuperAdmin($member['mb_id'])) {
            return;
        }

        if (
            empty($member['mb_id'])
            || ($this->board['bo_use_cert'] == 'cert' && !$member['mb_certify'])
        ) {
            $this->throwException(self::ERROR_CERT_REQUIRED);
        }
        // 본인 인증 된 계정 중에서 di로 저장 되었을 경우
        if (strlen($member['mb_dupinfo']) == 64 && $member['mb_certify']) {
            $this->throwException(self::ERROR_CERT_CHANGED);
        }

        if ($this->board['bo_use_cert'] == 'adult' && !$member['mb_adult']) {
            $this->throwException(self::ERROR_ADULT_REQUIRED);
        }
    }

    /**
     * 비밀글 읽기 권한 체크
     */
    public function checkAccessSecretWrite(array $member, array $write): void
    {
        if (!strstr($write['wr_option'], "secret")) {
            return;
        }

        $mb_id = $member['mb_id'];

        if ($this->isBoardManager($mb_id) || $this->isOwner($write, $mb_id)) {
            return;
        }

        // 답변글일 경우 원글의 작성자인지 체크
        if ($write['wr_reply'] && $mb_id) {
            $parent_write = $this->board_service->fetchParentWriteByNumber((int)$write['wr_num']);
            if (isset($parent_write['mb_id']) && $parent_write['mb_id'] === $mb_id) {
                return;
            }
        }

        $this->throwException(self::ERROR_SECRET_WRITE);
    }

    /**
     * 게시판 포인트 체크
     */
    public function checkPoint(int $board_point, int $member_point): bool
    {
        return $member_point + $board_point >= 0;
    }

    /**
     * 게시판 접근 레벨 체크
     */
    public function checkLevel(int $board_level, array $member): bool
    {
        $member_level = isset($member['mb_level']) ? $member['mb_level'] : 1;
        return $member_level >= $board_level;
    }

    /**
     * 관리자 체크
     */
    public function isBoardManager(string $mb_id, array $group = null): bool
    {
        if (is_null($group)) {
            $group = $this->group_service->fetchGroup($this->board['gr_id']);
        }

        return is_super_admin($this->config, $mb_id)
            || $this->isGroupAdmin($group, $mb_id)
            || $this->isBoardAdmin($mb_id);
    }

    /**
     * 그룹 관리자 체크
     */
    private function isGroupAdmin(array $group, string $mb_id): bool
    {
        if (empty($mb_id) || !isset($group['gr_admin']) || empty($group['gr_admin'])) {
            return false;
        }
        return $group['gr_admin'] === $mb_id;
    }

    /**
     * 게시판 관리자 체크
     */
    private function isBoardAdmin(string $mb_id): bool
    {
        if (empty($mb_id) || !isset($this->board['bo_admin']) || empty($this->board['bo_admin'])) {
            return false;
        }
        return $this->board['bo_admin'] === $mb_id;
    }

    /**
     * 글 작성자 체크
     */
    private function isOwner(array $write, string $mb_id): bool
    {
        if (empty($mb_id) || !isset($write['mb_id']) || empty($write['mb_id'])) {
            return false;
        }
        return $write['mb_id'] === $mb_id;
    }

    /**
     * 예외를 던지기 위한 메서드
     */
    private function throwException(string $message): void
    {
        throw new Exception($message);
    }
}
