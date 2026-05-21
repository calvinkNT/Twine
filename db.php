<?php

function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id                  INTEGER PRIMARY KEY AUTOINCREMENT,
            username            TEXT    NOT NULL,
            email               TEXT    UNIQUE NOT NULL,
            password_hash       TEXT    NOT NULL,
            token               TEXT    UNIQUE NOT NULL,
            description         TEXT    NOT NULL DEFAULT '',
            avatar_url          TEXT    NOT NULL DEFAULT '',
            location            TEXT    NOT NULL DEFAULT '',
            verified            INTEGER NOT NULL DEFAULT 0,
            follower_count      INTEGER NOT NULL DEFAULT 0,
            following_count     INTEGER NOT NULL DEFAULT 0,
            post_count          INTEGER NOT NULL DEFAULT 0,
            authored_post_count INTEGER NOT NULL DEFAULT 0,
            like_count          INTEGER NOT NULL DEFAULT 0,
            loop_count          INTEGER NOT NULL DEFAULT 0,
            profile_background  TEXT    NOT NULL DEFAULT '0x333333',
            secondary_color     TEXT    NOT NULL DEFAULT '0x000000',
            explicit            INTEGER NOT NULL DEFAULT 0,
            private_account     INTEGER NOT NULL DEFAULT 0,
            upload_hd           INTEGER NOT NULL DEFAULT 0,
            notify_posts        INTEGER NOT NULL DEFAULT 1,
            reposts_enabled     INTEGER NOT NULL DEFAULT 1,
            include_promoted    INTEGER NOT NULL DEFAULT 0,
            phone_number        TEXT,
            created_at          TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
        "CREATE TABLE IF NOT EXISTS posts (
            id                  INTEGER PRIMARY KEY,
            user_id             INTEGER NOT NULL REFERENCES users(id),
            description         TEXT    NOT NULL DEFAULT '',
            video_url           TEXT    NOT NULL DEFAULT '',
            thumbnail_url       TEXT    NOT NULL DEFAULT '',
            loop_count          INTEGER NOT NULL DEFAULT 0,
            like_count          INTEGER NOT NULL DEFAULT 0,
            comment_count       INTEGER NOT NULL DEFAULT 0,
            repost_count        INTEGER NOT NULL DEFAULT 0,
            private             INTEGER NOT NULL DEFAULT 0,
            promoted            INTEGER NOT NULL DEFAULT 0,
            explicit_content    INTEGER NOT NULL DEFAULT 0,
            channel_id          INTEGER NOT NULL DEFAULT 0,
            on_fire             INTEGER NOT NULL DEFAULT 0,
            foursquare_venue_id TEXT    DEFAULT NULL,
            venue_name          TEXT    DEFAULT NULL,
            created_at          TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
        "CREATE TABLE IF NOT EXISTS follows (
            follower_id  INTEGER NOT NULL REFERENCES users(id),
            following_id INTEGER NOT NULL REFERENCES users(id),
            PRIMARY KEY (follower_id, following_id)
        )",
        "CREATE TABLE IF NOT EXISTS follow_requests (
            requester_id INTEGER NOT NULL REFERENCES users(id),
            target_id    INTEGER NOT NULL REFERENCES users(id),
            created_at   TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now')),
            PRIMARY KEY (requester_id, target_id)
        )",
        "CREATE TABLE IF NOT EXISTS blocks (
            blocker_id INTEGER NOT NULL REFERENCES users(id),
            blocked_id INTEGER NOT NULL REFERENCES users(id),
            PRIMARY KEY (blocker_id, blocked_id)
        )",
        "CREATE TABLE IF NOT EXISTS likes (
            user_id    INTEGER NOT NULL REFERENCES users(id),
            post_id    INTEGER NOT NULL REFERENCES posts(id),
            created_at TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now')),
            PRIMARY KEY (user_id, post_id)
        )",
        "CREATE TABLE IF NOT EXISTS reposts (
            user_id    INTEGER NOT NULL REFERENCES users(id),
            post_id    INTEGER NOT NULL REFERENCES posts(id),
            created_at TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now')),
            PRIMARY KEY (user_id, post_id)
        )",
        "CREATE TABLE IF NOT EXISTS comments (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id    INTEGER NOT NULL REFERENCES posts(id),
            user_id    INTEGER NOT NULL REFERENCES users(id),
            comment    TEXT    NOT NULL,
            created_at TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
        "CREATE TABLE IF NOT EXISTS activity (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            source_user INTEGER NOT NULL,
            target_user INTEGER NOT NULL,
            type_id     INTEGER NOT NULL,
            post_id     INTEGER,
            is_read     INTEGER NOT NULL DEFAULT 0,
            created_at  TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
        "CREATE TABLE IF NOT EXISTS tags (
            id  INTEGER PRIMARY KEY AUTOINCREMENT,
            tag TEXT UNIQUE NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS post_tags (
            post_id INTEGER NOT NULL REFERENCES posts(id),
            tag_id  INTEGER NOT NULL REFERENCES tags(id),
            PRIMARY KEY (post_id, tag_id)
        )",
        "CREATE TABLE IF NOT EXISTS channels (
            id   INTEGER PRIMARY KEY,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS complaints (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            reporter_id INTEGER NOT NULL,
            target_type TEXT    NOT NULL,
            target_id   INTEGER NOT NULL,
            created_at  TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
    ];

    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }

    $ch = $pdo->prepare('INSERT OR IGNORE INTO channels (id, slug, name) VALUES (?,?,?)');
    foreach ([
        [1,  'comedy',              'Comedy'],
        [2,  'art-and-experimental','Art & Experimental'],
        [5,  'nature',              'Nature'],
        [7,  'family',              'Family'],
        [8,  'special-fx',         'Special FX'],
        [9,  'sports',             'Sports'],
        [10, 'food',               'Food'],
        [11, 'music',              'Music'],
        [12, 'beauty-and-fashion', 'Beauty & Fashion'],
        [13, 'health-and-fitness', 'Health & Fitness'],
        [14, 'news-and-politics',  'News & Politics'],
        [17, 'animals',            'Animals'],
    ] as [$id, $slug, $name]) {
        $ch->execute([$id, $slug, $name]);
    }

    foreach ([
        "ALTER TABLE posts ADD COLUMN foursquare_venue_id TEXT DEFAULT NULL",
        "ALTER TABLE posts ADD COLUMN venue_name TEXT DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN include_promoted INTEGER NOT NULL DEFAULT 0",
        "CREATE TABLE IF NOT EXISTS reposts (
            user_id INTEGER NOT NULL, post_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now')),
            PRIMARY KEY (user_id, post_id)
        )",
        "CREATE TABLE IF NOT EXISTS follow_requests (
            requester_id INTEGER NOT NULL, target_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now')),
            PRIMARY KEY (requester_id, target_id)
        )",
        "CREATE TABLE IF NOT EXISTS channels (id INTEGER PRIMARY KEY, slug TEXT NOT NULL UNIQUE, name TEXT NOT NULL)",
        "CREATE TABLE IF NOT EXISTS complaints (
            id INTEGER PRIMARY KEY AUTOINCREMENT, reporter_id INTEGER NOT NULL,
            target_type TEXT NOT NULL, target_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (strftime('%Y-%m-%dT%H:%M:%f', 'now'))
        )",
    ] as $sql) {
        try { $pdo->exec($sql); } catch (Exception) {}
    }

    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement {
    $s = db()->prepare($sql);
    $s->execute($params);
    return $s;
}
