<?php

function update_profile(int $id, array $data): void {
    $allowed_bg = ['0x333333','0xff5967','0xff794d','0xffaf40','0x68bf60',
                   '0x33ccbf','0x6db0f2','0x5082e5','0x7870cc','0xf279ac'];
    $fields = [];
    $vals   = [];

    foreach ([
        'username'       => 'username',
        'description'    => 'description',
        'location'       => 'location',
        'phoneNumber'    => 'phone_number',
        'avatarUrl'      => 'avatar_url',
        'secondaryColor' => 'secondary_color',
    ] as $key => $col) {
        if (array_key_exists($key, $data)) { $fields[] = "$col = ?"; $vals[] = $data[$key]; }
    }

    if (isset($data['profileBackground'])) {
        if (!in_array($data['profileBackground'], $allowed_bg, true)) fail(105, 'Invalid color');
        $fields[] = 'profile_background = ?';
        $vals[]   = $data['profileBackground'];
    }

    foreach ([
        'private'          => 'private_account',
        'explicitContent'  => 'explicit',
        'uploadHD'         => 'upload_hd',
        'notifyPosts'      => 'notify_posts',
        'repostsEnabled'   => 'reposts_enabled',
        'includePromoted'  => 'include_promoted',
    ] as $key => $col) {
        if (array_key_exists($key, $data)) { $fields[] = "$col = ?"; $vals[] = (int)(bool)$data[$key]; }
    }

    if (!$fields) return;
    $vals[] = $id;
    // Dynamic column list — still uses parameterized values, safe from injection.
    db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($vals);
}

function profile_object(array $u, int $viewer = 0, bool $private = false): array {
    $following    = 0;
    $followStatus = 0;
    $blocked      = 0;
    $blocking     = 0;

    if ($viewer && $viewer !== (int)$u['id']) {
        $following = (int)(bool)q('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?',
                                  [$viewer, $u['id']])->fetchColumn();
        if ($following) {
            $followStatus = 1;
        } else {
            $pending = (int)q('SELECT COUNT(*) FROM follow_requests WHERE requester_id=? AND target_id=?',
                              [$viewer, $u['id']])->fetchColumn();
            $followStatus = $pending > 0 ? 1 : 0;
        }
        $blocking = (int)(bool)q('SELECT COUNT(*) FROM blocks WHERE blocker_id=? AND blocked_id=?',
                                  [$viewer, $u['id']])->fetchColumn();
        $blocked  = (int)(bool)q('SELECT COUNT(*) FROM blocks WHERE blocker_id=? AND blocked_id=?',
                                  [$u['id'], $viewer])->fetchColumn();
    }

    $out = [
        'userId'             => (int)$u['id'],
        'username'           => $u['username'],
        'description'        => $u['description'],
        'avatarUrl'          => $u['avatar_url'] ?: null,
        'location'           => $u['location'],
        'verified'           => (int)$u['verified'],
        'followerCount'      => (int)$u['follower_count'],
        'followingCount'     => (int)$u['following_count'],
        'postCount'          => (int)$u['post_count'],
        'authoredPostCount'  => (int)$u['authored_post_count'],
        'likeCount'          => (int)$u['like_count'],   // posts liked BY this user
        'loopCount'          => (int)$u['loop_count'],
        'profileBackground'  => $u['profile_background'],
        'secondaryColor'     => $u['secondary_color'],
        'explicit'           => (int)$u['explicit'],
        'isExplicit'         => (int)$u['explicit'],
        'private'            => (int)$u['private_account'],
        'privateAccount'     => (int)$u['private_account'],
        'edition'            => 'classic',
        'twitterConnected'   => false,
        'twitterId'          => null,
        'twitterScreenname'  => null,
        'twitterVerified'    => false,
        'hiddenTwitter'      => 0,
        'followingOnTwitter' => false,
        'uploadHD'           => (bool)$u['upload_hd'],
        'disableAddressBook' => false,
        'acceptsOutOfNetworkConversations' => 0,
        'repostsEnabled'     => (int)$u['reposts_enabled'],
        'includePromoted'    => (int)($u['include_promoted'] ?? 0),
        'notifyPosts'        => (int)$u['notify_posts'],
        'phoneNumber'        => $u['phone_number'],
        'orderId'            => null,
        'following'          => $following,
        'followStatus'       => $followStatus,
        'followRequested'    => false, // Sends to the client that a follow has been requested
        'blocked'            => $blocked,
        'blocking'           => $blocking,
        'verifiedInformation'=> 1,
        'external'           => false,
        'friendIndex'        => 0,
        'hiddenEmail'        => false,
        'hiddenPhoneNumber'  => false,
        'shareUrl'           => base_url() . '/',
        'vanityUrls'         => [$u['username']],
    ];

    if ($private) {
        $out['email']       = $u['email'];
        $out['key']         = $u['token'];
        $out['deviceToken'] = $u['token'];
    }

    return $out;
}

function user_stub(array $u, int $viewer = 0): array {
    $following = ($viewer && $viewer !== (int)$u['id'])
        ? (int)(bool)q('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?',
                        [$viewer, $u['id']])->fetchColumn()
        : 0;
    return [
        'userId'            => (int)$u['id'],
        'username'          => $u['username'],
        'avatarUrl'         => $u['avatar_url'],
        'verified'          => (int)$u['verified'],
        'vanityUrls'        => [],
        'location'          => $u['location'],
        'following'         => $following,
        'profileBackground' => $u['profile_background'],
        'user'              => ['private' => (bool)$u['private_account']],
    ];
}

