-- Create database (edit the name if needed)
CREATE DATABASE IF NOT EXISTS coptic_patriarchs;
USE coptic_patriarchs;

-- Table: Patriarchs
CREATE TABLE patriarchs (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each patriarch
    name VARCHAR(255) NOT NULL,        -- Patriarch's name (in Arabic)
    period VARCHAR(255) NOT NULL,      -- Period of patriarchy (in Arabic)
    image VARCHAR(255),                -- Image path or filename
    bio TEXT,                         -- Biography (in Arabic)
    synaxarium TEXT                   -- Synaxarium biography
);

-- Table: Heresies
CREATE TABLE heresies (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each heresy
    name VARCHAR(255) NOT NULL,        -- Heresy name (in Arabic)
    description TEXT,                  -- Description (in Arabic)
    response TEXT                      -- How to respond (in Arabic)
);

-- Table: Patriarchs-Heresies Relationship
CREATE TABLE patriarchs_heresies (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each relation
    patriarch_id INT NOT NULL,         -- Patriarch ID (foreign key)
    heresy_id INT NOT NULL,            -- Heresy ID (foreign key)
    FOREIGN KEY (patriarch_id) REFERENCES patriarchs(id),
    FOREIGN KEY (heresy_id) REFERENCES heresies(id)
);

-- Insert 20 sample patriarchs (names, periods, bios in Arabic)
INSERT INTO patriarchs (name, period, image, bio) VALUES


-- Insert sample heresies (names, descriptions, responses in Arabic)
INSERT INTO heresies (name, description, response) VALUES

-- Insert sample relationships (edit as needed)
INSERT INTO patriarchs_heresies (patriarch_id, heresy_id) VALUES

-- Add Synaxarium column for a short biography from the Synaxarium
ALTER TABLE patriarchs ADD COLUMN synaxarium TEXT AFTER bio; 