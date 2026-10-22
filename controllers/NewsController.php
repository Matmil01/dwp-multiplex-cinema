<?php

class NewsController
{
    public function index(): void
    {
        $articles = News::all();

        require __DIR__ . '/../views/news.php';
    }

    public function show(int $id): void
    {
        $article = News::find($id);

        if (!$article) {
            http_response_code(404);
            $title = 'Article not found';
            $message = 'That article does not exist or is not published yet.';
            require __DIR__ . '/../views/error.php';
            return;
        }

        require __DIR__ . '/../views/news-article.php';
    }
}
