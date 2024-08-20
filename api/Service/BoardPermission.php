<?php

namespace API\Service;

use API\Service\BoardService;
use API\Service\GroupService;
use Exception;

/**
 * 게시판 전반의 권한 확인에 쓰입니다.
 */
class BoardPermission
{
    public array $config;
    public array $group;
    public array $board;

    private GroupService $group_service;
    private BoardService $board_service;
    private BoardGoodService $board_good_service;
    private MemberService $member_service;
    private PointService $point_service;
    private WriteService $write_service;

    private const ERROR_NO_ACCESS_GUEST = '비회원은 이 게시판에 접근할 권한이 없습니다.';
    private const ERROR_NO_ACCESS_PASSWORD = '비밀번호가 일치하지 않습니다.';
    private const ERROR_NO_ACCESS_GROUP = '게시판에 접근할 권한이 없습니다.';
    private const ERROR_NO_ACCESS_CERT = '이 게시판은 본인인증을 진행하신 회원만 접근 가능합니다.';
    private const ERROR_NO_ACCESS_CERT_CHANGED = '본인인증 정보가 변경되었습니다. 다시 인증을 진행해주세요.';
    private const ERROR_NO_ACCESS_ADULT = '이 게시판은 본인확인으로 성인인증 된 회원님만 접근이 가능합니다.';
    private const ERROR_NO_READ_LIST = '글 목록을 볼 권한이 없습니다.';
    private const ERROR_NO_READ_WRITE = '글을 읽을 권한이 없습니다.';
    private const ERROR_NO_READ_SECRET = '비밀글은 조회할 수 없습니다.';
    private const ERROR_NO_READ_POINTS = '보유하신 포인트(%s)가 없거나 모자라서 글읽기(%s)가 불가합니다.';
    private const ERROR_NO_CREATE_LEVEL = '글을 작성할 권한이 없습니다.';
    private const ERROR_NO_CREATE_NOTICE = '공지글을 작성할 권한이 없습니다.';
    private const ERROR_NO_CREATE_POINTS = '보유하신 포인트(%s)가 없거나 모자라서 글쓰기(%s)가 불가합니다.';
    private const ERROR_NO_REPLY_NOTICE = '공지에는 답변할 수 없습니다.';
    private const ERROR_NO_REPLY_LEVEL = '글을 답변할 권한이 없습니다.';
    private const ERROR_NO_REPLY_DEPTH = '더 이상 답변하실 수 없습니다. 답변은 10단계 까지만 가능합니다.';
    private const ERROR_NO_REPLY_COUNT = '더 이상 답변하실 수 없습니다. 답변은 26개 까지만 가능합니다.';
    private const ERROR_NO_REPLY_SECRET = '비밀글에는 작성자 또는 관리자만 답변이 가능합니다.';
    private const ERROR_NO_REPLY_SECRET_NONMEMBER = '비회원의 비밀글에는 답변이 불가합니다.';
    private const ERROR_NO_UPDATE_LEVEL = '자신의 권한보다 높은 권한의 회원이 작성한 글은 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_OWNER = '자신의 글이 아니므로 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_REPLY = '이 글과 관련된 답변글이 존재하므로 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_COMMENT = '이 글과 관련된 코멘트가 존재하므로 수정할 수 없습니다. 코멘트가 %s건 이상 달린 원글은 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_PASSWORD = '비밀번호가 일치하지 않으므로 수정할 수 없습니다.';
    private const ERROR_NO_UPLOAD_LEVEL = '파일을 업로드할 권한이 없습니다.';
    private const ERROR_NO_UPLOAD_OWNER = '자신의 글이 아니므로 파일을 업로드할 수 없습니다.';
    private const ERROR_NO_DOWNLOAD_LEVEL = '파일을 다운로드할 권한이 없습니다.';
    private const ERROR_NO_DOWNLOAD_POINTS = '보유하신 포인트(%s)가 없거나 모자라서 파일 다운로드(%s)가 불가합니다.';
    private const ERROR_NO_DELETE_LEVEL = '자신의 권한보다 높은 권한의 회원이 작성한 글은 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_OWNER = '자신의 글이 아니므로 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_REPLY = '이 글과 관련된 답변글이 존재하므로 삭제 할 수 없습니다.';
    private const ERROR_NO_DELETE_COMMENT = '이 글과 관련된 코멘트가 존재하므로 삭제 할 수 없습니다. 코멘트가 %s건 이상 달린 원글은 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_PASSWORD = '비밀번호가 일치하지 않으므로 삭제할 수 없습니다.';
    private const ERROR_NO_CREATE_COMMENT_LEVEL = '댓글을 작성할 권한이 없습니다.';
    private const ERROR_NO_CREATE_COMMENT_POINTS = '보유하신 포인트(%s)가 없거나 모자라서 댓글쓰기(%s)가 불가합니다.';
    private const ERROR_NO_UPDATE_COMMENT_LEVEL = '자신의 권한보다 높은 권한의 회원이 작성한 댓글은 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_COMMENT_OWNER = '자신의 댓글이 아니므로 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_COMMENT_REPLY = '이 글과 관련된 대댓글이 존재하므로 수정할 수 없습니다.';
    private const ERROR_NO_UPDATE_COMMENT_PASSWORD = '비밀번호가 일치하지 않으므로 댓글을 수정할 수 없습니다.';
    private const ERROR_NO_DELETE_COMMENT_LEVEL = '자신의 권한보다 높은 권한의 회원이 작성한 글은 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_COMMENT_OWNER = '자신의 댓글이 아니므로 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_COMMENT_PASSWORD = '비밀번호가 일치하지 않으므로 댓글을 삭제할 수 없습니다.';
    private const ERROR_NO_DELETE_COMMENT_REPLY = '이 글과 관련된 대댓글이 존재하므로 삭제할 수 없습니다.';
    private const ERROR_NO_GOOD_OWNER = '자신의 글에는 %s 하실 수 없습니다.';
    private const ERROR_NO_GOOD_SETTING = '이 게시판은 %s 기능을 사용하지 않습니다.';
    private const ERROR_NO_GOOD_EXIST = "이미 %s 하신 글 입니다.";


