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
USE smartwallet;

ALTER TABLE incomes
ADD COLUMN user_id INT,
ADD CONSTRAINT fk_incomes_user
FOREIGN KEY (user_id) REFERENCES userinfo(id);

SELECT * FROM incomes i WHERE i.card_id = 2 AND i.user_id = 11;
SELECT i.id , i.montant ,  i.laDate ,i.descri , c.card_name FROM incomes i LEFT JOIN cards c ON i.card_id = c.id WHERE i.user_id = 11;