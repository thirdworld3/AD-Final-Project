CREATE TABLE IF NOT EXISTS public."projects" (
    id uuid NOT NULL PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(255) NOT NULL,
    description text,
    status varchar(50) DEFAULT 'active',
    start_date date,
    end_date date,
    created_by uuid REFERENCES users(id),
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP
);
