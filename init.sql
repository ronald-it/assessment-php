CREATE TABLE companies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    website VARCHAR(255),
    address TEXT,
    source VARCHAR(50),
    inserted_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE normalized_companies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) UNIQUE,
    canonical_website VARCHAR(255),
    address TEXT
);

INSERT INTO companies (name, website, address, source) VALUES
('OpenAI', 'https://openai.com', '123 AI Street', 'API_1'),
('OpenAI', 'https://openai.com', '123 AI Street', 'MANUAL'),
('Innovatiespotter', 'https://innovatiespotter.nl', 'Groningen', 'SCRAPER_2'),
('Apple', 'https://apple.com', '1 Infinite Loop', 'API_1'),
('apple', 'https://apple.com', '1 Infinite Loop', 'SCRAPER_1');