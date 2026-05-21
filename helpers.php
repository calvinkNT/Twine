<?php

function ok(mixed $data): void {
    echo json_encode(['code' => '', 'data' => $data, 'success' => true, 'error' => '']);
    exit;
}

function ok_paged(array $records, int $page, int $size = 15): void {
    echo json_encode([
        'code'    => '',
        'data'    => [
            'count'        => count($records),
            'records'      => $records,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'backAnchor'   => -1,
            'anchor'       => 0,
            'nextPage'     => count($records) === $size ? $page + 1 : null,
        ],
        'size'    => $size,
        'success' => true,
        'error'   => '',
    ]);
    exit;
}

function fail(int $code, string $msg, int $http = 400): never {
    http_response_code($http);
    echo json_encode(['code' => $code, 'data' => (object)[], 'success' => false, 'error' => $msg]);
    exit;
}

function auth(): array {
    $token = $_SERVER['HTTP_VINE_SESSION_ID'] ?? '';
    if (!$token) fail(103, 'Not authenticated', 401);
    $user = q('SELECT * FROM users WHERE token = ?', [$token])->fetch();
    if (!$user) fail(103, 'Not authenticated', 401);
    return $user;
}

function base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function snowflake(): int {
    static $seq = 0;
    $ms  = (int)(microtime(true) * 1000) - 1388534400000;
    $seq = ($seq + 1) & 0xFFF;
    return ($ms << 22) | (1 << 12) | $seq;
}

function page_offset(): array {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $size = min(100, max(1, (int)($_GET['size'] ?? 15)));
    return [$page, $size, ($page - 1) * $size];
}

function user_row(int $id): array|false {
    return q('SELECT * FROM users WHERE id = ?', [$id])->fetch();
}

function make_thumb(string $video_path): string {
    if (!file_exists($video_path)) return '';
    $dir  = UPLOAD_DIR . '/thumbnails';
    @mkdir($dir, 0755, true);
    $file = pathinfo($video_path, PATHINFO_FILENAME) . '.jpg';
    $path = $dir . '/' . $file;
    exec('ffmpeg -y -i ' . escapeshellarg($video_path) . ' -vframes 1 ' . escapeshellarg($path) . ' 2>/dev/null');
    if (!file_exists($path) || filesize($path) === 0) return '';
    return base_url() . '/dynamic/thumbnails/' . $file;
}

function extract_tags(string $text): array {
    preg_match_all('/#(\w+)/u', $text, $m);
    return array_values(array_unique(array_map('strtolower', $m[1])));
}

function save_tags(int $post_id, string $description): void {
    foreach (extract_tags($description) as $tag) {
        q('INSERT OR IGNORE INTO tags (tag) VALUES (?)', [$tag]);
        $tagId = q('SELECT id FROM tags WHERE tag = ?', [$tag])->fetchColumn();
        q('INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (?,?)', [$post_id, $tagId]);
    }
}

function notify(int $source, int $target, int $type, ?int $post_id = null): void {
    if ($source === $target) return;
    q('DELETE FROM activity WHERE source_user=? AND target_user=? AND type_id=? AND (post_id IS ? OR post_id=?)',
      [$source, $target, $type, $post_id, $post_id]);
    q('INSERT INTO activity (source_user, target_user, type_id, post_id) VALUES (?,?,?,?)',
      [$source, $target, $type, $post_id]);
}

function comment_records(int $postId, int $page, int $size): array {
    $off  = ($page - 1) * $size;
    $rows = q('SELECT c.id, c.comment, c.created_at,
                      u.id AS user_id, u.username, u.avatar_url, u.verified,
                      u.location, u.description, u.explicit
               FROM comments c JOIN users u ON u.id=c.user_id
               WHERE c.post_id=? ORDER BY c.created_at ASC LIMIT ? OFFSET ?',
              [$postId, $size, $off])->fetchAll();
    return array_map(fn($r) => [
        'commentId' => (int)$r['id'],
        'postId'    => $postId,
        'userId'    => (int)$r['user_id'],
        'username'  => $r['username'],
        'avatarUrl' => $r['avatar_url'] ?: null,
        'verified'  => (int)$r['verified'],
        'location'  => $r['location'],
        'comment'   => $r['comment'],
        'entities'  => [],
        'created'   => $r['created_at'],
        'user'      => [
            'username'        => $r['username'],
            'verified'        => (int)$r['verified'],
            'description'     => $r['description'],
            'avatarUrl'       => $r['avatar_url'] ?: null,
            'userId'          => (int)$r['user_id'],
            'location'        => $r['location'],
            'explicitContent' => (int)$r['explicit'],
        ],
    ], $rows);
}

function ok_comments(array $records, int $page, int $size): void {
    $count = count($records);
    echo json_encode([
        'code'    => '',
        'data'    => [
            'count'        => $count,
            'records'      => $records,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'backAnchor'   => -1,
            'anchor'       => 0,
            'nextPage'     => $count === $size ? $page + 1 : null,
            'size'         => $count,
            'page'         => $page,
        ],
        'success' => true,
        'page'    => $page,
        'size'    => $count,
    ]);
    exit;
}
