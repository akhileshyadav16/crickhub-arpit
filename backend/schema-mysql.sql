-- CrickHub MySQL schema

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role VARCHAR(32) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_role CHECK (role IN ('admin', 'viewer'))
);

CREATE TABLE IF NOT EXISTS teams (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    city VARCHAR(120),
    coach VARCHAR(120),
    captain VARCHAR(120),
    founded SMALLINT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS players (
    id CHAR(36) PRIMARY KEY,
    team_id CHAR(36),
    name VARCHAR(150) NOT NULL,
    role VARCHAR(80) NOT NULL,
    matches INTEGER NOT NULL DEFAULT 0,
    runs INTEGER NOT NULL DEFAULT 0,
    average DECIMAL(6,2) NOT NULL DEFAULT 0,
    strike_rate DECIMAL(6,2) NOT NULL DEFAULT 0,
    hundreds INTEGER NOT NULL DEFAULT 0,
    fifties INTEGER NOT NULL DEFAULT 0,
    fours INTEGER NOT NULL DEFAULT 0,
    sixes INTEGER NOT NULL DEFAULT 0,
    bio TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS matches (
    id CHAR(36) PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    home_team_id CHAR(36),
    away_team_id CHAR(36),
    venue VARCHAR(180),
    match_date DATE,
    status VARCHAR(40) NOT NULL DEFAULT 'Scheduled',
    result VARCHAR(200),
    summary TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (home_team_id) REFERENCES teams (id) ON DELETE SET NULL,
    FOREIGN KEY (away_team_id) REFERENCES teams (id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_players_team_id ON players (team_id);
CREATE INDEX IF NOT EXISTS idx_matches_match_date ON matches (match_date DESC);

-- Seed sample users (password: admin123 / viewer123, change in production)
INSERT INTO users (id, email, password_hash, role)
VALUES
    (UUID(), 'admin@crickhub.local', '$2y$10$q.ycgkSzl/xTFFVS1hpYpuWRerceuUrH0x73r715MuJ08QyutGXom', 'admin'),
    (UUID(), 'viewer@crickhub.local', '$2y$10$rgVNtI2um4dzmaVcBUCYxO16bhu.qTlun2bzTE02YV//SzeB7549C', 'viewer')
ON DUPLICATE KEY UPDATE email=email;

