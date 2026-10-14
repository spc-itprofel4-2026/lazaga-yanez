CREATE TABLE weather_readings (
 id INT AUTO_INCREMENT PRIMARY KEY,
 city VARCHAR(100) NOT NULL,
 notes TEXT,
 temperature DECIMAL(10,2) NOT NULL,
 humidity INT,
 recorded_at DATETIME NOT NULL,
 is_alert TINYINT(1)
);
