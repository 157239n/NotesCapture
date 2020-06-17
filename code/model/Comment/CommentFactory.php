<?php

namespace Kelvinho\Notes\Comment;

use Kelvinho\Notes\Highlight\Highlight;
use Kelvinho\Notes\Singleton\Logs;
use mysqli;

class CommentFactory {
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }

    /**
     * Gets root comment of a specific highlight.
     *
     * @param int $highlightId
     * @return Comment
     */
    public function getRoot(int $highlightId): Comment {
        if (!$answer = $this->mysqli->query("select comment_id, user_handle, parent_comment_id, unix_time, content from comments where highlight_id = $highlightId")) throw new CommentNotFound();
        /** @var Comment[] $comments */
        $comments = array();
        while ($row = $answer->fetch_assoc())
            $comments[$row["comment_id"]] = new Comment($this->mysqli, $row["comment_id"], $highlightId, $row["user_handle"], $row["parent_comment_id"], $row["unix_time"], $row["content"]);
        $anyComment = null;
        foreach ($comments as $comment_id => $comment) {
            $parentCommentId = $comment->getParentCommentId();
            if (isset($comments[$parentCommentId])) {
                $comment->setParentComment($comments[$parentCommentId]);
                $comments[$parentCommentId]->setChildComment($comment);
            }
            $anyComment = $comment;
        }
        if ($anyComment === null) throw new CommentNotFound();
        return $anyComment->getRoot();
    }

    /**
     * @param Highlight $highlight
     * @param string $user_handle
     * @param Comment $parentComment
     * @param string $content
     * @return Comment
     */
    public function new(Highlight $highlight, string $user_handle, string $content, ?Comment $parentComment = null): Comment {
        $highlightId = $highlight->getHighlightId();
        $parentCommentId = ($parentComment === null) ? 0 : $parentComment->getCommentId();
        $unixTime = time();
        if (!$this->mysqli->query("insert into comments (highlight_id, user_handle, parent_comment_id, unix_time, content) values ($highlightId, '" . $this->mysqli->escape_string($user_handle) . "', $parentCommentId, $unixTime, '" . $this->mysqli->escape_string($content) . "')")) Logs::error($this->mysqli->error);
        $insertId = $this->mysqli->insert_id;
        return $this->getRoot($highlightId)->findChild($insertId);
    }
}
