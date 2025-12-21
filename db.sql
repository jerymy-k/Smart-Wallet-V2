CREATE DATABASE SmartWallet;

USE smartwallet;

CREATE TABLE incomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    montant DECIMAL(10, 2) NOT NULL,
    laDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descri TEXT
);

CREATE TABLE expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    montant DECIMAL(10, 2) NOT NULL,
    laDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descri TEXT
);

INSERT INTO
    incomes (montant, descri)
VALUES (
        '$montant_incomes',
        '$incomes_desc'
    );

INSERT INTO
    expenses (montant, descri)
VALUES (
        '$montant_expenses',
        '$expenses_desc'
    );

UPDATE incomes
SET
    montant = '$montant',
    descri = '$descripcion'
WHERE
    id = $id;

UPDATE expenses
SET
    montant = '$montant',
    descri = '$descripcion'
WHERE
    id = $id;

DELETE FROM incomes WHERE id = $id;

SET @num := 0;

UPDATE incomes SET id = (@num := @num + 1) ORDER BY id;

ALTER TABLE incomes AUTO_INCREMENT = 1;

DELETE FROM expenses WHERE id = $id;

SET @num := 0;

UPDATE expenses SET id = (@num := @num + 1) ORDER BY id;

ALTER TABLE expenses AUTO_INCREMENT = 1;

CREATE TABLE userinfo (
    id int PRIMARY KEY AUTO_INCREMENT,
    FullName TEXT NOT NULL,
    Email VARCHAR(50) NOT NULL UNIQUE,
    Passw TEXT NOT NULL
);

ALTER TABLE userinfo ADD COLUMN stat BOOLEAN DEFAULT FALSE;

use smartwallet;

CREATE TABLE cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id int NOT NULL,
    card_name VARCHAR(100) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_cards_user FOREIGN KEY (user_id) REFERENCES userinfo (id) ON DELETE CASCADE
);
ALTER TABLE incomes ADD COLUMN card_id int NOT NULL;
ALTER TABLE incomes
ADD CONSTRAINT fk_income_card
FOREIGN KEY (card_id) REFERENCES cards(id)
ON DELETE CASCADE;
ALTER TABLE expenses ADD COLUMN card_id int NOT NULL;
ALTER TABLE expenses
ADD CONSTRAINT fk_expens_card
FOREIGN KEY (card_id) REFERENCES cards(id)
ON DELETE CASCADE;
INSERT INTO incomes (montant , descri , card_id) VALUES (100 , 'jfjfjfj' , 1);

ALTER TABLE userinfo ADD COLUMN otp int DEFAULT NULL ;


ALTER TABLE cards
ADD COLUMN bank_name VARCHAR(100) NULL,
ADD COLUMN initial_balance DECIMAL(10,2) NULL;
ALTER TABLE cards
MODIFY COLUMN bank_name VARCHAR(100) NULL AFTER card_name,

MODIFY COLUMN initial_balance DECIMAL(10,2) NULL DEFAULT 0.00 AFTER bank_name;
ALTER TABLE `cards` CHANGE `created_at` `added_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE expenses
ADD CONSTRAINT fk_expenses_cate
FOREIGN KEY (cate_id) REFERENCES categorie(id);

SELECT * FROM incomes i WHERE i.card_id = 2 AND i.user_id = 11;
SELECT i.id , i.montant ,  i.laDate ,i.descri , c.card_name FROM incomes i LEFT JOIN cards c ON i.card_id = c.id WHERE i.user_id = 11;


CREATE TABLE user_ip (
    id int PRIMARY KEY AUTO_INCREMENT,
    ip VARCHAR(20) NOT NULL ,
    user_id int ,
    FOREIGN KEY (user_id) REFERENCES userinfo(id)
);

CREATE TABLE categorie (
    id int PRIMARY KEY AUTO_INCREMENT ,
    cate VARCHAR(50) UNIQUE , 
    limite DECIMAL(10,2) ,
    rest DECIMAL(10,2)
);
INSERT INTO categorie (cate, limite, rest) VALUES
('Food', 2000.00, 2000.00),
('Transport', 800.00, 800.00),
('Rent', 3500.00, 3500.00),
('Internet', 300.00, 300.00),
('Electricity', 400.00, 400.00),
('Water', 200.00, 200.00),
('Health', 1000.00, 1000.00),
('Education', 1200.00, 1200.00),
('Entertainment', 600.00, 600.00),
('Shopping', 900.00, 900.00);
SELECT * FROM categorie;
CREATE TABLE cate_inco (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cate_name VARCHAR(50) NOT NULL
);
USE smartwallet;
INSERT INTO cate_inco (cate_name) VALUES
('Salary'),
('Freelance'),
('Business'),
('Bonus'),
('Investment'),
('Rental Income'),
('Commission'),
('Gift');

ALTER TABLE categorie ADD COLUMN IsActive BOOLEAN DEFAULT 0 ;
-- Remove the unique constraint on 'cate' alone
ALTER TABLE `categorie` DROP INDEX `cate`;

-- Add a composite unique constraint on both 'cate' and 'user_id'
ALTER TABLE `categorie` ADD UNIQUE KEY `unique_cate_per_user` (`cate`, `user_id`);


use smartwallet;
ALTER TABLE cards ADD COLUMN principal BOOLEAN DEFAULT NULL;


-- Supprime l'ancienne table si elle existe
DROP TABLE IF EXISTS transfers;

CREATE TABLE IF NOT EXISTS transfertss (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    note TEXT,
    status VARCHAR(20) DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES userinfo(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES userinfo(id) ON DELETE CASCADE
);

ALTER TABLE categorie DROP COLUMN IsActive;

CREATE TABLE monthly_recurrents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  card_id INT NOT NULL,

  type ENUM('income','expense') NOT NULL,
  title VARCHAR(120) NOT NULL,
  category_id INT NULL,
  amount DECIMAL(10,2) NOT NULL,

  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_run DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  INDEX (user_id),
  INDEX (is_active)
);
