USE `meister_todo`;

INSERT IGNORE INTO teams (id,name,description) VALUES (1,'Engineering','Engineering Team'),(2,'Marketing','Marketing Team');
INSERT IGNORE INTO tags (name) VALUES ('backend'),('frontend'),('urgent'),('research');

-- Note: Use the `scripts/create_admin.php` helper to create an admin user (it hashes the password).
