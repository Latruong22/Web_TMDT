-- Insert test accounts
-- Admin: email: admin@snowboard.com, password: admin123
-- User: email: user@test.com, password: user123

-- Clear existing test accounts
DELETE FROM users WHERE email IN ('admin@snowboard.com', 'user@test.com');

-- Insert Admin account
INSERT INTO users (fullname, email, password, role, status, phone, address, created_at) 
VALUES (
    'Administrator',
    'admin@snowboard.com',
    '$2y$12$tLr3.TLSJtBKIQmdQ/8LR.4KwSxLwxjf7BEEQov7eE7pzMLMG5DAy',
    'admin',
    'active',
    '0123456789',
    'Admin Address',
    NOW()
);

-- Insert User account (password: user123)
INSERT INTO users (fullname, email, password, role, status, phone, address, created_at) 
VALUES (
    'Test User',
    'user@test.com',
    '$2y$12$jJLvrmReqM4YpyfzhzM3oejcNWX92HJ1GXpkgodqCpvOvWSSF.bte',
    'user',
    'active',
    '0987654321',
    'User Address',
    NOW()
);

-- Display inserted accounts
SELECT user_id, fullname, email, role, status, created_at FROM users WHERE email IN ('admin@snowboard.com', 'user@test.com');
