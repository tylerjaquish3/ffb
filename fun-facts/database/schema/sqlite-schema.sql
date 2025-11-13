CREATE TABLE IF NOT EXISTS "draft" (
	id integer NOT NULL,
	year integer DEFAULT 0 NOT NULL,
	round integer DEFAULT 0 NOT NULL,
	round_pick integer DEFAULT 0 NOT NULL,
	overall_pick integer DEFAULT 0 NOT NULL,
	manager_id integer DEFAULT 0 NOT NULL,
	position varchar(10) DEFAULT '0' NOT NULL,
	player varchar(50) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS "finishes" (
	id integer NOT NULL,
	year integer DEFAULT 0 NOT NULL,
	manager_id integer DEFAULT 0 NOT NULL,
	finish integer DEFAULT 0 NOT NULL,
	PRIMARY KEY (id)
);
CREATE TABLE manager_fun_facts (
	id integer NOT NULL,
	manager_id integer DEFAULT 0 NOT NULL,
	fun_fact_id integer DEFAULT 0 NOT NULL,
	rank integer,
	value varchar(50),
	note varchar(100),
	new_leader integer DEFAULT 0 NOT NULL,
	created_at varchar(50),
	updated_at varchar(50),
	PRIMARY KEY (id)
);
CREATE TABLE IF NOT EXISTS "playoff_matchups" (
	id integer NOT NULL,
	year integer NOT NULL,
	round varchar(50) NOT NULL,
	manager1_id integer DEFAULT 0 NOT NULL,
	manager2_id integer DEFAULT 0 NOT NULL,
	manager1_seed integer DEFAULT 0,
	manager2_seed integer DEFAULT 0,
	manager1_score float(12) DEFAULT 0,
	manager2_score float(12) DEFAULT 0,
	PRIMARY KEY (id)
);
CREATE TABLE team_names (
	id integer NOT NULL,
	manager_id integer DEFAULT 0 NOT NULL,
	year integer DEFAULT 0 NOT NULL,
	name varchar(50) DEFAULT '0' NOT NULL,
	moves integer DEFAULT 0 NOT NULL,
	trades integer DEFAULT 0 NOT NULL,
	PRIMARY KEY (id)
);
CREATE TABLE fun_facts (id integer NOT NULL, fact varchar (100) NOT NULL, is_positive integer DEFAULT 1 NOT NULL, type VARCHAR, sort_order INTEGER DEFAULT 0, PRIMARY KEY (id));
CREATE TABLE trades (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE, trade_identifier INTEGER NOT NULL, year INTEGER, manager_from_id INTEGER, manager_to_id INTEGER, player STRING, week INTEGER);
CREATE TABLE stats (id integer NOT NULL, roster_id integer, pass_yds integer, pass_tds integer, ints integer, rush_yds integer, rush_tds integer, receptions integer, rec_yds integer, rec_tds integer, fumbles integer, fg_made integer, fg_yards integer, pat_made integer, def_sacks integer, def_int integer, def_fum integer, PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS "regular_season_matchups" (id integer NOT NULL, year integer DEFAULT 0 NOT NULL, week_number integer DEFAULT 0 NOT NULL, manager1_id integer DEFAULT 0 NOT NULL, manager2_id integer DEFAULT 0 NOT NULL, manager1_score float (12) DEFAULT 0 NOT NULL, manager2_score float (12) DEFAULT 0 NOT NULL, winning_manager_id integer, losing_manager_id integer, manager1_projected DECIMAL (12), manager2_projected DECIMAL (12), PRIMARY KEY (id));
CREATE TABLE season_managers (id INTEGER PRIMARY KEY AUTOINCREMENT, year INTEGER, manager_id INTEGER, yahoo_id INTEGER);
CREATE TABLE managers (id integer NOT NULL, name varchar (50) NOT NULL, PRIMARY KEY (id));
CREATE TABLE season_positions (id INTEGER PRIMARY KEY AUTOINCREMENT, year INTEGER NOT NULL, position VARCHAR (11) NOT NULL, sort_order INTEGER (11));
CREATE TABLE nfl_teams (id integer NOT NULL, name varchar (50), abbr VARCHAR (10), sportradar_id varchar (200), PRIMARY KEY (id));
CREATE TABLE IF NOT EXISTS "rosters" (id integer NOT NULL, year integer DEFAULT 0 NOT NULL, week integer, manager varchar (50), player varchar (100), position varchar (50), roster_spot varchar (50), projected float (12), points float (12), team VARCHAR (5), game_time DATETIME, game_slot INTEGER, PRIMARY KEY (id));
CREATE TABLE newsletters (id INTEGER PRIMARY KEY AUTOINCREMENT, year INT NOT NULL, week INT NOT NULL, recap TEXT, preview TEXT);
CREATE TABLE schedule (id integer NOT NULL, manager1_id integer, manager2_id integer, year INT, week integer, PRIMARY KEY (id));
CREATE TABLE playoff_rosters (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, year INTEGER NOT NULL, round VARCHAR (20) NOT NULL, week INTEGER, manager VARCHAR (20) NOT NULL, player VARCHAR (50), position VARCHAR (10), roster_spot VARCHAR (10), team VARCHAR (10), points DECIMAL, game_time DATETIME, game_slot INTEGER);
CREATE TABLE IF NOT EXISTS "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);
CREATE TABLE IF NOT EXISTS "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");
CREATE TABLE IF NOT EXISTS "password_resets" ("email" varchar not null, "token" varchar not null, "created_at" datetime);
CREATE INDEX "password_resets_email_index" on "password_resets" ("email");
CREATE TABLE IF NOT EXISTS "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");
CREATE TABLE record_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, manager_id VARCHAR(255) NOT NULL, year INTEGER NOT NULL, week VARCHAR(255) NOT NULL, fun_fact_id INTEGER NOT NULL, value VARCHAR(255) NOT NULL, note CLOB DEFAULT NULL, new_leader BOOLEAN DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL);
CREATE TABLE record_log_backup_111(
  id INT,
  manager_id TEXT,
  year INT,
  week TEXT,
  fun_fact_id INT,
  value TEXT,
  note TEXT,
  new_leader NUM,
  created_at NUM,
  updated_at NUM
);
CREATE TABLE player_aliases (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	player VARCHAR(100) NOT NULL,
	alias_1 VARCHAR(100),
	alias_2 VARCHAR(100),
	alias_3 VARCHAR(100)
);
CREATE TABLE standings (
	id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	year INTEGER NOT NULL,
	week INTEGER NOT NULL,
	manager_id INTEGER NOT NULL,
	rank INTEGER NOT NULL,
	points DECIMAL(8,2) NOT NULL,
	wins INTEGER DEFAULT 0,
	losses INTEGER DEFAULT 0
);
INSERT INTO migrations VALUES(1,'2014_10_12_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO migrations VALUES(3,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO migrations VALUES(4,'2023_09_17_000000_create_record_log_table',1);
INSERT INTO migrations VALUES(5,'2025_09_17_000001_alter_record_log_week_to_string',2);
