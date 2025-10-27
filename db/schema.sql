-- SQLite schema for NOVEL NEST

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    username TEXT UNIQUE NOT NULL,
    photo TEXT,
    CREATE TABLE IF NOT EXISTS branch_entries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        branch_id INTEGER NOT NULL,
        date TEXT NOT NULL,
        entry_time TEXT,
        leave_time TEXT,
        FOREIGN KEY(user_id) REFERENCES users(id),
        FOREIGN KEY(branch_id) REFERENCES branches(id)
    );    email TEXT NOT NULL, -- removed UNIQUE constraint
    phone TEXT,
    address TEXT,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    unique_id TEXT UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    type TEXT,
    author_id INTEGER,
    cover TEXT,
    description TEXT,
    price REAL,
    stock INTEGER,
    FOREIGN KEY(author_id) REFERENCES authors(id)
);

CREATE TABLE IF NOT EXISTS authors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    bio TEXT,
    photo TEXT
);

CREATE TABLE IF NOT EXISTS attendance (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    branch TEXT,
    entry_time TEXT,
    exit_time TEXT,
    books TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS lend (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    branch TEXT,
    received_time TEXT,
    return_time TEXT,
    books TEXT,
    method TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS buy (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    branch TEXT,
    time TEXT,
    books TEXT,
    method TEXT,
    payment_total REAL,
    payment_id TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS amounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    change REAL,
    reason TEXT,
    date TEXT,
    final_amount REAL,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS branches (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    address TEXT,
    email TEXT,
    phone TEXT
);

CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT,
    date TEXT
);

CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    password1 TEXT,
    password2 TEXT,
    password3 TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id)
);
