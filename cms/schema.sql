/* ======================================

           CMS DATABASE SCHEMA

   ====================================== */
CREATE TABLE IF NOT EXISTS "users" (
	"username"	TEXT NOT NULL UNIQUE,
	"display_name"	TEXT NOT NULL,
	"hash_pw_with_salt"	TEXT NOT NULL,
	PRIMARY KEY("username")
);

CREATE TABLE IF NOT EXISTS "posts" (
	"post_id"	TEXT NOT NULL UNIQUE,
	"username"	TEXT NOT NULL,
	"title"	TEXT,
	"date"	INTEGER,
	"content"	TEXT,
	"status"	TEXT,
	"tags"	TEXT,
	"views"	INTEGER,
	PRIMARY KEY("post_id"),
	FOREIGN KEY("username") REFERENCES "users"("username")
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
	"username"	INTEGER NOT NULL,
	"token"	TEXT NOT NULL UNIQUE,
	"ttl"	INTEGER NOT NULL,
	FOREIGN KEY("username") REFERENCES "users"("username"),
	PRIMARY KEY("session_id" AUTOINCREMENT)
);