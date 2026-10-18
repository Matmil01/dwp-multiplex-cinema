<?php

require_once __DIR__ . '/../models/News.php';

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
            echo 'Article not found.';
            return;
        }

        require __DIR__ . '/../views/news-article.php';
    }
}
