-- ============================================================
-- Migration: Add Image_Path column to events table
-- ============================================================
ALTER TABLE events
ADD COLUMN Image_Path VARCHAR(255) DEFAULT NULL AFTER Event_Category;
