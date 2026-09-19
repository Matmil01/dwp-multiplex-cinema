<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DWP Multiplex Cinema</title>
    <link rel="stylesheet" href="assets/dist.css">
</head>
<body class="bg-gray-950 text-white">
    <header class="p-6 border-b border-gray-800">
        <h1 class="text-2xl font-bold">DWP Multiplex Cinema</h1>
        <?php require __DIR__ . '/../src/components/navbar.php'; ?>
    </header>

    <main class="p-6 space-y-8">
        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-6">Tonight's Program</h2>

            <div class="mb-10">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-amber-400 mb-4">On Film This Week</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder C poster" class="w-2/5 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-amber-500 text-amber-300 mb-2">35mm Print</span>
                                <h3 class="font-semibold text-lg mb-1"><a href="movie-details.php?id=3" class="hover:underline">Movie placeholder C</a></h3>
                                <p class="text-gray-400 text-base">Comedy &middot; 95 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 8:00 PM &middot; 35mm Screen</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder D poster" class="w-2/5 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-amber-400 text-amber-200 mb-2">70mm Roadshow</span>
                                <h3 class="font-semibold text-lg mb-1"><a href="movie-details.php?id=4" class="hover:underline">Movie placeholder D</a></h3>
                                <p class="text-gray-400 text-base">Sci-Fi, Adventure &middot; 128 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 6:15 PM &middot; 70mm Screen</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-4">Also Screening</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder A poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-gray-600 text-gray-300 mb-2">Digital</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=1" class="hover:underline">Movie placeholder A</a></h3>
                                <p class="text-gray-400 text-base">Drama, Mystery &middot; 118 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 7:15 PM &middot; Standard Digital</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder B poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-blue-500 text-blue-300 mb-2">IMAX</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=2" class="hover:underline">Movie placeholder B</a></h3>
                                <p class="text-gray-400 text-base">Action, Thriller &middot; 102 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 9:30 PM &middot; IMAX</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder E poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-gray-600 text-gray-300 mb-2">Digital</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=5" class="hover:underline">Movie placeholder E</a></h3>
                                <p class="text-gray-400 text-base">Horror &middot; 99 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 9:30 PM &middot; Standard Digital</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder F poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-gray-600 text-gray-300 mb-2">Digital</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=6" class="hover:underline">Movie placeholder F</a></h3>
                                <p class="text-gray-400 text-base">Animation, Family &middot; 90 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 5:30 PM &middot; Standard Digital</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder G poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-gray-600 text-gray-300 mb-2">Digital</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=7" class="hover:underline">Movie placeholder G</a></h3>
                                <p class="text-gray-400 text-base">Documentary &middot; 84 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 4:00 PM &middot; Standard Digital</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <div class="flex gap-4 items-center">
                            <img src="assets/images/movie.jpg" alt="Movie placeholder H poster" class="w-1/2 aspect-2/3 object-cover rounded-lg shrink-0">
                            <div class="flex-1">
                                <span class="inline-block uppercase text-xs tracking-widest px-2 py-0.5 rounded border border-blue-500 text-blue-300 mb-2">IMAX</span>
                                <h3 class="font-semibold mb-2"><a href="movie-details.php?id=8" class="hover:underline">Movie placeholder H</a></h3>
                                <p class="text-gray-400 text-base">Romance, Drama &middot; 112 min</p>
                                <p class="text-gray-500 text-sm mt-1">Today, 8:45 PM &middot; IMAX</p>
                                <a href="#" class="inline-block mt-3 bg-blue-900 rounded-lg px-4 py-2 text-sm">Tickets</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-4">About DWP Multiplex Cinema</h2>
            <p class="text-gray-400">Don't come. We hate people.</p>
        </section>

        <section class="py-8 min-h-screen">
            <h2 class="text-xl font-semibold mb-4">News</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 15, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder A</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=1" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 10, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder B</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=2" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">September 4, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder C</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=3" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 28, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder D</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=4" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 21, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder E</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=5" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
                <div class="bg-gray-900 rounded-lg p-4">
                    <div class="w-full h-20 bg-gray-800 rounded-lg flex items-center justify-center text-gray-500 text-xs mb-2">Thumbnail</div>
                    <p class="text-gray-500 text-xs mb-1">August 14, 2026</p>
                    <h3 class="font-semibold mb-1">Headline placeholder F</h3>
                    <p class="text-gray-400 text-sm mb-2">Snippet placeholder text, a short teaser of the full post.</p>
                    <a href="news-article.php?id=6" class="text-blue-400 text-sm hover:underline">Read more &rarr;</a>
                </div>
            </div>
            <a href="news.php" class="text-blue-400 text-sm hover:underline">See all news &rarr;</a>
        </section>
    </main>

    <?php require __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