    public function __construct(
        GroupService $group_service,
        BoardService $board_service,
        BoardGoodService $board_good_service,
        MemberService $member_service,
        PointService $point_service,
        WriteService $write_service
    ) {
        $this->group_service = $group_service;
        $this->board_service = $board_service;
        $this->board_good_service = $board_good_service;
        $this->member_service = $member_service;
        $this->point_service = $point_service;
        $this->write_service = $write_service;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function setGroup(array $group): void
    {
        $this->group = $group;
    }

    public function setBoard(array $board): void
    {
        $this->board = $board;
    }

    /**
     * 글 목록 조회 권한 체크
     * @throws Exception 권한 에러
     */
    public function readWrites(array $member): void
    {
        $level = (int)$this->board['bo_list_level'];
        $this->checkMemberLevel($member, $level, self::ERROR_NO_READ_LIST);
        $this->checkAccessCert($member);
    }

    /**
     * 글 읽기 권한 체크
     * @throws Exception
     */
    public function readWrite(array $member, array $write, string $password = null): void
    {
        $level = (int)$this->board['bo_read_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_READ_WRITE);
        $this->checkAccessCert($member);
        $this->checkReadSecretWrite($member, $write, $password);
        $this->checkMemberPoint('read', $member, $write);
    }

    /**
     * 글 작성 권한 체크
     */
    public function createWrite(array $member): void
    {
        $level = (int)$this->board['bo_write_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_CREATE_LEVEL);
        $this->checkAccessCert($member);
        // TODO: 게시글 연속 등록 방지 추가
    }

    /**
     * 공지 게시글 작성권한 체크
     */
    public function createNotice(array $member): void
    {
        if (!$this->isBoardManager($member['mb_id'])) {
            $this->throwException(self::ERROR_NO_CREATE_NOTICE);
        }
    }

    /**
     * 글 답변 권한 체크
     */
    public function createReply(array $member, array $write): void
    {
        $level = (int)$this->board['bo_reply_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_REPLY_LEVEL);
        $this->checkAccessCert($member);
        $this->checkReplyNotice($write['wr_id']);
        $this->checkReplyDepth($write);
        $this->checkReplySecret($member, $write);
        // TODO: 게시글 연속 등록 방지 추가
    }

    /**
     * 글 수정 권한 체크
     */
    public function updateWrite(array $member, array $write): void
    {
        if (is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        $this->checkAccessBoardGroup($member['mb_id']);
        $this->hasWriteReply($write, self::ERROR_NO_UPDATE_REPLY);
        $this->checkCommentLimit($write, $this->board['bo_count_modify'], self::ERROR_NO_UPDATE_COMMENT);
        $this->checkAccessCert($member);
        $this->verifyWriteOwnerAndLevel($member, $write, 'update');
    }

    /**
     * 비회원 글 수정 권한 체크
     */
    public function updateWriteByNonMember(array $member, array $write, string $wr_password): void
    {
        if ($this->isBoardManager($member['mb_id'])) {
            return;
        }

        $this->checkAccessBoardGroup($member['mb_id']);
        $this->hasWriteReply($write, self::ERROR_NO_UPDATE_REPLY);
        $this->checkCommentLimit($write, $this->board['bo_count_modify'], self::ERROR_NO_UPDATE_COMMENT);
        $this->checkAccessCert($member);
        $this->checkWritePassword($write, $wr_password, self::ERROR_NO_UPDATE_PASSWORD);
    }

    /**
     *  파일 업로드 권한 체크
     * @param array $member
     * @param array $write
     * @return void
     * @throws Exception
     */
    public function uploadFiles(array $member, array $write): void
    {
        if (is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        $level = (int)$this->board['bo_upload_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_UPLOAD_LEVEL);
        $this->checkAccessCert($member);
        $this->verifyWriteOwnerAndLevel($member, $write, 'upload');
    }

    /**
     *  파일 다운로드 권한 체크
     * @param array $member
     * @param array $write
     * @return void
     * @throws Exception
     */
    public function downloadFiles(array $member, array $write): void
    {
        if ($this->isBoardManager($member['mb_id'])) {
            return;
        }

        $level = (int)$this->board['bo_download_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_DOWNLOAD_LEVEL);
        $this->checkAccessCert($member);
        $this->checkMemberPoint('download', $member, $write);
    }

    /**
     * 추천/비추천 권한 체크
     * @param string $mb_id
     * @param array $write 글 정보
     * @param string $type good|nogood
     * @return void
     * @throws Exception
     */
    public function goodWrite(string $mb_id, array $write, string $type)
    {
        if (!$this->isUsedGood($type)) {
            $word = get_good_word($type);
            $this->throwException(sprintf(self::ERROR_NO_GOOD_SETTING, $word));
        }
        $exists = $this->board_good_service->fetchGoodByMember($mb_id, $this->board['bo_table'], $write['wr_id']);
        if ($exists) {
            $word = get_good_word($exists['bg_flag']);
            $this->throwException(sprintf(self::ERROR_NO_GOOD_EXIST, $word));
        }

        if ($this->isBoardManager($mb_id)) {
            return;
        }
        if ($this->isOwner($write, $mb_id)) {
            $word = get_good_word($type);
            $this->throwException(sprintf(self::ERROR_NO_GOOD_OWNER, $word));
        }
    }

    /**
     * 글 삭제 권한 체크
     */
    public function deleteWrite(array $member, array $write): void
    {
        if (is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        $this->verifyWriteOwnerAndLevel($member, $write, 'delete');
        $this->hasWriteReply($write, self::ERROR_NO_DELETE_REPLY);
        $this->checkCommentLimit($write, $this->board['bo_count_delete'], self::ERROR_NO_DELETE_COMMENT);
    }

    /**
     * 비회원 글 삭제 권한 체크
     */
    public function deleteWriteByNonMember(array $member, array $write, string $wr_password): void
    {
        if ($this->isBoardManager($member['mb_id'])) {
            return;
        }

        $this->hasWriteReply($write, self::ERROR_NO_DELETE_REPLY);
        $this->checkCommentLimit($write, $this->board['bo_count_delete'], self::ERROR_NO_DELETE_COMMENT);
        $this->checkWritePassword($write, $wr_password, self::ERROR_NO_DELETE_PASSWORD);
    }

    /**
     * 댓글 작성 권한 체크
     */
    public function createComment(array $member, array $write): void
    {
        $level = (int)$this->board['bo_comment_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_CREATE_COMMENT_LEVEL);
        $this->checkMemberPoint('comment', $member, $write);
        // TODO: 게시글 연속 등록 방지 추가
    }

    /**
     * 댓글 수정 권한 체크
     */
    public function updateComment(array $member, array $comment): void
    {
        if (is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        $level = (int)$this->board['bo_comment_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_CREATE_COMMENT_LEVEL);
        $this->hasCommentReply($comment, self::ERROR_NO_UPDATE_COMMENT_REPLY);
        $this->verifyCommentOwnerAndLevel($member, $comment, 'update');
    }

    /**
     * 비회원 댓글 수정 권한 체크
     */
    public function updateCommentByNonMember(array $member, array $comment, string $wr_password): void
    {
        if ($this->isBoardManager($member['mb_id'])) {
            return;
        }

        $this->checkAccessBoardGroup($member['mb_id']);
        $this->hasCommentReply($comment, self::ERROR_NO_UPDATE_COMMENT_REPLY);
        $this->checkWritePassword($comment, $wr_password, self::ERROR_NO_UPDATE_COMMENT_PASSWORD);
    }

    /**
     * 댓글 삭제 권한 체크
     */
    public function deleteComment(array $member, array $comment): void
    {
        if (is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        $this->checkAccessBoardGroup($member['mb_id']);
        $this->hasCommentReply($comment, self::ERROR_NO_DELETE_COMMENT_REPLY);
        $this->verifyCommentOwnerAndLevel($member, $comment, 'delete');
    }

    /**
     * 비회원 댓글 삭제 권한 체크
     */
    public function deleteCommentByNonMember(array $member, array $comment, string $wr_password): void
    {
        if ($this->isBoardManager($member['mb_id'])) {
            return;
        }

        $this->checkAccessBoardGroup($member['mb_id']);
        $this->hasCommentReply($comment, self::ERROR_NO_DELETE_COMMENT_REPLY);
        $this->checkWritePassword($comment, $wr_password, self::ERROR_NO_DELETE_COMMENT_PASSWORD);
    }

    /**
     * 관리자 체크
     */
    public function isBoardManager(string $mb_id): bool
    {
        return is_super_admin($this->config, $mb_id)
            || $this->isGroupAdmin($mb_id)
            || $this->isBoardAdmin($mb_id);
    }

    /**
     * 글 수정/삭제 시 관리자/작성자 체크
     * @throws Exception
     */
    private function verifyWriteOwnerAndLevel(array $member, array $write, string $type): void
    {
        $message_level = "";
        $message_owner = "";
        switch ($type) {
            case 'update':
                $message_level = self::ERROR_NO_UPDATE_LEVEL;
                $message_owner = self::ERROR_NO_UPDATE_OWNER;
                break;
            case 'delete':
                $message_level = self::ERROR_NO_DELETE_LEVEL;
                $message_owner = self::ERROR_NO_DELETE_OWNER;
                break;
            case 'upload':
                $message_level = self::ERROR_NO_UPLOAD_LEVEL;
                $message_owner = self::ERROR_NO_UPLOAD_OWNER;
                break;
        }

        $write_member = $this->member_service->fetchMemberById($write['mb_id']);

        if ($this->isGroupAdmin($member['mb_id']) || $this->isBoardAdmin($member['mb_id'])) {
            $this->checkMemberLevel($member, $write_member['mb_level'], $message_level);
        } elseif (!$this->isOwner($write, $member['mb_id'])) {
            $this->throwException($message_owner);
        }
    }

    /**
     * 글 수정/삭제 시 관리자/작성자 체크
     * @throws Exception
     */
    private function verifyCommentOwnerAndLevel(array $member, array $comment, string $type): void
    {
        $message_level = "";
        $message_owner = "";
        switch ($type) {
            case 'update':
                $message_level = self::ERROR_NO_UPDATE_COMMENT_LEVEL;
                $message_owner = self::ERROR_NO_UPDATE_COMMENT_OWNER;
                break;
            case 'delete':
                $message_level = self::ERROR_NO_DELETE_COMMENT_LEVEL;
                $message_owner = self::ERROR_NO_DELETE_COMMENT_OWNER;
                break;
        }

        $comment_member = $this->member_service->fetchMemberById($comment['mb_id']);

        if ($this->isGroupAdmin($member['mb_id']) || $this->isBoardAdmin($member['mb_id'])) {
            $this->checkMemberLevel($member, $comment_member['mb_level'], $message_level);
        } elseif (!$this->isOwner($comment, $member['mb_id'])) {
            $this->throwException($message_owner);
        }
    }

    /**
     * 대댓글이 있는지 체크
     */
    private function hasCommentReply(array $write, string $message): void
    {
        $replies = $this->write_service->fetchReplyByComment($write);
        if (count($replies) > 0) {
            $this->throwException($message);
        }
    }

    /**
     * 답변한 게시글이 공지글인지 체크
     * @throws Exception
     */
    private function checkReplyNotice(int $parent_id): void
    {
        $notice_ids = explode(",", $this->board['bo_notice']);
        if (in_array($parent_id, $notice_ids)) {
            $this->throwException(self::ERROR_NO_REPLY_NOTICE);
        }
    }

    /**
     * 답변글 작성시 원글이 비밀글인지 체크
     * @throws Exception
     */
    private function checkReplySecret(array $member, array $write): void
    {
        if (str_contains($write['wr_option'], 'secret')) {
            if ($this->isBoardManager($member['mb_id'])) {
                return;
            }

            if ($write['mb_id']) {
                if (!$this->isOwner($write, $member['mb_id'])) {
                    $this->throwException(self::ERROR_NO_REPLY_SECRET);
                }
            } else {
                $this->throwException(self::ERROR_NO_REPLY_SECRET_NONMEMBER);
            }
        }
    }

    /**
     * 답변 깊이 체크
     */
    private function checkReplyDepth(array $reply_array): void
    {
        if (strlen($reply_array['wr_reply']) == 10) {
            $this->throwException(self::ERROR_NO_REPLY_DEPTH);
        }
    }

    /**
     *
     * 글읽기 포인트 체크
     * - 그누보드5에선 세션을 사용했지만 세션을 사용하지 않으므로 테이블의 내역을 체크한다.
     */
    private function checkMemberPoint(string $type, array $member, array $write): void
    {
        // 읽기, 쓰기, 댓글, 다운로드
        switch ($type) {
            case 'read':
                $board_point = (int)$this->board['bo_read_point'];
                $board_level = (int)$this->board['bo_read_level'];
                $message = self::ERROR_NO_READ_POINTS;
                break;
            case 'write':
                $board_point = (int)$this->board['bo_write_point'];
                $board_level = (int)$this->board['bo_write_level'];
                $message = self::ERROR_NO_CREATE_POINTS;
                break;
            case 'comment':
                $board_point = (int)$this->board['bo_comment_point'];
                $board_level = (int)$this->board['bo_comment_level'];
                $message = self::ERROR_NO_CREATE_COMMENT_POINTS;
                break;
            case 'download':
                $board_point = (int)$this->board['bo_download_point'];
                $board_level = (int)$this->board['bo_download_level'];
                $message = self::ERROR_NO_DOWNLOAD_POINTS;
                break;
            default:
                return;
        }

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
        $exists_relation = $this->point_service->fetchPointByRelation($mb_id, $this->board['bo_table'], $wr_id, '읽기');
        if ($exists_relation) {
            return;
        }

        if ($mb_point + $board_point < 0) {
            $this->throwException(sprintf($message, $mb_point, $board_point));
        }
    }

    /**
     * 게시판 접근권한 체크
     */
    private function checkAccessBoardGroup(string $mb_id): void
    {
        if (!isset($this->group['gr_use_access']) || !$this->group['gr_use_access']) {
            return;
        }

        if (!$mb_id) {
            $this->throwException(self::ERROR_NO_ACCESS_GUEST);
        }

        if (!$this->isBoardManager($mb_id)) {
            $group_member = $this->group_service->fetchGroupMember($this->group['gr_id'], $mb_id);
            if (empty($group_member)) {
                $this->throwException(self::ERROR_NO_ACCESS_GROUP);
            }
        }
    }

    /**
     * 본인인증 체크
     * @throws Exception
     */
    private function checkAccessCert(array $member): void
    {
        if ($this->board['bo_use_cert'] == '' || !$this->config['cf_cert_use'] || is_super_admin($this->config, $member['mb_id'])) {
            return;
        }

        if (
            empty($member['mb_id'])
            || ($this->board['bo_use_cert'] === 'cert' && !$member['mb_certify'])
        ) {
            $this->throwException(self::ERROR_NO_ACCESS_CERT);
        }
        // 본인 인증 된 계정 중에서 di로 저장 되었을 경우
        if (strlen($member['mb_dupinfo']) == 64 && $member['mb_certify']) {
            $this->throwException(self::ERROR_NO_ACCESS_CERT_CHANGED);
        }

        if ($this->board['bo_use_cert'] === 'adult' && !$member['mb_adult']) {
            $this->throwException(self::ERROR_NO_ACCESS_ADULT);
        }
    }

    /**
     * 비밀글 읽기 권한 체크
     * @throws Exception
     */
    private function checkReadSecretWrite(array $member, array $write, $password = null): void
    {
        if (!str_contains($write['wr_option'], "secret")) {
            return;
        }

        $mb_id = $member['mb_id'];

        if ($this->isBoardManager($mb_id)) {
            return;
        }

        // 비회원 비밀글 읽기 권한 체크
        if (!$mb_id && $password != null && trim($write['wr_password'])) {
            $result = check_password($password, $write['wr_password']);
            if (!$result) {
                $this->throwException(self::ERROR_NO_ACCESS_PASSWORD);
            }
            return;
        }

        if ($this->isOwner($write, $mb_id)) {
            return;
        }

        // 답변글일 경우 원글의 작성자인지 체크
        if ($write['wr_reply'] && $mb_id) {
            $parent_write = $this->write_service->fetchParentWriteByNumber((int)$write['wr_num']);
            if (isset($parent_write['mb_id']) && $parent_write['mb_id'] === $mb_id) {
                return;
            }
        }

        $this->throwException(self::ERROR_NO_READ_SECRET);
    }

    /**
     * 댓글 읽기 권한 확인
     * @param $mb_id
     * @param array $comment
     * @param ?string $password 비밀번호를 알고있을경우
     * @return bool
     */
    public function canReadSecretComment($mb_id, array $comment, $password = null): bool
    {
        if (is_super_admin($this->config, $mb_id)) {
            return true;
        }
        if (!str_contains($comment['wr_option'], "secret")) {
            return true;
        }

        if ($this->isBoardManager($mb_id)) {
            return true;
        }

        // 비회원 비밀글 읽기 권한 체크
        if (!$mb_id && $password != null && trim($comment['wr_password'])) {
            $result = check_password($password, $comment['wr_password']);
            if (!$result) {
                return false;
            }
            return true;
        }

        if ($this->isOwner($comment, $mb_id)) {
            return true;
        }

        return false;
    }

    /**
     * 답변글이 있는지 체크
     */
    private function hasWriteReply(array $write, string $message): void
    {
        $replies = $this->write_service->fetchReplyByWrite($write);
        if (count($replies) > 0) {
            $this->throwException($message);
        }
    }

    /**
     * 댓글 읽기 권한 체크
     * @throws Exception
     */
    public function readComment(array $member, array $comment, string $password = null): void
    {
        $level = (int)$this->board['bo_read_level'];
        $this->checkAccessBoardGroup($member['mb_id']);
        $this->checkMemberLevel($member, $level, self::ERROR_NO_READ_WRITE);
        $this->checkAccessCert($member);
        $can_read = $this->canReadSecretComment($member['mb_id'], $comment, $password);
        if (!$can_read) {
            $this->throwException(self::ERROR_NO_READ_SECRET);
        }
    }

    /**
     * 댓글 수정/삭제 시 댓글 갯수 체크
     */
    private function checkCommentLimit(array $write, int $limit, string $message): void
    {
        $comments = $this->write_service->fetchCommentsByWrite($write);
        $comments_count = count($comments);
        if ($comments_count >= $limit) {
            $this->throwException(sprintf($message, $limit));
        }
    }

    /**
     * 게시글에 접근 가능한 회원레벨 체크
     * @throws Exception
     */
    private function checkMemberLevel(array $member, int $level, string $message): void
    {
        $member_level = $member['mb_level'] ?? 1;
        if ($member_level < $level) {
            $this->throwException($message);
        }
    }

    /**
     * 게시글 비밀번호 체크
     */
    private function checkWritePassword(array $write, string $wr_password, string $message): void
    {
        if (!check_password($wr_password, $write['wr_password'])) {
            $this->throwException($message);
        }
    }

    /**
     * 그룹 관리자 체크
     */
    private function isGroupAdmin(string $mb_id): bool
    {
        if (empty($mb_id) || !isset($this->group['gr_admin']) || empty($this->group['gr_admin'])) {
            return false;
        }
        return $this->group['gr_admin'] === $mb_id;
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
    public function isOwner(array $write, string $mb_id): bool
    {
        if (empty($mb_id) || !isset($write['mb_id']) || empty($write['mb_id'])) {
            return false;
        }
        return $write['mb_id'] === $mb_id;
    }

    /**
     * 게시판의 추천/비추천 사용 여부 체크
     * @param string $type good|nogood
     * @return bool
     */
    private function isUsedGood(string $type)
    {
        return isset($this->board['bo_use_' . $type]) && $this->board['bo_use_' . $type];
    }

    /**
     * 예외를 던지기 위한 메서드
     * @throws Exception
     */
    private function throwException(string $message): void
    {
        throw new Exception($message, 403);
    }
}
