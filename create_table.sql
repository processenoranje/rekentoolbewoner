-- SQL to create the table for household data
CREATE TABLE household_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    postcode VARCHAR(10),
    huisnummer VARCHAR(10),
    toevoeging VARCHAR(10),
    zonnepanelen TINYINT(1) DEFAULT 0,
    preset VARCHAR(10),
    verbruik INT,
    opwek INT,
    data_source ENUM('preset', 'custom'),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);