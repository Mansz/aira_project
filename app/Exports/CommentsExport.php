<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class CommentsExport implements FromCollection
{
    protected $comments;

    public function __construct($comments)
    {
        $this->comments = $comments;
    }

    public function collection()
    {
        return $this->comments->map(function($comment) {
            return [
                'Time' => $comment->created_at->format('Y-m-d H:i:s'),
                'User' => $comment->user->name,
                'Comment' => $comment->content,
            ];
        });
    }
}