<?php

class NewsController
{
    public function index(): void
    {
        // A list of rows (plain lists), newest first.
        $articles = News::all();

        require __DIR__ . '/../templates/news.php';
    }

    public function show(int $id): void
    {
        // A News object (so the view writes $article->title), or null if hidden/not found.
        $article = News::find($id);

        if (!$article) {
            http_response_code(404);
            $title = 'Article not found';
            $message = 'That article does not exist or is not published yet.';
            require __DIR__ . '/../templates/error.php';
            return;
        }

        require __DIR__ . '/../templates/news-article.php';
    }
}
