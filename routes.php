<?php

if ($path === '') {
    header("Content-Type: text/html");
    require_once "inst.php";
}

// PUT /upload/videos/:filename
else if ($method === 'PUT' && $parts[0] === 'upload' && ($parts[1] ?? '') === 'videos' && isset($parts[2])) {
    $me  = auth();
    $dir = UPLOAD_DIR . '/videos';
    @mkdir($dir, 0755, true);

    $postId = snowflake();
    $fpath  = $dir . '/' . $postId . '.mp4';

    if (file_put_contents($fpath, $body) === false || filesize($fpath) === 0) {
        fail(104, 'Failed to save video');
    }

    $videoUrl = base_url() . '/dynamic/videos/' . $postId . '.mp4';

    // Move
    q('INSERT INTO posts (id, user_id, video_url, thumbnail_url) VALUES (?,?,?,?)',
      [$postId, $me['id'], $videoUrl, '']);
    q('UPDATE users SET post_count=post_count+1, authored_post_count=authored_post_count+1 WHERE id=?',
      [$me['id']]);

    header('X-Upload-Key: ' . $videoUrl);
    ok((object)[]);
}

// PUT /upload/avatars/:filename
elseif ($method === 'PUT' && $parts[0] === 'upload' && ($parts[1] ?? '') === 'avatars' && isset($parts[2])) {
    $me  = auth();
    $dir = UPLOAD_DIR . '/avatars';
    @mkdir($dir, 0755, true);

    $name  = $me['id'] . '_' . basename($parts[2]);
    $fpath = $dir . '/' . $name;

    if (file_put_contents($fpath, $body) === false) fail(104, 'Failed to save avatar');

    $url = base_url() . '/dynamic/avatars/' . $name;
    q('UPDATE users SET avatar_url=? WHERE id=?', [$url, $me['id']]);

    header('X-Upload-Key: ' . $url);
    ok((object)[]);
}

// PUT /upload/thumbs/:filename
// vine thumbnail
elseif ($method === 'PUT' && $parts[0] === 'upload' && ($parts[1] ?? '') === 'thumbs' && isset($parts[2])) {
    auth();
    $dir = UPLOAD_DIR . '/thumbnails';
    @mkdir($dir, 0755, true);

    $url = base_url() . '/autoGenThumb';
    if (strlen($body) > 0) {
        $filename = uniqid() . '_' . basename($parts[2]);
        $path     = $dir . '/' . $filename;
        if (file_put_contents($path, $body) !== false) {
            $url = base_url() . '/dynamic/thumbnails/' . $filename;
        }
    }

    header('X-Upload-Key: ' . $url);
    ok((object)[]);
}

// POST /users
// register
elseif ($method === 'POST' && $path === 'users') {
    $username = trim($input['username'] ?? '');
    $email    = trim($input['email']    ?? '');
    $password =      $input['password'] ?? '';

    if (!$username || !$email || !$password) fail(105, 'username, email, and password required');

    $token = bin2hex(random_bytes(16));
    try {
        q('INSERT INTO users (username, email, password_hash, token) VALUES (?,?,?,?)',
          [$username, $email, password_hash($password, PASSWORD_DEFAULT), $token]);
        $id = (int)db()->lastInsertId();
    } catch (PDOException) {
        fail(101, 'Email already registered', 401);
    }
    ok(['username' => $username, 'userId' => $id, 'key' => $token]);
}

// POST /users/authenticate 
// login
elseif ($method === 'POST' && $path === 'users/authenticate') {
    $email    = trim($input['username'] ?? '');
    $password =      $input['password'] ?? '';

    $user = q('SELECT * FROM users WHERE email=?', [$email])->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        fail(101, 'Invalid credentials', 401);
    }
    ok(['username' => $user['username'], 'userId' => (int)$user['id'],
        'avatarUrl' => $user['avatar_url'], 'key' => $user['token']]);
}

// DELETE /users/authenticate
// logout
elseif ($method === 'DELETE' && $path === 'users/authenticate') {
    $me = auth();
    q('UPDATE users SET token=? WHERE id=?', [bin2hex(random_bytes(16)), $me['id']]);
    ok((object)[]);
}

// GET /users/me
// own profile
elseif ($method === 'GET' && $path === 'users/me') {
    $me = auth();
    ok(profile_object($me, (int)$me['id'], true));
}

