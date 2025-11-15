-- 010_add_chat_is_read.sql
-- Adds an is_read flag to chat messages to support unread counts and realtime badges.

ALTER TABLE chat_messages
  ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message;
