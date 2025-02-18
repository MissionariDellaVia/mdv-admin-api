-- User Authentication and Admin Management
CREATE TABLE Admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    last_login DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    INDEX idx_admin_email (email)
) ENGINE=InnoDB;

-- Saints Information
CREATE TABLE Saints (
    saint_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    biography TEXT,
    recurrence_date DATE,
    feast_day DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    INDEX idx_saint_recurrence (recurrence_date)
) ENGINE=InnoDB;

-- Gospel Base Information
CREATE TABLE Gospels (
    gospel_id INT AUTO_INCREMENT PRIMARY KEY,
    gospel_verse VARCHAR(255) NOT NULL UNIQUE,
    gospel_text TEXT NOT NULL,
    evangelist VARCHAR(100) NOT NULL,
    sacred_text_reference TEXT,
    liturgical_period VARCHAR(100),
    latest_comment_id INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    UNIQUE INDEX idx_gospel_verse (gospel_verse)
) ENGINE=InnoDB;

-- Comments for Gospels (1:N relationship)
CREATE TABLE Comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    gospel_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    extra_info TEXT,
    youtube_link VARCHAR(255),
    comment_order INT NOT NULL,
    is_latest BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (gospel_id) REFERENCES Gospels(gospel_id) ON DELETE CASCADE,
    INDEX idx_gospel_comments (gospel_id, is_latest)
) ENGINE=InnoDB;

-- GospelWay
CREATE TABLE GospelWay (
    calendar_id INT AUTO_INCREMENT PRIMARY KEY,
    calendar_date DATE NOT NULL,
    gospel_id INT NOT NULL,
    saint_id INT,
    liturgical_season VARCHAR(100),
    is_solemnity BOOLEAN DEFAULT FALSE,
    is_feast BOOLEAN DEFAULT FALSE,
    is_memorial BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (gospel_id) REFERENCES Gospels(gospel_id),
    FOREIGN KEY (saint_id) REFERENCES Saints(saint_id),
    UNIQUE INDEX idx_calendar_date (calendar_date)
) ENGINE=InnoDB;

-- Contact Types (Email, Phone, Friar, Nun, etc.)
CREATE TABLE ContactTypes (
    contact_type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

-- Addresses
CREATE TABLE Addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    street VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

-- Contacts with their types and addresses
CREATE TABLE Contacts (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    contact_type_id INT NOT NULL,
    contact_value VARCHAR(255) NOT NULL,
    address_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (contact_type_id) REFERENCES ContactTypes(contact_type_id),
    FOREIGN KEY (address_id) REFERENCES Addresses(address_id)
) ENGINE=InnoDB;

-- Events and Appointments
CREATE TABLE Events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT '9999-01-01',
    start_time TIME,
    end_time TIME,
    place VARCHAR(255) NOT NULL,
    is_holy_mass BOOLEAN DEFAULT FALSE,
    is_recurring BOOLEAN DEFAULT FALSE,
    recurrence_pattern VARCHAR(50),
    address_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME,
    FOREIGN KEY (address_id) REFERENCES Addresses(address_id)
) ENGINE=InnoDB;

-- Static Content Pages
CREATE TABLE TextContents (
    content_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NOT NULL,
    is_published BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

-- Media Files
CREATE TABLE Media (
    media_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED,
    alt_text VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;

-- Sacred Verses with Colors
CREATE TABLE Seeds (
    seed_id INT AUTO_INCREMENT PRIMARY KEY,
    verse_text TEXT NOT NULL,
    color VARCHAR(50),
    reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME
) ENGINE=InnoDB;