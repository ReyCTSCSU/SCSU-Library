CREATE DATABASE IF NOT EXISTS scsu_library;
USE scsu_library;

CREATE TABLE Users (
    user_id        VARCHAR(20) PRIMARY KEY,
    user_email     VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    full_name      VARCHAR(100) NOT NULL,
    role           ENUM('student', 'librarian') NOT NULL,
    date_joined    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Books (
    book_id            INT AUTO_INCREMENT PRIMARY KEY,
    title              VARCHAR(255) NOT NULL,
    author             VARCHAR(255) NOT NULL,
    isbn               VARCHAR(20),
    genre              VARCHAR(100),
    publication_year   INT,
    location           VARCHAR(100),
    quantity_total     INT NOT NULL DEFAULT 1,
    quantity_available INT NOT NULL DEFAULT 1,
    status             ENUM('available', 'out_of_stock') DEFAULT 'available',
    is_active          TINYINT(1) NOT NULL DEFAULT 1,   -- ✅ NEW SOFT DELETE COLUMN
    date_added         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Borrowings (
    borrow_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id      VARCHAR(20) NOT NULL,
    book_id      INT NOT NULL,
    borrow_date  DATE NOT NULL,
    due_date     DATE NOT NULL,
    return_date  DATE NULL,
    status       ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
);

CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id    VARCHAR(20) NOT NULL,
    book_id         INT NULL,
    message         TEXT NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
);

CREATE TABLE Favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id     VARCHAR(20) NOT NULL,
    book_id     INT NOT NULL,
    date_added  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
);

CREATE TABLE Activity_Log (
    activity_id   INT AUTO_INCREMENT PRIMARY KEY,
    librarian_id  VARCHAR(20) NOT NULL,
    action        VARCHAR(50) NOT NULL,
    book_id       INT NULL,
    timestamp     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    details       TEXT,
    FOREIGN KEY (librarian_id) REFERENCES Users(user_id),
    FOREIGN KEY (book_id) REFERENCES Books(book_id)
);
