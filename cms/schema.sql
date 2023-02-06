/* ======================================

           CMS DATABASE SCHEMA

   ====================================== */
CREATE TABLE IF NOT EXISTS "users" (
	"user_id"	INTEGER NOT NULL UNIQUE,
	"username"	TEXT NOT NULL UNIQUE,
	"display_name"	TEXT NOT NULL,
	"hash_pw_with_salt"	TEXT NOT NULL,
	PRIMARY KEY("user_id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "posts" (
	"post_id"	INTEGER NOT NULL UNIQUE,
	"user_id"	INTEGER NOT NULL,
	"post_URL"	TEXT NOT NULL UNIQUE,
	"title"	TEXT NOT NULL,
	"date"	INTEGER NOT NULL,
	"content"	TEXT NOT NULL,
	"status"	TEXT NOT NULL,
	"tags"	TEXT,
	"views"	INTEGER,
	PRIMARY KEY("post_id" AUTOINCREMENT),
	FOREIGN KEY("user_id") REFERENCES "users"("user_id")
);

CREATE TABLE IF NOT EXISTS "comments" (
	"comment_id"	INTEGER NOT NULL UNIQUE,
	"post_id"	INTEGER NOT NULL,
	"comment"	TEXT NOT NULL,
	"name"	TEXT NOT NULL,
	FOREIGN KEY("post_id") REFERENCES "posts"("post_id"),
	PRIMARY KEY("comment_id" AUTOINCREMENT)
);

CREATE TABLE IF NOT EXISTS "sessions" (
	"session_id"	INTEGER NOT NULL UNIQUE,
	"user_id"	INTEGER NOT NULL,
	"token"	TEXT NOT NULL UNIQUE,
	"ttl"	INTEGER NOT NULL,
	FOREIGN KEY("user_id") REFERENCES "users"("user_id"),
	PRIMARY KEY("session_id" AUTOINCREMENT)
);