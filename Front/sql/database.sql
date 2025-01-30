
    CREATE TABLE admins (
        admin_id INT PRIMARY KEY AUTO_INCREMENT,
        admin_image VARCHAR(255), 
        admin_name VARCHAR(50),
        admin_username VARCHAR(55) UNIQUE,
        admin_contact_no VARCHAR(12),
        admin_email VARCHAR(55) UNIQUE,
        admin_gender VARCHAR(10),
        pass_code VARCHAR(50) DEFAULT 'admin',
        admin_password VARCHAR(155),
        admin_active_status VARCHAR(20) DEFAULT 'Offline'
    );


INSERT INTO admins (admin_name, admin_username, admin_contact_no, admin_email, admin_gender, admin_password) 
VALUES ('Admin', 'Admin', '12345678901', 'admin@gmail.com', 'Male', SHA2('admin', 256));

CREATE TABLE users (
    uid INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    userimg TEXT, 
    firstname VARCHAR(50),
    lastname VARCHAR(50),
    gender VARCHAR(10),
    age VARCHAR(500),
    email VARCHAR(100) UNIQUE NOT NULL,
    contactnum VARCHAR(15),
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    Q1 VARCHAR(255),
    A1 VARCHAR(255),
    user_status VARCHAR(20) DEFAULT 'Offline',
    profile_completed TINYINT(1) DEFAULT 0,
    remember_token VARCHAR(64)
);



CREATE TABLE announcement (
    post_aid INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    post_image1 VARCHAR(500),
    post_content1 TEXT,
    check_status TINYINT(1) DEFAULT 0, -- 0 = unread, 1 = read
    post_date1 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    likes1 INT DEFAULT 0,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);


CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    image_upload TEXT,
    recipient_username VARCHAR(50) NOT NULL,
    message_content TEXT,
    read_status TINYINT(1) DEFAULT 0, -- 0 = unread, 1 = read
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES admins(admin_id),
    FOREIGN KEY (recipient_id) REFERENCES users(uid)
);



CREATE TABLE messages1 (
    msg_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_uid INT NOT NULL,
    recipient_aid INT NOT NULL,
    image_upload TEXT,
    message_cont TEXT,
    read_status1 TINYINT(1) DEFAULT 0, -- 0 = unread, 1 = read
    sent_at1 DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_uid) REFERENCES users(uid),
    FOREIGN KEY (recipient_aid) REFERENCES admins(admin_id)
    
);


CREATE TABLE posts (
    post_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    post_image VARCHAR(100),
    post_content TEXT,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    likes INT DEFAULT 0,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);


CREATE TABLE feedback (
    f_id INT PRIMARY KEY AUTO_INCREMENT,
    f_name VARCHAR(50),
    f_gmail VARCHAR(100) UNIQUE NOT NULL,
    f_content TEXT,
    f_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE reservation (
    s_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    uid INT,
    service_type VARCHAR(100),
    s_description VARCHAR(100),
    s_description1 VARCHAR(100),
    set_date DATE,
    time_slot VARCHAR(100),
    s_address VARCHAR(255),
    valid_id TEXT,
    s_requirements TEXT,
    s_requirements1 TEXT,
    s_requirements2 TEXT,
    s_requirements3 TEXT,
    s_requirements4 TEXT,
    s_requirements5 TEXT,
    per_head INT,
    r_type VARCHAR(50),
    priest VARCHAR(50),
    fee VARCHAR(100),
    amount VARCHAR(100),
    payment_type VARCHAR(50),
    s_status VARCHAR(20) DEFAULT 'To Pay',
    delete_bol INT,
    r_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uid) REFERENCES users(uid),
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);

    CREATE TABLE payment (
        pay_id INT PRIMARY KEY AUTO_INCREMENT,
        uid INT,
        s_id INT,
        p_screenshot TEXT,
        total_amount VARCHAR(100),
        ref_num INT,
        pay_date DATE,
        p_status VARCHAR(20) DEFAULT 'Pending',
        p_date  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uid) REFERENCES users(uid),
        FOREIGN KEY (s_id) REFERENCES reservation(s_id)
    );


CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    uid INT DEFAULT NULL,
    s_id INT DEFAULT NULL,
    pay_id INT DEFAULT NULL,
    post_aid INT,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id),
    FOREIGN KEY (uid) REFERENCES users(uid),
    FOREIGN KEY (s_id) REFERENCES reservation(s_id),
    FOREIGN KEY (pay_id) REFERENCES payment(pay_id),
    FOREIGN KEY (post_aid) REFERENCES announcement(post_aid)
);



CREATE TABLE notification_admin (
    n_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    uid INT,
    s_id INT,
    pay_id INT,
    f_id INT,
    message_noti VARCHAR(500),
    is_read1 BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id),
    FOREIGN KEY (uid) REFERENCES users(uid),
    FOREIGN KEY (f_id) REFERENCES feedback(f_id),
    FOREIGN KEY (s_id) REFERENCES reservation(s_id),
    FOREIGN KEY (pay_id) REFERENCES payment(pay_id)
);

CREATE TABLE `schedule_list` (
  `id` int(30) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



--
--CREATE TABLE notifications (
--  notification_id INT PRIMARY KEY AUTO_INCREMENT,
--  uid INT,
--  message TEXT,
--  status VARCHAR(20),
--  notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--  FOREIGN KEY (uid) REFERENCES users(uid)
--);



-- user ID MODIFY TO 5 DIGIT RANDOMIZE NUMBERS 
-- CLICK THE DATABASE AND GO TO SQL PASTE THIS

ALTER TABLE users MODIFY uid INT;

DELIMITER $$

CREATE TRIGGER before_insert_users
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
  DECLARE rand_uid INT;

  -- Generate a random 5-digit number and ensure it is unique
  SET rand_uid = FLOOR(10000 + (RAND() * 90000));

  -- Ensure the random number is unique by checking if it already exists
  WHILE EXISTS (SELECT 1 FROM users WHERE uid = rand_uid) DO
    SET rand_uid = FLOOR(10000 + (RAND() * 90000));
  END WHILE;

  -- Assign the unique random uid to the new row
  SET NEW.uid = rand_uid;
END$$

DELIMITER ;



--TO FIX BUGS  insert this to SQL 
INSERT INTO users (username, firstname, lastname,gender,age,email,contactnum,password)
VALUES ('test','test','test','Male','22','test@gmail.com','09151309012',SHA2 ('test123456',256));
