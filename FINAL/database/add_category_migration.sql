-- ============================================================
-- Migration: Add Event_Category column to events table
-- ============================================================
-- Run this script on your existing database to support
-- category-based filtering on the landing page.
-- Existing events will default to 'General'.
-- ============================================================

ALTER TABLE events
ADD COLUMN Event_Category VARCHAR(50) NOT NULL DEFAULT 'General' AFTER Capacity;

-- Index for fast category-based lookups
CREATE INDEX idx_event_category ON events(Event_Category);