function post_object(array $p, int $viewer): array {
    $liked      = (int)(bool)q('SELECT COUNT(*) FROM likes   WHERE user_id=? AND post_id=?', [$viewer, $p['id']])->fetchColumn();
    $following  = (int)(bool)q('SELECT COUNT(*) FROM follows WHERE follower_id=? AND following_id=?', [$viewer, $p['user_id']])->fetchColumn();
    $myRepost   = (int)q('SELECT COUNT(*) FROM reposts WHERE user_id=? AND post_id=?', [$viewer, $p['id']])->fetchColumn() > 0;

    $tags = q('SELECT t.tag FROM tags t JOIN post_tags pt ON pt.tag_id=t.id WHERE pt.post_id=?',
              [$p['id']])->fetchAll(PDO::FETCH_COLUMN);

    $like_records = array_map(fn($r) => [
        'username'   => $r['username'],
        'verified'   => (int)$r['verified'],
        'avatarUrl'  => $r['avatar_url'],
        'created'    => $r['created_at'],
        'userId'     => (int)$r['user_id'],
        'location'   => $r['location'],
        'likeId'     => 0,
        'user'       => [
            'username'        => $r['username'],
            'verified'        => (int)$r['verified'],
            'description'     => $r['description'],
            'avatarUrl'       => $r['avatar_url'],
            'userId'          => (int)$r['user_id'],
            'location'        => $r['location'],
            'explicitContent' => null,
        ],
    ], q('SELECT l.created_at, l.user_id, u.username, u.avatar_url, u.verified, u.location, u.description
          FROM likes l JOIN users u ON u.id=l.user_id
          WHERE l.post_id=? ORDER BY l.created_at DESC LIMIT 15', [$p['id']])->fetchAll());

    // Build entities from description text
    $entities = [];
    preg_match_all('/#(\w+)/u', $p['description'], $tm, PREG_OFFSET_CAPTURE);
    foreach ($tm[1] as $m) {
        $start = max(0, $m[1] - 1); // position of '#'
        $entities[] = ['type' => 'tag', 'range' => [$start, $start + strlen($m[0]) + 1],
                       'link' => 'vine://tag/' . $m[0], 'id' => 0, 'title' => $m[0]];
    }
    preg_match_all('/@(\w+)/u', $p['description'], $mm, PREG_OFFSET_CAPTURE);
    foreach ($mm[1] as $m) {
        $uid = q('SELECT id FROM users WHERE username=?', [$m[0]])->fetchColumn();
        $start = max(0, $m[1] - 1);
        $entities[] = ['type' => 'mention', 'id' => (int)($uid ?: 0),
                       'text' => $m[0], 'range' => [$start, $start + strlen($m[0]) + 1]];
    }

    // Fetch author — guard against deleted users so we never crash on a false return
    $author = user_row((int)$p['user_id']);
    if (!$author) {
        $author = [
            'id' => $p['user_id'], 'username' => '[deleted]', 'avatar_url' => '',
            'description' => '', 'location' => '', 'verified' => 0, 'profile_background' => '0x333333',
        ];
    }

    $host = base_url();
    return [
        'postId'            => $p['id'],
        'userId'            => (int)$p['user_id'],
        'username'          => $author['username'],
        'avatarUrl'         => $author['avatar_url'],
        'verified'          => (int)$author['verified'],
        'description'       => $p['description'],
        'videoUrl'          => $p['video_url'],
        'videoLowURL'       => $p['video_url'],
        'videoPreview'      => $p['video_url'],
        'videoUrls'         => [
            ['format' => 'h264', 'rate' => 30, 'videoUrl' => $p['video_url']],
            ['format' => 'webm', 'rate' => 30, 'videoUrl' => $p['video_url']],
        ],
        'thumbnailUrl'      => $p['thumbnail_url'],
        'created'           => $p['created_at'],
        'liked'             => $liked,
        'private'           => (int)$p['private'],
        'promoted'          => (int)$p['promoted'],
        'explicitContent'   => (int)$p['explicit_content'],
        'following'         => $following,
        'foursquareVenueId' => $p['foursquare_venue_id'] ?? null,
        'venueName'         => $p['venue_name'] ?? null,
        'myRepostId'        => $myRepost ? (int)$p['id'] : null,
        'vanityUrls'        => [],
        'tags'              => $tags,
        'permalinkUrl'      => "$host/v/{$p['id']}",
        'shareUrl'          => "$host/v/{$p['id']}",
        'likes'    => ['count' => (int)$p['like_count'],    'records' => $like_records],
        'comments' => ['count' => (int)$p['comment_count'], 'records' => []],
        'reposts'  => ['count' => (int)$p['repost_count'],  'records' => []],
        'loops'    => ['count' => (int)$p['loop_count'], 'created' => $p['created_at'],
                       'velocity' => 0.1, 'onFire' => (int)$p['on_fire']],
        'entities' => $entities,
        'user'     => [
            'userId'            => (int)$author['id'],
            'username'          => $author['username'],
            'avatarUrl'         => $author['avatar_url'],
            'description'       => $author['description'],
            'location'          => $author['location'],
            'verified'          => (int)$author['verified'],
            'profileBackground' => $author['profile_background'],
            'following'         => $following,
            'explicitContent'   => null,
        ],
    ];
}
