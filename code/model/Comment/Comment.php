<?php

namespace Kelvinho\Notes\Comment;

use Kelvinho\Notes\Singleton\Logs;
use mysqli;

/**
 * Class Comment. Each comment has an owner, a unix timestamp, the highlight it is in, a parent comment, and the content itself.
 *
 * It's important to remember that a Highlight have a root comment, then we can have child comments hooked on like a linked list.
 *
 * @package Kelvinho\Notes\Comment
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
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
     * @internal For constructing the object only
     */
    public function getParentCommentId(): int {
        return $this->parentCommentId;
    }

    /**
     * @param Comment $parentComment
     * @internal For constructing the object only
     */
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

    /**
     * Finds the child comment that has a specific id. Returns null if can't find one, instead of throwing an error.
     *
     * @param int $childCommentId
     * @return $this|null
     */
    public function findChild(int $childCommentId): ?Comment {
        if ($this->commentId == $childCommentId) return $this;
        if ($this->childComment !== null) return $this->childComment->findChild($childCommentId);
        return null;
    }

    /**
     * Deletes this comment only, and reconnect the comment chain. If the comment is root then deletes the entire chain.
     *
     * @param bool $hard For internal uses only. If true, then mindlessly deletes this element and the chain below
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
        } else { // if this is not the root, then just delete normally, which means rewiring the structure
            if ($childCommentId !== 0)
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
