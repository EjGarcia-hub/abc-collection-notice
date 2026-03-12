CREATE DATABASE collection_system;
USE collection_system;

CREATE TABLE collection_notices (
  notice_id INT AUTO_INCREMENT PRIMARY KEY,
  borrower_name VARCHAR(255),
  address TEXT,
  loan_type VARCHAR(100),
  promissory_no VARCHAR(50),
  notice_date DATE,
  as_of_date DATE,
  notice_level ENUM('FIRST','SECOND','FINAL'),
  total_amount DECIMAL(12,2),
  total_words VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE collection_amortizations (
  amort_id INT AUTO_INCREMENT PRIMARY KEY,
  notice_id INT,
  due_date DATE,
  amortization DECIMAL(10,2),
  interest DECIMAL(10,2),
  penalty DECIMAL(10,2),
  total DECIMAL(10,2),
  FOREIGN KEY (notice_id) REFERENCES collection_notices(notice_id)
);