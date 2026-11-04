<?php

// Adapter: translates TMDB's API response shape into what the rest of the app expects.
class TmdbAdapter
{
    private const BASE_URL = 'https://api.themoviedb.org/3';
    // w200 = 200 pixels wide; TMDB serves posters at several sizes by changing this part.
    private const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p/w200';

    private string $readToken;

    public function __construct(?string $readToken = null)
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->readToken = $readToken ?? $config['tmdb']['read_token'];
    }

    // Search results shown to the admin so they can pick the right match before importing it.
    // array_map runs the small function on every result and collects the reshaped versions.
    // `?? ''` guards against fields TMDB sometimes leaves out.
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
        // append_to_response=credits asks for the cast and crew in the same request.
        $data = $this->request("/movie/{$tmdbId}", ['append_to_response' => 'credits']);

        $crew = $data['credits']['crew'] ?? [];

        // TMDB lists every crew member with a 'job'. Take the first one whose job is Director;
        // `break` leaves the loop as soon as it's found.
        $director = null;
        foreach ($crew as $member) {
            if ($member['job'] === 'Director') {
                $director = ['tmdb_person_id' => $member['id'], 'name' => $member['name']];
                break;
            }
        }

        // Writers can appear under several job titles, and the same person can hold more than
        // one of them — $seenWriterIds makes sure each person is listed once.
        $writers = [];
        $seenWriterIds = [];
        foreach ($crew as $member) {
            if (in_array($member['job'], ['Writer', 'Screenplay', 'Story'], true) && !in_array($member['id'], $seenWriterIds, true)) {
                $writers[] = ['tmdb_person_id' => $member['id'], 'name' => $member['name']];
                $seenWriterIds[] = $member['id'];
            }
        }

        // Sort the cast by TMDB's billing 'order' (0 = top billing) and keep the first four.
        // usort sorts a list with a comparison function; <=> gives -1/0/1 for less/equal/greater.
        $castList = $data['credits']['cast'] ?? [];
        usort($castList, fn(array $a, array $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        $cast = array_map(
            fn(array $c) => ['tmdb_person_id' => $c['id'], 'name' => $c['name']],
            array_slice($castList, 0, 4)
        );

        // Our field names on the left, TMDB's on the right. Only the first two genres are kept.
        // array_column(list, 'name') pulls just the 'name' out of each entry.
        return [
            'tmdb_id' => $data['id'],
            'title' => $data['title'] ?? '',
            'description' => $data['overview'] ?? '',
            'duration_minutes' => $data['runtime'] ?? null,
            'release_year' => $data['release_date'] ? (int) substr($data['release_date'], 0, 4) : null,
            'poster_url' => $data['poster_path'] ? self::IMAGE_BASE_URL . $data['poster_path'] : null,
            'genres' => array_column(array_slice($data['genres'] ?? [], 0, 2), 'name'),
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

        // http_build_query turns ['query' => 'alien'] into "query=alien" for the address.
        $url = self::BASE_URL . $path . '?' . http_build_query($query);

        // cURL settings: give me the response as text (instead of printing it), send the token
        // in an Authorization header, ask for JSON, and give up after 10 seconds.
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

        // Three things can go wrong: no connection at all, a non-200 status (e.g. 401 bad token,
        // 404 unknown movie), or a body that isn't valid JSON. Each becomes a readable error
        // that the admin page shows as a message.
        if ($response === false) {
            throw new RuntimeException('TMDB request failed: ' . $error);
        }

        if ($status !== 200) {
            throw new RuntimeException('TMDB request returned HTTP ' . $status . ': ' . $response);
        }

        // json_decode(..., true) turns JSON text into PHP lists (true = lists, not objects).
        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('TMDB returned an unexpected response.');
        }

        return $decoded;
    }
}
