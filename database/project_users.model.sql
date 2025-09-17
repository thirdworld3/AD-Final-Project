CREATE TABLE IF NOT EXISTS project_users (
    project_id uuid NOT NULL REFERENCES projects (id),
    user_id uuid NOT NULL REFERENCES users (id),
    role varchar(50) DEFAULT 'member',
    joined_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id, user_id)
);
