-- Migration to add phone column to users for WhatsApp integration
USE `meister_todo`;
ALTER TABLE users ADD COLUMN phone VARCHAR(32) DEFAULT NULL;