// PUT /users/:id
// update profile
elseif (in_array($method, ['PUT','POST']) && $parts[0] === 'users'
        && isset($parts[1]) && is_numeric($parts[1]) && !isset($parts[2])) {
    $me = auth();
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);
    update_profile((int)$me['id'], $input);
    ok(profile_object(user_row((int)$me['id']), (int)$me['id'], true));
}

// PUT /users/:id/preferences
// extra settings
elseif ($method === 'PUT' && $parts[0] === 'users' && isset($parts[1])
        && is_numeric($parts[1]) && ($parts[2] ?? '') === 'preferences') {
    $me = auth();
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);
    update_profile((int)$me['id'], $input);
    ok(profile_object(user_row((int)$me['id']), (int)$me['id'], true));
}

// PUT /users/:id/following/suggested/contacts
// address book sync stub (not implemented)
elseif ($method === 'PUT' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'following' && ($parts[3] ?? '') === 'suggested'
        && ($parts[4] ?? '') === 'contacts') {
    //auth();
    //ok((object)[]);
}

// GET /users/search/:query
// search for usernames
elseif ($method === 'GET' && $parts[0] === 'users' && ($parts[1] ?? '') === 'search' && isset($parts[2])) {
    $me = auth();
    [$page, $size, $off] = page_offset();
    $like = '%' . str_replace(['%','_'], ['\\%','\\_'], $parts[2]) . '%';
    $rows = q("SELECT * FROM users WHERE username LIKE ? ESCAPE '\\' ORDER BY username LIMIT ? OFFSET ?",
              [$like, $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// GET /users/profiles/:id
// view custom profile
elseif ($method === 'GET' && $parts[0] === 'users' && ($parts[1] ?? '') === 'profiles' && isset($parts[2])) {
    $me   = auth();
    $user = q('SELECT * FROM users WHERE id=?', [(int)$parts[2]])->fetch();
    if (!$user) fail(900, 'User not found', 404);
    ok(profile_object($user, (int)$me['id'], (int)$user['id'] === (int)$me['id']));
}

// POST /users/:id/explicit / DELETE /users/:id/explicit
// set or unset "sensitive" account type.
elseif ($parts[0] === 'users' && isset($parts[1]) && is_numeric($parts[1]) && ($parts[2] ?? '') === 'explicit') {
    $me = auth();
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);
    q('UPDATE users SET explicit=? WHERE id=?', [$method === 'POST' ? 1 : 0, $me['id']]);
    ok(profile_object(user_row((int)$me['id']), (int)$me['id'], true));
}

// GET /users/:id/pendingNotificationsCount
elseif ($method === 'GET' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'pendingNotificationsCount') {
    $me = auth();
    ok((int)q('SELECT COUNT(*) FROM activity WHERE target_user=? AND is_read=0', [$me['id']])->fetchColumn());
}

// GET /users/:id/notifications/followRequests
/*elseif ($method === 'GET' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[3] ?? '') === 'followRequests') {
    $me = auth();
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);
    [$page, $size, $off] = page_offset();
    $rows = q('SELECT u.* FROM users u JOIN follow_requests fr ON fr.requester_id=u.id
               WHERE fr.target_id=? ORDER BY fr.created_at DESC LIMIT ? OFFSET ?',
              [$me['id'], $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}*/

// GET /users/:id/notifications
elseif ($method === 'GET' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'notifications') {
    $me = auth();
    [$page, $size, $off] = page_offset();

    $rows = q('SELECT a.*, u.username, u.avatar_url, u.id AS source_id
               FROM activity a JOIN users u ON u.id=a.source_user
               WHERE a.target_user=? ORDER BY a.created_at DESC LIMIT ? OFFSET ?',
              [$me['id'], $size, $off])->fetchAll();

    $bodies = [
        1 => 'is now following you!', 2 => 'liked your post',
        3 => 'commented on your post', 4 => 'reposted your post',
        5 => 'is now following you!', 6 => null,
        7 => 'mentioned you in a post', 8 => 'mentioned you in the comments of a post',
    ];

    $records = [];
    foreach ($rows as $row) {
        $uid  = (int)$row['source_id'];
        $tmpl = $bodies[$row['type_id']] ?? '';
        $body_text = $row['type_id'] === 6
            ? 'A moderation action was taken against your account.'
            : "<: user | vine://user-id/$uid :>{$row['username']}<:> $tmpl";

        $thumb = null;
        $pid   = null;
        if ($row['post_id']) {
            $post = q('SELECT thumbnail_url FROM posts WHERE id=?', [$row['post_id']])->fetch();
            if ($post) { $pid = (int)$row['post_id']; $thumb = $post['thumbnail_url'] ?: null; }
        }

        $records[] = [
            'body'               => $body_text,
            'userId'             => $uid,
            'postId'             => $pid,
            'thumbnailUrl'       => $thumb,
            'username'           => $row['username'],
            'avatarUrl'          => $row['avatar_url'],
            'notificationTypeId' => (int)$row['type_id'],
            'created'            => $row['created_at'],
        ];
    }

    q('UPDATE activity SET is_read=1 WHERE target_user=? AND is_read=0', [$me['id']]);
    ok_paged($records, $page, $size);
}

// POST /users/:id/followRequests/:requesterId
// approve follow req
elseif ($method === 'POST' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'followRequests' && isset($parts[3])) {
    $me          = auth();
    $requesterId = (int)$parts[3];
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);

    if ((int)q('SELECT COUNT(*) FROM follow_requests WHERE requester_id=? AND target_id=?',
               [$requesterId, $me['id']])->fetchColumn() === 0) {
        fail(900, 'Follow request not found', 404);
    }

    q('DELETE FROM follow_requests WHERE requester_id=? AND target_id=?', [$requesterId, $me['id']]);
    q('INSERT OR IGNORE INTO follows (follower_id, following_id) VALUES (?,?)', [$requesterId, $me['id']]);
    q('UPDATE users SET following_count=following_count+1 WHERE id=?', [$requesterId]);
    q('UPDATE users SET follower_count=follower_count+1  WHERE id=?', [$me['id']]);
    ok((object)[]);
}

// DELETE /users/:id/followRequests/:requesterId 
// deny follow req
elseif ($method === 'DELETE' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'followRequests' && isset($parts[3])) {
    $me = auth();
    if ((int)$parts[1] !== (int)$me['id']) fail(103, 'Forbidden', 403);
    q('DELETE FROM follow_requests WHERE requester_id=? AND target_id=?', [(int)$parts[3], $me['id']]);
    ok((object)[]);
}

// GET /users/:id/followers
// who's following user Id
elseif ($method === 'GET' && $parts[0] === 'users' && isset($parts[1])
        && is_numeric($parts[1]) && ($parts[2] ?? '') === 'followers') {
    $me = auth();
    [$page, $size, $off] = page_offset();
    $rows = q('SELECT u.* FROM users u JOIN follows f ON f.follower_id=u.id
               WHERE f.following_id=? LIMIT ? OFFSET ?', [(int)$parts[1], $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// GET /users/:id/following
// who is user id following
elseif ($method === 'GET' && $parts[0] === 'users' && isset($parts[1])
        && is_numeric($parts[1]) && ($parts[2] ?? '') === 'following') {
    $me = auth();
    [$page, $size, $off] = page_offset();
    $rows = q('SELECT u.* FROM users u JOIN follows f ON f.following_id=u.id
               WHERE f.follower_id=? LIMIT ? OFFSET ?', [(int)$parts[1], $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// POST /users/:id/followers — follow (or send request if private account)
elseif ($method === 'POST' && $parts[0] === 'users' && isset($parts[1])
        && is_numeric($parts[1]) && ($parts[2] ?? '') === 'followers') {
    $me       = auth();
    $targetId = (int)$parts[1];
    if ($targetId === (int)$me['id']) fail(105, 'Cannot follow yourself');

    $target = q('SELECT * FROM users WHERE id=?', [$targetId])->fetch();
    if (!$target) fail(900, 'User not found', 404);

    // Already following — idempotent
    if ((int)q('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?',
               [$me['id'], $targetId])->fetchColumn() > 0) {
        ok((object)[]);
    }

    //if ($target['private_account']) {
    //    q('INSERT OR IGNORE INTO follow_requests (requester_id, target_id) VALUES (?,?)', [$me['id'], $targetId]);
    //    notify((int)$me['id'], $targetId, 1);
    //} else {
        q('DELETE FROM follow_requests WHERE requester_id=? AND target_id=?', [$me['id'], $targetId]);
        q('INSERT INTO follows (follower_id, following_id) VALUES (?,?)', [$me['id'], $targetId]);
        q('UPDATE users SET following_count=following_count+1 WHERE id=?', [$me['id']]);
        q('UPDATE users SET follower_count=follower_count+1  WHERE id=?', [$targetId]);
        notify((int)$me['id'], $targetId, 1);
    //}
    ok((object)[]);
}

// POST /users/:id/blocked/:targetId
// block
elseif ($method === 'POST' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'blocked' && isset($parts[3])) {
    $me        = auth();
    $blockedId = (int)$parts[3];
    if ($blockedId === (int)$me['id']) fail(105, 'Cannot block yourself');
    if (!q('SELECT id FROM users WHERE id=?', [$blockedId])->fetchColumn()) fail(900, 'User not found', 404);

    q('INSERT OR IGNORE INTO blocks (blocker_id, blocked_id) VALUES (?,?)', [$me['id'], $blockedId]);

    // Remove follows in both directions, keeping counts accurate
    foreach ([[$me['id'], $blockedId], [$blockedId, $me['id']]] as [$follower, $followee]) {
        if ((int)q('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?',
                   [$follower, $followee])->fetchColumn() > 0) {
            q('DELETE FROM follows WHERE follower_id=? AND following_id=?', [$follower, $followee]);
            q('UPDATE users SET following_count=MAX(0,following_count-1) WHERE id=?', [$follower]);
            q('UPDATE users SET follower_count=MAX(0,follower_count-1)   WHERE id=?', [$followee]);
        }
    }
    ok((object)[]);
}

// DELETE /users/:id/blocked/:targetId 
// unblock
elseif ($method === 'DELETE' && $parts[0] === 'users' && isset($parts[1])
        && ($parts[2] ?? '') === 'blocked' && isset($parts[3])) {
    $me = auth();
    q('DELETE FROM blocks WHERE blocker_id=? AND blocked_id=?', [$me['id'], (int)$parts[3]]);
    ok((object)[]);
}

// POST /users/:id/complaints 
// report a user
elseif ($method === 'POST' && $parts[0] === 'users' && isset($parts[1])
        && is_numeric($parts[1]) && ($parts[2] ?? '') === 'complaints') {
    $me = auth();
    q('INSERT INTO complaints (reporter_id, target_type, target_id) VALUES (?,?,?)',
      [$me['id'], 'user', (int)$parts[1]]);
    ok((object)[]);
}

// GET /timelines/graph 
// home feed
elseif ($method === 'GET' && $path === 'timelines/graph') {
    $me = auth();
    [$page, $size, $off] = page_offset();

    $rows = q("SELECT p.* FROM posts p
               WHERE (p.user_id=? OR p.user_id IN (SELECT following_id FROM follows WHERE follower_id=?))
                 AND p.user_id NOT IN (SELECT blocked_id FROM blocks WHERE blocker_id=?)
                 AND p.user_id NOT IN (SELECT blocker_id FROM blocks WHERE blocked_id=?)
               ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
              [$me['id'], $me['id'], $me['id'], $me['id'], $size, $off])->fetchAll();

    ok_paged(array_map(fn($p) => post_object($p, (int)$me['id']), $rows), $page, $size);
}

// GET /timelines/posts/:postId 
// single post
elseif ($method === 'GET' && $parts[0] === 'timelines' && ($parts[1] ?? '') === 'posts' && isset($parts[2])) {
    $me   = auth();
    $post = q('SELECT * FROM posts WHERE id=?', [$parts[2]])->fetch();
    if (!$post) fail(900, 'Post not found', 404);
    ok_paged([post_object($post, (int)$me['id'])], 1);
}

// GET /timelines/users/:userId 
// a user's own posts
elseif ($method === 'GET' && $parts[0] === 'timelines' && ($parts[1] ?? '') === 'users'
        && isset($parts[2]) && !isset($parts[3])) {
    $me = auth();
    [$page, $size, $off] = page_offset();
    $rows = q('SELECT * FROM posts WHERE user_id=? ORDER BY created_at DESC LIMIT ? OFFSET ?',
              [(int)$parts[2], $size, $off])->fetchAll();
    ok_paged(array_map(fn($p) => post_object($p, (int)$me['id']), $rows), $page, $size);
}

// GET /timelines/users/:userId/likes 
// posts liked by a user
elseif ($method === 'GET' && $parts[0] === 'timelines' && ($parts[1] ?? '') === 'users'
        && isset($parts[2]) && ($parts[3] ?? '') === 'likes') {
    $me = auth();
    [$page, $size, $off] = page_offset();
    $rows = q('SELECT p.* FROM posts p JOIN likes l ON l.post_id=p.id AND l.user_id=?
               ORDER BY l.created_at DESC LIMIT ? OFFSET ?',
              [(int)$parts[2], $size, $off])->fetchAll();
    ok_paged(array_map(fn($p) => post_object($p, (int)$me['id']), $rows), $page, $size);
}

// GET /timelines/tags/:tag
// view all vines from tag
elseif ($method === 'GET' && $parts[0] === 'timelines' && ($parts[1] ?? '') === 'tags' && isset($parts[2])) {
    $me  = auth();
    [$page, $size, $off] = page_offset();
    $tag = strtolower($parts[2]);
    $rows = q('SELECT p.* FROM posts p JOIN post_tags pt ON pt.post_id=p.id JOIN tags t ON t.id=pt.tag_id
               WHERE t.tag=? ORDER BY p.created_at DESC LIMIT ? OFFSET ?',
              [$tag, $size, $off])->fetchAll();
    if (!$rows && $page === 1) fail(900, 'Tag not found', 404);
    ok_paged(array_map(fn($p) => post_object($p, (int)$me['id']), $rows), $page, $size);
}

// POST /posts 
// finalise a post after video upload
elseif ($method === 'POST' && $path === 'posts') {
    $me           = auth();
    $videoUrl     = trim($input['videoUrlWebm'] ?? $input['videoUrl'] ?? '');
    $thumbnailUrl = trim($input['thumbnailUrl'] ?? '');
    $description  = trim($input['description']  ?? '');
    $channelId    = (int)($input['channelId']   ?? 0);
    $venueId      = $input['foursquareVenueId'] ?? null;
    $venueName    = $input['venueName']         ?? null;

    if (!$videoUrl) fail(105, 'videoUrl required');

    $existingId = q('SELECT id FROM posts WHERE video_url=? AND user_id=? ORDER BY created_at DESC LIMIT 1',
                    [$videoUrl, $me['id']])->fetchColumn();

    if (!$thumbnailUrl) {
        $localPath    = BASE . parse_url($videoUrl, PHP_URL_PATH);
        $thumbnailUrl = make_thumb($localPath);
    }

    if ($existingId) {
        q('UPDATE posts SET description=?, thumbnail_url=?, channel_id=?, foursquare_venue_id=?, venue_name=?
           WHERE id=? AND user_id=?',
          [$description, $thumbnailUrl, $channelId, $venueId, $venueName, $existingId, $me['id']]);
        save_tags($existingId, $description);
    } else {
        $postId = snowflake();
        q('INSERT INTO posts (id, user_id, description, video_url, thumbnail_url, channel_id, foursquare_venue_id, venue_name)
           VALUES (?,?,?,?,?,?,?,?)',
          [$postId, $me['id'], $description, $videoUrl, $thumbnailUrl, $channelId, $venueId, $venueName]);
        q('UPDATE users SET post_count=post_count+1, authored_post_count=authored_post_count+1 WHERE id=?',
          [$me['id']]);
        save_tags($postId, $description);
    }

    ok((object)[]);
}

// DELETE /posts/:id 
// delete own post and all associated data
elseif ($method === 'DELETE' && $parts[0] === 'posts' && isset($parts[1]) && !isset($parts[2])) {
    $me     = auth();
    $postId = $parts[1];

    $post = q('SELECT * FROM posts WHERE id=? AND user_id=?', [$postId, $me['id']])->fetch();
    if (!$post) fail(900, 'Post not found or not yours', 404);

    foreach (['video_url' => 'videos', 'thumbnail_url' => 'thumbnails'] as $col => $subdir) {
        if (!empty($post[$col])) {
            $fp = UPLOAD_DIR . '/' . $subdir . '/' . basename(parse_url($post[$col], PHP_URL_PATH));
            if (file_exists($fp)) @unlink($fp);
        }
    }

    $likers = q('SELECT user_id FROM likes WHERE post_id=?', [$postId])->fetchAll(PDO::FETCH_COLUMN);
    foreach ($likers as $likerId) {
        q('UPDATE users SET like_count=MAX(0,like_count-1) WHERE id=?', [$likerId]);
    }

    q('DELETE FROM reposts   WHERE post_id=?', [$postId]);
    q('DELETE FROM post_tags WHERE post_id=?', [$postId]);
    q('DELETE FROM likes     WHERE post_id=?', [$postId]);
    q('DELETE FROM comments  WHERE post_id=?', [$postId]);
    q('DELETE FROM activity  WHERE post_id=?', [$postId]);
    q('DELETE FROM posts     WHERE id=?',      [$postId]);
    q('UPDATE users SET post_count=MAX(0,post_count-1), authored_post_count=MAX(0,authored_post_count-1) WHERE id=?',
      [$me['id']]);

    ok((object)[]);
}

// GET /posts/:id/likes 
// who liked a post
elseif ($method === 'GET' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'likes') {
    $me = auth();
    [$page, $size, $off] = page_offset();
    if (!q('SELECT id FROM posts WHERE id=?', [$parts[1]])->fetchColumn()) fail(900, 'Post not found', 404);
    $rows = q('SELECT u.* FROM users u JOIN likes l ON l.user_id=u.id
               WHERE l.post_id=? ORDER BY l.created_at DESC LIMIT ? OFFSET ?',
              [$parts[1], $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// POST /posts/:id/likes
// like a post, ret updated likers list
elseif ($method === 'POST' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'likes') {
    $me     = auth();
    $postId = $parts[1];

    $post = q('SELECT user_id FROM posts WHERE id=?', [$postId])->fetch();
    if (!$post) fail(900, 'Post not found', 404);

    if ((int)q('SELECT COUNT(*) FROM likes WHERE user_id=? AND post_id=?', [$me['id'], $postId])->fetchColumn() === 0) {
        q('INSERT INTO likes (user_id, post_id) VALUES (?,?)', [$me['id'], $postId]);
        q('UPDATE posts SET like_count=like_count+1 WHERE id=?', [$postId]);
        q('UPDATE users SET like_count=like_count+1 WHERE id=?', [$me['id']]);
        notify((int)$me['id'], (int)$post['user_id'], 2, (int)$postId);
    }

    [$page, $size, $off] = page_offset();
    $rows = q('SELECT u.* FROM users u JOIN likes l ON l.user_id=u.id
               WHERE l.post_id=? ORDER BY l.created_at DESC LIMIT ? OFFSET ?',
              [$postId, $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// DELETE /posts/:id/likes
// unlike, ret updated likers list
elseif ($method === 'DELETE' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'likes') {
    $me     = auth();
    $postId = $parts[1];

    if ((int)q('SELECT COUNT(*) FROM likes WHERE user_id=? AND post_id=?', [$me['id'], $postId])->fetchColumn() > 0) {
        q('DELETE FROM likes WHERE user_id=? AND post_id=?', [$me['id'], $postId]);
        q('UPDATE posts SET like_count=MAX(0,like_count-1) WHERE id=?', [$postId]);
        q('UPDATE users SET like_count=MAX(0,like_count-1) WHERE id=?', [$me['id']]);
    }

    [$page, $size, $off] = page_offset();
    $rows = q('SELECT u.* FROM users u JOIN likes l ON l.user_id=u.id
               WHERE l.post_id=? ORDER BY l.created_at DESC LIMIT ? OFFSET ?',
              [$postId, $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// GET /posts/:id/comments
// list of comments under a post
elseif ($method === 'GET' && $parts[0] === 'posts' && isset($parts[1])
        && ($parts[2] ?? '') === 'comments' && !isset($parts[3])) {
    $me     = auth();
    $postId = (int)$parts[1];
    if (!q('SELECT id FROM posts WHERE id=?', [$postId])->fetchColumn()) fail(900, 'Post not found', 404);
    [$page, $size] = page_offset();
    ok_comments(comment_records($postId, $page, $size), $page, $size);
}

// POST /posts/:id/comments
// add a comment, ret updated list
elseif ($method === 'POST' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'comments') {
    $me      = auth();
    $postId  = (int)$parts[1];
    $comment = trim($input['comment'] ?? '');
    if (!$comment) fail(105, 'comment required');

    $post = q('SELECT user_id FROM posts WHERE id=?', [$postId])->fetch();
    if (!$post) fail(900, 'Post not found', 404);

    q('INSERT INTO comments (post_id, user_id, comment) VALUES (?,?,?)', [$postId, $me['id'], $comment]);
    q('UPDATE posts SET comment_count=comment_count+1 WHERE id=?', [$postId]);
    notify((int)$me['id'], (int)$post['user_id'], 3, (int)$postId);

    [$page, $size] = page_offset();
    ok_comments(comment_records($postId, $page, $size), $page, $size);
}

// DELETE /posts/:postId/comments/:commentId
// delete own comment, ret updated list
elseif ($method === 'DELETE' && $parts[0] === 'posts' && isset($parts[1])
        && ($parts[2] ?? '') === 'comments' && isset($parts[3])) {
    $me        = auth();
    $postId    = (int)$parts[1];
    $commentId = (int)$parts[3];

    $comment = q('SELECT * FROM comments WHERE id=? AND user_id=?', [$commentId, $me['id']])->fetch();
    if (!$comment) fail(900, 'Comment not found or not yours', 404);

    q('DELETE FROM comments WHERE id=?', [$commentId]);
    q('UPDATE posts SET comment_count=MAX(0,comment_count-1) WHERE id=?', [$postId]);

    [$page, $size] = page_offset();
    ok_comments(comment_records($postId, $page, $size), $page, $size);
}

// GET /posts/:id/reposts
// list users who reposted
elseif ($method === 'GET' && $parts[0] === 'posts' && isset($parts[1])
        && ($parts[2] ?? '') === 'reposts' && !isset($parts[3])) {
    $me     = auth();
    $postId = (int)$parts[1];
    if (!q('SELECT id FROM posts WHERE id=?', [$postId])->fetchColumn()) fail(900, 'Post not found', 404);
    [$page, $size, $off] = page_offset();

    $rows = q('SELECT u.* FROM users u JOIN reposts r ON r.user_id=u.id
               WHERE r.post_id=? ORDER BY r.created_at DESC LIMIT ? OFFSET ?',
              [$postId, $size, $off])->fetchAll();
    ok_paged(array_map(fn($u) => user_stub($u, (int)$me['id']), $rows), $page, $size);
}

// POST /posts/:id/repost 
// revine
elseif ($method === 'POST' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'repost') {
    $me     = auth();
    $origId = (int)$parts[1];

    $orig = q('SELECT * FROM posts WHERE id=?', [$origId])->fetch();
    if (!$orig) fail(900, 'Post not found', 404);

    if ((int)q('SELECT COUNT(*) FROM reposts WHERE user_id=? AND post_id=?', [$me['id'], $origId])->fetchColumn() === 0) {
        q('INSERT INTO reposts (user_id, post_id) VALUES (?,?)', [$me['id'], $origId]);
        q('UPDATE posts SET repost_count=repost_count+1 WHERE id=?', [$origId]);
        notify((int)$me['id'], (int)$orig['user_id'], 4, (int)$origId);
    }

    ok([]);
}

// DELETE /posts/:origId/repost / /posts/:origId/repost/:repostId 
// un-revine
elseif ($method === 'DELETE' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'repost') {
    $me     = auth();
    $origId = (int)$parts[1];

    if ((int)q('SELECT COUNT(*) FROM reposts WHERE user_id=? AND post_id=?', [$me['id'], $origId])->fetchColumn() > 0) {
        q('DELETE FROM reposts WHERE user_id=? AND post_id=?', [$me['id'], $origId]);
        q('UPDATE posts SET repost_count=MAX(0,repost_count-1) WHERE id=?', [$origId]);
    }

    ok([]);
}

// POST /posts/:id/complaints
//  report a post
elseif ($method === 'POST' && $parts[0] === 'posts' && isset($parts[1]) && ($parts[2] ?? '') === 'complaints') {
    $me = auth();
    q('INSERT INTO complaints (reporter_id, target_type, target_id) VALUES (?,?,?)',
      [$me['id'], 'post', (int)$parts[1]]);
    ok((object)[]);
}

// GET /channels/featured 
// all channels; only returns records on page 1
elseif ($method === 'GET' && $path === 'channels/featured') {
    auth();
    [$page] = page_offset();
    $rows   = $page === 1 ? q('SELECT * FROM channels ORDER BY id')->fetchAll() : [];
    $records = array_map(fn($c) => [
        'channelId'     => (int)$c['id'],
        'channel'       => $c['name'],
        'iconUrl'       => 'channels/' . $c['slug'] . '@2x.png',
        'retinaIconUrl' => 'channels/' . $c['slug'] . '@2x.png'
    ], $rows);

    echo json_encode([
        'code'         => '',
        'data'         => ['count' => count($records), 'anchorStr' => '0', 'records' => $records],
        'previousPage' => $page > 1 ? $page - 1 : null,
        'backAnchor'   => '',
        'anchor'       => '0',
        'nextPage'     => null,
        'size'         => count($records),
        'success'      => true,
        'error'        => '',
    ]);
    exit;
}

// GET /timelines/channels/:channelId/recent|popular
elseif ($method === 'GET' && $parts[0] === 'timelines' && ($parts[1] ?? '') === 'channels'
        && isset($parts[2]) && isset($parts[3])) {
    $me        = auth();
    $channelId = (int)$parts[2];
    $area      = $parts[3];
    [$page, $size, $off] = page_offset();

    if (!q('SELECT id FROM channels WHERE id=?', [$channelId])->fetchColumn()) fail(900, 'Channel not found', 404);

    $order = $area === 'popular' ? 'p.like_count DESC, p.loop_count DESC' : 'p.created_at DESC';
    $rows  = q("SELECT p.* FROM posts p
                WHERE p.channel_id=?
                  AND p.user_id NOT IN (SELECT blocked_id FROM blocks WHERE blocker_id=?)
                  AND p.user_id NOT IN (SELECT blocker_id FROM blocks WHERE blocked_id=?)
                ORDER BY $order LIMIT ? OFFSET ?",
               [$channelId, $me['id'], $me['id'], $size, $off])->fetchAll();

    ok_paged(array_map(fn($p) => post_object($p, (int)$me['id']), $rows), $page, $size);
}

// GET /explore/v2 or v3
elseif ($parts[0] === 'explore' && in_array($parts[1] ?? '', ['v2', 'v3'])) {
    try {
        $trending = q("
            SELECT t.tag, COUNT(pt.post_id) AS post_count
            FROM tags t JOIN post_tags pt ON pt.tag_id = t.id
            JOIN posts p ON p.id = pt.post_id
            WHERE p.created_at >= strftime('%Y-%m-%dT%H:%M:%f', 'now', '-7 days')
            GROUP BY t.id ORDER BY post_count DESC LIMIT 20
        ")->fetchAll();
    } catch (Exception) {
        $trending = [];
    }

    header('Content-Type: text/html');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Explore</title>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    </head>
    <body>
    <div class="container" style="max-width:420px;margin-top:1em;">
      <div class="well">

        <a href="vine://popular-now" class="btn btn-warning btn-lg btn-block" style="margin-top:10px;">
          <span class="glyphicon glyphicon-thumbs-up"></span> Popular Now &raquo;
        </a>
        <a href="vine://trending-people" class="btn btn-danger btn-lg btn-block" style="margin-top:10px;">
          <span class="glyphicon glyphicon-arrow-up"></span> On The Rise &raquo;
        </a>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Trending Tags</strong></div>
        <ul class="list-group">
          <?php if (empty($trending)): ?>
            <li class="list-group-item text-muted">No trending tags yet.</li>
          <?php else: ?>
            <?php foreach ($trending as $row): ?>
              <?php
                $tag   = htmlspecialchars($row['tag'], ENT_QUOTES);
                $count = (int)$row['post_count'];
                $label = $count === 1 ? '1 post' : "$count posts";
              ?>
              <li class="list-group-item">
                <a href="vine://tag/<?= $tag ?>">#<?= $tag ?></a>
                <span class="badge"><?= $label ?></span>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

else {
    http_response_code(404);
    echo json_encode(['code' => 900, 'data' => (object)[], 'success' => false, 'error' => 'Endpoint not found']);
}