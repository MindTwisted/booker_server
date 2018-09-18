<?php

require_once '../bootstrap/env.php';
require_once '../app/config/config.php';
require_once '../bootstrap/autoload.php';
require_once '../bootstrap/start.php';

// Set QueryBuilder instance
$builderMySQL = $queryBuilder;

// Get tables prefix from config
$prefix = DB_TABLE_PREFIX;

// Disable foreign key checks
$builderMySQL->raw("SET FOREIGN_KEY_CHECKS=0");

// Drop tables
$builderMySQL->raw("DROP TABLE IF EXISTS {$prefix}users");
$builderMySQL->raw("DROP TABLE IF EXISTS {$prefix}rooms");
$builderMySQL->raw("DROP TABLE IF EXISTS {$prefix}events");

// Enable foreign key checks
$builderMySQL->raw("SET FOREIGN_KEY_CHECKS=1");

// Create users table
$builderMySQL->raw(
    "CREATE TABLE {$prefix}users (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  name VARCHAR(255) NOT NULL,
                  email VARCHAR(255) NOT NULL,
                  password VARCHAR(255) NOT NULL,
                  role ENUM('admin', 'user') DEFAULT 'user',
                  UNIQUE (email)
            )"
);

// Create rooms table
$builderMySQL->raw(
    "CREATE TABLE {$prefix}rooms (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  name VARCHAR(255) NOT NULL,
                  UNIQUE (name)
            )"
);

// Create events table
$builderMySQL->raw(
    "CREATE TABLE {$prefix}events (
                  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                  recur_id BIGINT UNSIGNED DEFAULT NULL,
                  description TEXT NOT NULL,
                  start_time TIMESTAMP NOT NULL DEFAULT '1980-01-01 00:00:00',
                  end_time TIMESTAMP NOT NULL DEFAULT '1980-01-01 00:00:00',
                  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  user_id INT UNSIGNED,
                  room_id INT UNSIGNED,
                  FOREIGN KEY (user_id)
                        REFERENCES {$prefix}users (id)
                        ON DELETE SET NULL,
                  FOREIGN KEY (room_id)
                        REFERENCES {$prefix}rooms (id)
                        ON DELETE SET NULL
            )"
);