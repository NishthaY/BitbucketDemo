\set db advice2pay

-- https://www.codeigniter.com/user_guide/libraries/sessions.html#database-driver

CREATE TABLE "ci_sessions" (
  "id" varchar(128) NOT NULL,
  "ip_address" varchar(45) NOT NULL,
  "timestamp" bigint DEFAULT 0 NOT NULL,
  "data" text DEFAULT '' NOT NULL
);
ALTER TABLE "ci_sessions" OWNER TO :db;

CREATE INDEX "ci_sessions_timestamp" ON "ci_sessions" ("timestamp");

-- When sess_match_ip = TRUE
--ALTER TABLE ci_sessions ADD PRIMARY KEY (id, ip_address);

-- When sess_match_ip = FALSE
ALTER TABLE ci_sessions ADD PRIMARY KEY (id);

-- To drop a previously created primary key (use when changing the setting)
--ALTER TABLE ci_sessions DROP PRIMARY KEY;