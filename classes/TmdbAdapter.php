<?php

// Adapter: translates TMDB's API response shape into what the rest of the app expects.
class TmdbAdapter
{
    private const BASE_URL = 'https://api.themoviedb.org/3';
    private const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w200';

    private string $readToken;

    public function __construct(?string $readToken = null)
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->readToken = $readToken ?? $config['tmdb']['read_token'];
    }

    // Search results shown to the admin so they can pick the right match before importing it.
    public function searchMovies(string $query): array
    {
        $data = $this->request('/search/movie', ['query' => $query, 'include_adult' => 'false']);

        return array_map(function (array $result): array {
            return [
                'id' => $result['id'],
                'title' => $result['title'] ?? '',
                'year' => substr($result['release_date'] ?? '', 0, 4),
                'posterUrl' => $result['poster_path'] ? self::IMAGE_BASE_URL . $result['poster_path'] : null,
                'overview' => $result['overview'] ?? '',
            ];
        }, $data['results'] ?? []);
    }

    // Full movie info for one TMDB id, reshaped into the fields our own movies table expects.
    public function getMovieDetails(int $tmdbId): array
    {
        $data = $this->request("/movie/{$tmdbId}", ['append_to_response' => 'credits']);

        $crew = $data['credits']['crew'] ?? [];

        $director = null;
        foreach ($crew as $member) {
            if ($member['job'] === 'Director') {
                $director = ['tmdb_person_id' => $member['id'], 'name' => $member['name']];
                break;
            }
        }

        $writers = [];
        $seenWriterIds = [];
        foreach ($crew as $member) {
            if (in_array($member['job'], ['Writer', 'Screenplay', 'Story'], true) && !in_array($member['id'], $seenWriterIds, true)) {
                $writers[] = ['tmdb_person_id' => $member['id'], 'name' => $member['name']];
                $seenWriterIds[] = $member['id'];
            }
        }

        $castList = $data['credits']['cast'] ?? [];
        usort($castList, fn(array $a, array $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        $cast = array_map(
            fn(array $c) => ['tmdb_person_id' => $c['id'], 'name' => $c['name']],
            array_slice($castList, 0, 4)
        );

        return [
            'tmdb_id' => $data['id'],
            'title' => $data['title'] ?? '',
            'description' => $data['overview'] ?? '',
            'duration_minutes' => $data['runtime'] ?? null,
            'release_year' => $data['release_date'] ? (int) substr($data['release_date'], 0, 4) : null,
            'poster_url' => $data['poster_path'] ? self::IMAGE_BASE_URL . $data['poster_path'] : null,
            'genres' => array_map(fn(array $g) => $g['name'], $data['genres'] ?? []),
            'director' => $director,
            'writers' => $writers,
            'cast' => $cast,
        ];
    }

    // Shared HTTP call for every TMDB endpoint: adds the auth header, checks the response, decodes JSON.
    private function request(string $path, array $query): array
    {
        if ($this->readToken === '') {
            throw new RuntimeException('TMDB_READ_TOKEN is not set.');
        }

        $url = self::BASE_URL . $path . '?' . http_build_query($query);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->readToken,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('TMDB request failed: ' . $error);
        }

        if ($status !== 200) {
            throw new RuntimeException('TMDB request returned HTTP ' . $status . ': ' . $response);
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('TMDB returned an unexpected response.');
        }

        return $decoded;
    }
}
