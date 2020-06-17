<?php

namespace Kelvinho\Notes\Comment;

use Kelvinho\Notes\Singleton\Logs;
use mysqli;

class Comment {
    private mysqli $mysqli;
    private int $commentId;
    private int $highlightId;
    private string $user_handle;
    private int $parentCommentId;
    private int $unixTime;
    private string $content;
    private ?Comment $parentComment = null;
    private ?Comment $childComment = null;

    /**
     * Highlight constructor.
     * @param mysqli $mysqli
     * @param int $commentId
     * @param int $highlightId
     * @param string $user_handle
     * @param int $parentCommentId
     * @param int $unixTime
     * @param string $content
     */
    public function __construct(mysqli $mysqli, int $commentId, int $highlightId, string $user_handle, int $parentCommentId, int $unixTime, string $content) {
        $this->mysqli = $mysqli;
        $this->commentId = $commentId;
        $this->highlightId = $highlightId;
        $this->user_handle = $user_handle;
        $this->parentCommentId = $parentCommentId;
        $this->unixTime = $unixTime;
        $this->content = $content;
    }

    public function getUserHandle(): string {
        return $this->user_handle;
    }

    public function getUnixTime(): int {
        return $this->unixTime;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): Comment {
        $this->content = $content;
        return $this;
    }

    /**
     * This is a separate function, to be used for initialization purposes only. It will still return the correct answer
     * in normal use, but you should get the parent first, then get its id.
     *
     * @return int
     */
    public function getParentCommentId(): int {
        return $this->parentCommentId;
    }

    public function setParentComment(Comment $parentComment) {
        $this->parentComment = $parentComment;
    }

    public function getChildComment(): ?Comment {
        return $this->childComment;
    }

    public function setChildComment(Comment $childComment) {
        $this->childComment = $childComment;
    }

    public function getRoot(): Comment {
        if ($this->parentComment !== null) return $this->parentComment->getRoot();
        return $this;
    }

    public function findChild(int $childCommentId): ?Comment {
        if ($this->commentId == $childCommentId) return $this;
        if ($this->childComment !== null) return $this->childComment->findChild($childCommentId);
        return null;
    }

    /**
     * Deletes this comment only, and reconnect the comment chain. If root then deletes the entire chain.
     * @param bool $hard
     */
    public function delete(bool $hard = false): void {
        if (!$this->mysqli->query("delete from comments where comment_id = $this->commentId")) Logs::mysql($this->mysqli);
        if ($hard) {
            if ($this->childComment !== null) $this->childComment->delete(true);
            return;
        }
        $parentCommentId = ($this->parentComment === null) ? 0 : $this->parentComment->getCommentId();
        $childCommentId = ($this->childComment === null) ? 0 : $this->childComment->getCommentId();
        if ($parentCommentId === 0) { // if this is the root, then deletes everything altogether, because this means that the associated highlight will also be deleted
            if ($childCommentId !== 0) $this->childComment->delete(true);
            return;
        }
        if ($childCommentId !== 0) { // if this is not the root, then just delete normally, which means rewiring the structure
            if (!$this->mysqli->query("update comments set parent_comment_id = $parentCommentId where comment_id = $childCommentId")) Logs::mysql($this->mysqli);
        }
    }

    public function getCommentId(): int {
        return $this->commentId;
    }

    public function saveState(): void {
        if (!$this->mysqli->query("update comments set content = '" . $this->mysqli->escape_string($this->content) . "' where comment_id = $this->commentId")) Logs::mysql($this->mysqli);
    }
}
